<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     * Includes the last message and other participant info.
     */
    public function index()
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with([
                'users:id,name,avatar_url', // Load name and avatar
                'messages' => fn($query) => $query->latest()->limit(1)->with('sender:id,name') // Last message
            ])
            ->get()
            ->map(function ($conversation) use ($user) {
                // Find the other participant (not current user)
                $otherUser = $conversation->users->first(fn($u) => $u->id !== $user->id) ?? $conversation->users->first();

                return [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'lastMessageCreatedAt' => $conversation->messages->first()?->created_at, // frontend expects camelCase
                    'with_farmer' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatarUrl' => $otherUser->avatar_url ?? '/default-avatar.png',
                    ] : null,
                    'last_message' => $conversation->messages->first() ? [
                        'id' => $conversation->messages->first()->id,
                        'message' => $conversation->messages->first()->message, // Use 'message' not 'text'
                        'sender_id' => $conversation->messages->first()->sender_id,
                        'created_at' => $conversation->messages->first()->created_at,
                    ] : null,
                    'unread_count' => $conversation->messages()
                        ->where('sender_id', '!=', $user->id)
                        ->where('is_read', false)
                        ->count(),
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Get all messages in a specific conversation.
     * Also marks received messages as read.
     */
public function messages($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    if (!$conversation->users->contains('id', Auth::id())) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $messages = $conversation->messages()
        ->with(['sender:id,name,avatar_url']) // loads avatar_url via accessor
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($msg) {
            $sender = $msg->sender;
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_id' => $msg->sender_id,
                'sender_name' => $sender?->name ?? 'Unknown',
                'sender_avatar' => $sender?->avatar_url ?? '/default-avatar.png',
                'created_at' => $msg->created_at->toISOString(),
                'status' => $msg->is_read ? 'delivered' : 'sent',
            ];
        });

    // Mark as read
    $conversation->messages()
        ->where('sender_id', '!=', Auth::id())
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json($messages);
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

        // Prevent chatting with self
        if ($currentUserId == $otherUserId) {
            return response()->json(['error' => 'Cannot chat with yourself'], 400);
        }

        // Check for existing conversation
        $existing = Conversation::whereHas('users', function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        })->whereHas('users', function ($q) use ($otherUserId) {
            $q->where('user_id', $otherUserId);
        })->first();

        if ($existing) {
            return response()->json($existing, 200);
        }

        // Create new conversation
        $conversation = new Conversation([
            'subject' => $request->subject ?? 'New Conversation'
        ]);
        $conversation->save();

        // Attach both users
        $conversation->users()->attach([$currentUserId, $otherUserId]);

        return response()->json($conversation, 201);
    }

    /**
     * Send a new message to a conversation.
     */
   public function sendMessage(Request $request, $conversationId)
{
    $request->validate([
        'message' => 'required|string|max:1000'
    ]);

    $currentUser = Auth::user();

    // If conversationId is 0 or 'new', create a new one
    if ($conversationId === 'new' || $conversationId === 0) {
        $request->validate([
            'receiver_id' => 'required|exists:users,id'
        ]);

        $existing = Conversation::whereHas('users', function ($q) {
            $q->where('user_id', $currentUser->id);
        })->whereHas('users', function ($q) use ($request) {
            $q->where('user_id', $request->receiver_id);
        })->first();

        if ($existing) {
            $conversation = $existing;
        } else {
            $conversation = new Conversation(['subject' => 'New Conversation']);
            $conversation->save();
            $conversation->users()->attach([$currentUser->id, $request->receiver_id]);
        }
    } else {
        $conversation = Conversation::findOrFail($conversationId);
    }

    // Ensure current user is in the conversation
    if (!$conversation->users->contains('id', $currentUser->id)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Create message
    $message = $conversation->messages()->create([
        'sender_id' => $currentUser->id,
        'message' => $request->message,
        'is_read' => false,
    ]);

    $message->load('sender:id,name,avatar_url');

    return response()->json([
        'id' => $message->id,
        'message' => $message->message,
        'sender_id' => $message->sender_id,
        'sender_name' => $message->sender->name,
        'sender_avatar' => $message->sender->avatar_url ?? '/default-avatar.png',
        'created_at' => $message->created_at->toISOString(),
        'status' => 'sent'
    ], 201);
}
    /**
     * Mark all unread messages in a conversation as read.
     */
    public function markAsRead($conversationId)
    {
        $conversation = Conversation::with('users')->findOrFail($conversationId);

        if (!$conversation->users->contains('id', Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'read']);
    }
}