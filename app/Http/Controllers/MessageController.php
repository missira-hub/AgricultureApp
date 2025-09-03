<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     */
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with([
            'participants:id,name,avatar', // Load basic user data
            'messages' => fn($q) => $q->orderBy('created_at', 'desc')->limit(1) // last message
        ])
        ->withCount('messages')
        ->latest()
        ->get();

        return response()->json($conversations);
    }

    /**
     * Show all messages in a conversation.
     */
    public function show($conversationId)
    {
        $userId = Auth::id();

        // Make sure user is part of the conversation
        $conversation = Conversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with([
            'messages' => fn($q) => $q->orderBy('created_at', 'asc'),
            'messages.sender:id,name,avatar' // Only needed fields
        ])->findOrFail($conversationId);

        // Optionally mark messages as read
        $conversation->messages
            ->where('sender_id', '!=', $userId)
            ->each->markAsRead();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'participants' => $conversation->participants,
            ],
            'messages' => $conversation->messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_read' => (bool) $msg->is_read,
                    'created_at' => $msg->created_at,
                    'sender' => [
                        'id' => $msg->sender->id,
                        'name' => $msg->sender->name,
                        'avatar_url' => $msg->sender->avatar_url,
                    ]
                ];
            })
        ]);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000', // changed from 'body'
            'receiver_id' => 'required|integer|exists:users,id',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        $senderId = Auth::id();
        $receiverId = $validated['receiver_id'];
        $conversationId = $validated['conversation_id'] ?? null;

        // Find or create conversation
        if (!$conversationId) {
            $conversation = Conversation::whereHas('participants', function ($q) use ($senderId) {
                $q->where('user_id', $senderId);
            })->whereHas('participants', function ($q) use ($receiverId) {
                $q->where('user_id', $receiverId);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create();
                $conversation->addParticipants([$senderId, $receiverId]);
            }
        } else {
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }

            // Optional: Verify user is participant
            if (!$conversation->participants->contains('id', $senderId)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // Create message
        $message = Message::create([
            'sender_id' => $senderId,
            'conversation_id' => $conversation->id,
            'message' => $validated['message'], // fixed: was 'body'
            'is_read' => false,
        ]);

        // Load sender relationship for response
        $message->load('sender:id,name,avatar');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'avatar_url' => $message->sender->avatar_url,
                ]
            ],
            'conversation_id' => $conversation->id,
        ], 201);
    }

    /**
     * Start a chat (if using Chat model — likely redundant)
     */
    public function startChat(Request $request)
    {
        // ⚠️ Note: You may not need a separate Chat model if using Conversation
        // Consider merging or removing this

        $request->validate([
            'user_one_id' => 'required|exists:users,id',
            'user_two_id' => 'required|exists:users,id',
        ]);

        $chat = \App\Models\Chat::create([
            'user_one_id' => $request->user_one_id,
            'user_two_id' => $request->user_two_id,
        ]);

        return response()->json([
            'message' => 'Chat started successfully',
            'chat' => $chat
        ], 201);
    }
}