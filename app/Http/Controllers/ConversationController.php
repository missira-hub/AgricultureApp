<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     * Includes last message and the other participant info.
     */
   public function index()
{
    $user = Auth::user();

    $conversations = Conversation::with([
        'users:id,name,avatar', // ✅ Use 'avatar', not 'avatar_url'
        'messages' => function ($q) {
            $q->latest()->limit(1);
        },
        'product:id,name'
    ])
    ->whereHas('users', fn($q) => $q->where('users.id', $user->id))
    ->get();

    return response()->json($conversations->map(function ($conv) use ($user) {
        $otherUser = $conv->users->where('id', '!=', $user->id)->first();

        return [
            'id' => $conv->id,
            'subject' => $conv->subject,
            'product' => $conv->product ? ['id' => $conv->product->id, 'name' => $conv->product->name] : null,
            'with_user' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar_url' => $otherUser->avatar_url ?? '/default-avatar.png' // ✅ This now works
            ] : null,
            'last_message' => $conv->messages->isNotEmpty() ? [
                'message' => $conv->messages->first()->message,
                'created_at' => $conv->messages->first()->created_at,
            ] : null,
            'created_at' => $conv->created_at,
            'updated_at' => $conv->updated_at,
        ];
    }));
}

/**
 * Get all messages in a conversation with sent/received differentiation.
 * Marks received messages as read.
 */
public function messages($conversationId)
    {
        try {
            $user = Auth::user();
$conversation = Conversation::with([
    'users', 
    'messages.sender:id,name,avatar' // ✅ Only real fields
])->findOrFail($conversationId);
            if (!$conversation->users->contains('id', $user->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $messages = $conversation->messages->map(function ($msg) use ($user, $conversation) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name ?? 'Unknown',
                    'sender_avatar' => $msg->sender->avatar_url ?? '/default-avatar.png', // ✅ OK
                    'type' => $msg->sender_id === $user->id ? 'sent' : 'received',
                    'status' => $msg->is_read ? 'read' : 'delivered',
                    'created_at' => $msg->created_at->toDateTimeString(),
                ];
            });

            // Mark unread messages as read for current user
            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json($messages);

        } catch (\Exception $e) {
            Log::error('ConversationController@messages failed: '.$e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

    /**
     * Start a new conversation or return existing one.
     */
    public function startChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255'
        ]);

        $currentUserId = Auth::id();
        $otherUserId = $request->user_id;

        if ($currentUserId === $otherUserId) {
            return response()->json(['error' => 'Cannot chat with yourself'], 400);
        }

        $conversation = Conversation::whereHas('users', fn($q) => $q->where('user_id', $currentUserId))
            ->whereHas('users', fn($q) => $q->where('user_id', $otherUserId))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'subject' => $request->subject ?? 'New Conversation'
            ]);
            $conversation->users()->attach([$currentUserId, $otherUserId]);
        }

        return response()->json($conversation, $conversation->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Send a new message to a conversation.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = Auth::user();

        if ($conversationId === 'new' || $conversationId == 0) {
            $request->validate(['receiver_id' => 'required|exists:users,id']);

            $conversation = Conversation::whereHas('users', fn($q) => $q->where('user_id', $user->id))
                ->whereHas('users', fn($q) => $q->where('user_id', $request->receiver_id))
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create(['subject' => 'New Conversation']);
                $conversation->users()->attach([$user->id, $request->receiver_id]);
            }
        } else {
            $conversation = Conversation::findOrFail($conversationId);
        }

        if (!$conversation->users->contains('id', $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $request->message,
            'is_read' => false
        ]);

$message->load('sender:id,name,avatar'); // ✅ Not avatar_url
        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'sender_avatar' => $message->sender->avatar_url ?? '/default-avatar.png',
            'type' => 'sent',
            'status' => 'sent',
            'created_at' => $message->created_at->toDateTimeString(),
        ], 201);
    }
}
