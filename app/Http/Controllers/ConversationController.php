<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NewMessage;

class ConversationController extends Controller
{
 
public function index(Request $request)
{
    $userId = Auth::id(); // ✅ Get the logged-in user's ID

    // Now you can safely use $userId
    $messages = Message::where('sender_id', $userId)
                       ->orWhere('receiver_id', $userId)
                       ->get();

    return response()->json($messages);
}

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
            'message' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255'
        ]);

        // Prevent messaging yourself
        if ($request->recipient_id == Auth::id()) {
            return response()->json(['error' => 'Cannot message yourself'], 422);
        }

        // Check for existing conversation
        $existingConversation = Auth::user()->conversations()
            ->whereHas('users', function($query) use ($request) {
                $query->where('user_id', $request->recipient_id);
            })
            ->when($request->product_id, function($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->first();

        if ($existingConversation) {
            $conversation = $existingConversation;
        } else {
            $conversation = Conversation::create([
                'subject' => $request->subject ?? 'New Conversation',
                'product_id' => $request->product_id
            ]);

            $conversation->addParticipants([Auth::id(), $request->recipient_id]);
        }

        // Create the message
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->message
        ]);

        // Broadcast the message
        broadcast(new NewMessage($message))->toOthers();

        // Send notification
        $recipient = User::find($request->recipient_id);
        $recipient->notify(new \App\Notifications\NewMessageNotification($message));

        return response()->json([
            'conversation' => $conversation->load('users', 'product'),
            'message' => $message
        ], 201);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark as read
        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now()
        ]);

        return response()->json([
            'conversation' => $conversation->load('users', 'product'),
            'messages' => $messages
        ]);
    }

    public function markAsRead(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}