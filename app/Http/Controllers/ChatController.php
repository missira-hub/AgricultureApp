<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;  // Make sure you have Chat model

class ChatController extends Controller
{
    // Create a new chat/conversation
    public function store(Request $request)
    {
        $request->validate([
            'user_one_id' => 'required|exists:users,id',
            'user_two_id' => 'required|exists:users,id',
        ]);

        $chat = Chat::create([
            'user_one_id' => $request->user_one_id,
            'user_two_id' => $request->user_two_id,
        ]);

        return response()->json($chat, 201);
    }

    // Show a chat by ID
    public function show($id)
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json($chat);
    }
}
