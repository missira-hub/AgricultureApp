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
    $user = auth()->user();

    $conversations = Conversation::with([
        'consumer:id,name,avatar_url',
        'users:id,name,avatar_url', // ✅ Load users with avatar
        'messages' => function ($q) {
            $q->latest()->limit(1); // Optional: only last message
        },
        'product:id,name'
    ])
    ->whereHas('users', function ($q) use ($user) {
        $q->where('users.id', $user->id);
    })
    ->get();

    // Transform for frontend
    return response()->json($conversations->map(function ($conv) use ($user) {
        // Find the other participant (the farmer)
        $farmer = $conv->users->where('id', '!=', $user->id)->first();

        return [
            'id' => $conv->id,
            'subject' => $conv->subject,
            'product_id' => $conv->product_id,
            'product' => $conv->product ? ['id' => $conv->product->id, 'name' => $conv->product->name] : null,

            // ✅ Add with_farmer object (for frontend)
            'with_farmer' => $farmer ? [
                'id' => $farmer->id,
                'name' => $farmer->name,
                'avatar_url' => $farmer->avatar_url ?? '/default-avatar.png',
            ] : null,

            // Last message
            'last_message' => $conv->messages->isNotEmpty() ? [
                'message' => $conv->messages->first()->message,
                'created_at' => $conv->messages->first()->created_at,
            ] : null,

            'created_at' => $conv->created_at,
            'updated_at' => $conv->updated_at,
        ];
    }));
}
public function showMessages($id)
{
    $user = auth()->user();

    $conversation = Conversation::with('users')
        ->whereHas('users', fn($q) => $q->where('users.id', $user->id))
        ->findOrFail($id);

    $messages = $conversation->messages()
        ->with('sender:id,name,avatar_url') // ✅ Load sender with avatar
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($msg) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'sender_avatar_url' => $msg->sender->avatar_url ?? '/default-avatar.png',
                'created_at' => $msg->created_at,
            ];
        });

    return response()->json($messages);
}

    /**
     * Get all messages in a specific conversation.
     * Also marks received messages as read.
     */
    public function messages($conversationId)
{
    try {
        $conversation = Conversation::with('users:id,name')->findOrFail($conversationId);

        if (!$conversation->users->contains('id', Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name,avatar_url']) // ✅ Load sender with name & avatar_url
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                $sender = $message->sender; // This should now be loaded

                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $sender?->name ?? 'Unknown User',
                    'sender_avatar' => $sender?->avatar_url ?? '/default-avatar.png',
                    'created_at' => $message->created_at->toISOString(),
                    'status' => $message->is_read ? 'read' : 'delivered',
                ];
            });

        // Mark as read
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    } catch (\Exception $e) {
        \Log::error('ConversationController@messages failed: ' . $e->getMessage());
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
    public function getConversation($farmerId)
{
    $farmer = User::findOrFail($farmerId);
    $conversation = Conversation::where('farmer_id', $farmerId)->firstOrCreate([
        'farmer_id' => $farmerId,
        'user_id' => auth()->id(), // Current user
    ]);

    return response()->json([
        'conversation_id' => $conversation->id,
        'farmer' => [
            'id' => $farmer->id,
            'name' => $farmer->name,
            'avatar' => $farmer->avatar_url,
        ],
        'last_seen_at' => $conversation->updated_at,
        'messages' => $conversation->messages()->orderBy('created_at')->get(),
    ]);
}
public function getByFarmer($farmerId)
{
    $user = auth()->user();

    // Ensure farmer exists and is not the user
    $farmer = User::where('id', $farmerId)->where('role', 'farmer')->first();
    if (!$farmer) {
        return response()->json(['error' => 'Farmer not found'], 404);
    }

    // Find existing conversation or create new
    $conversation = Conversation::whereHas('users', function ($q) use ($user) {
        $q->where('users.id', $user->id);
    })->whereHas('users', function ($q) use ($farmer) {
        $q->where('users.id', $farmer->id);
    })->with(['users:id,name,avatar_url', 'messages.sender:id,name,avatar_url'])->first();

    if (!$conversation) {
        // Create new conversation
        $conversation = Conversation::create([
            'subject' => "Chat with {$farmer->name}",
        ]);

        $conversation->users()->attach([$user->id, $farmer->id]);
    }

    return response()->json([
        'id' => $conversation->id,
        'subject' => $conversation->subject,
        'messages' => $conversation->messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'sender_avatar_url' => $msg->sender->avatar_url ?? '/default-avatar.png',
                'created_at' => $msg->created_at,
            ];
        }),
    ]);
}
}