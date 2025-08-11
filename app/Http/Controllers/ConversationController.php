<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // ✅ Get all conversations for the authenticated user
    public function index()
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with([
                'users:id,name', // Only load name
                'messages' => fn($q) => $q->latest()->limit(1)->with('sender:id,name') // last message
            ])
            ->get()
            ->map(function ($conv) use ($user) {
                $otherUser = $conv->users->first(fn($u) => $u->id !== $user->id) ?? $conv->users->first();

                return [
                    'id' => $conv->id,
                    'subject' => $conv->subject,
                    'last_message_at' => $conv->messages->first()?->created_at,
                    'with_farmer' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                    ] : null,
                    'last_message' => $conv->messages->first() ? [
                        'id' => $conv->messages->first()->id,
                        'text' => $conv->messages->first()->message,
                        'sender_id' => $conv->messages->first()->sender_id,
                        'created_at' => $conv->messages->first()->created_at,
                    ] : null,
                ];
            });

        return response()->json($conversations);
    }

    // ✅ Get all messages in a conversation
    public function messages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is part of the conversation
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            });

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // ✅ Start a new conversation
    public function startChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255'
        ]);

        $existing = Conversation::whereHas('users', function ($q) {
            $q->where('user_id', Auth::id());
        })->whereHas('users', function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        })->first();

        if ($existing) {
            return response()->json($existing);
        }

        $conversation = new Conversation(['subject' => $request->subject]);
        $conversation->save();
        $conversation->users()->attach([Auth::id(), $request->user_id]);

        return response()->json($conversation, 201);
    }

    // ✅ Send a message
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        $message->load('sender:id,name'); // Load sender info

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'created_at' => $message->created_at->toISOString(),
        ], 201);
    }

    // ✅ Mark as read
    public function markAsRead($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['status' => 'read']);
    }
}