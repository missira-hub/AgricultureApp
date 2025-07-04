<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\UserTyping;


class MessageController extends Controller
{
    // POST /api/messages/send
   public function send(Request $request)
{
    $inbox = Message::where('receiver_id', auth()->id())
    ->whereHas('sender', function ($q) {
        $q->where('role', 'farmer'); // Or 'consumer'
    })
    ->latest()
    ->get();

    $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'message' => 'required|string',
    ]);

    $sender = auth()->user();
    $receiver = User::findOrFail($request->receiver_id);

    // Optional checks based on roles
    if ($sender->role === $receiver->role) {
        return response()->json(['error' => 'Cannot message someone of the same type.'], 403);
    }

    // Save message
    $message = Message::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'message' => $request->message,
        'is_read' => false,

        
    ]);

    return response()->json(['message' => 'Message sent successfully.', 'data' => $message]);
}


    // GET /api/messages/conversation/{userId}
    public function conversation($userId)
    {
        $userId = intval($userId);
        $myId = Auth::id();

        $messages = Message::where(function ($query) use ($userId, $myId) {
            $query->where('sender_id', $myId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId, $myId) {
            $query->where('sender_id', $userId)->where('receiver_id', $myId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    // GET /api/messages/inbox
    public function inbox()
{
    $userId = Auth::id();

    // Fetch all messages where user is sender or receiver
    $messages = Message::with(['sender:id,name,role', 'receiver:id,name,role'])
        ->where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->latest()
        ->get()
        // Group by the other person in the conversation
        ->groupBy(function ($msg) use ($userId) {
            return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
        })
        // Get the latest message from each group
        ->map(function ($group) {
            return $group->first();
        })
        ->values();

    return response()->json([
        'status' => 'success',
        'user_id' => $userId,
        'inbox' => $messages
    ]);
}
public function unreadCount(Request $request)
{
    $user = $request->user();
    $count = Message::where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
    return response()->json(['unread_count' => $count]);
}

    public function typing(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|exists:users,id',
    ]);

    broadcast(new UserTyping(auth()->id(), $request->receiver_id))->toOthers();

    return response()->json(['status' => 'typing broadcast sent']);
}

public function delete($id)
{
    $message = Message::where('id', $id)
                      ->where('sender_id', auth()->id()) // Only sender can delete
                      ->firstOrFail();

    $message->delete();

    return response()->json(['status' => 'deleted']);
}
public function markAsRead($id)
{
    $message = Message::where('id', $id)
                      ->where('receiver_id', auth()->id())
                      ->firstOrFail();

    $message->read_at = now();
    $message->save();

    return response()->json(['status' => 'read']);
}


}
