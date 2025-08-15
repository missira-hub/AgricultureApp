<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\Conversation;


class MessageController extends Controller
{
  public function index()
{
    $userId = auth()->id();

    $conversations = Conversation::whereHas('participants', function ($q) use ($userId) {
        $q->where('user_id', $userId);
    })->with(['participants', 'lastMessage'])->get();

    return response()->json($conversations);
}

    public function show($conversationId)
{
    $conversation = Conversation::with('messages.user')->findOrFail($conversationId);

    return response()->json([
        'conversation' => $conversation,
        'messages' => $conversation->messages
    ]);
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'body' => 'required|string',
        'receiver_id' => 'required|integer|exists:users,id',
        'conversation_id' => 'nullable|integer',
    ]);

    $senderId = Auth::id();
    $conversationId = $validated['conversation_id'] ?? null;

    if (!$conversationId) {
        // Try to find an existing conversation between these two users
        $conversation = Conversation::whereHas('participants', function ($q) use ($senderId) {
            $q->where('user_id', $senderId);
        })->whereHas('participants', function ($q) use ($validated) {
            $q->where('user_id', $validated['receiver_id']);
        })->first();

        if (!$conversation) {
            // Create new conversation
            $conversation = Conversation::create();
            // Add participants properly using your helper method
            $conversation->addParticipants([$senderId, $validated['receiver_id']]);
        }

        $conversationId = $conversation->id;
    } else {
        // Verify the conversation exists
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }
    }

    // Create the message tied to this conversation
    $message = Message::create([
        'sender_id' => $senderId,
        'receiver_id' => $validated['receiver_id'],
        'body' => $validated['body'],
        'conversation_id' => $conversationId,
    ]);

    return response()->json([
        'message' => $message,
        'conversation_id' => $conversationId,
    ], 201);
}


public function startChat(Request $request)
{
    $request->validate([
        'user_one_id' => 'required|exists:users,id',
        'user_two_id' => 'required|exists:users,id',
    ]);

    $chat = Chat::create([
        'user_one_id' => $request->user_one_id,
        'user_two_id' => $request->user_two_id,
    ]);

    return response()->json([
        'message' => 'Chat started successfully',
        'chat' => $chat
    ], 201);
}


}
