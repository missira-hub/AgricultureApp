<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function unreadCount()
    {
        $user = auth()->user();
        $count = $user->notifications()->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }

    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Notification',
                    'message' => $n->data['message'] ?? 'You have a new update.',
                    'is_read' => $n->read_at !== null,
                    'type' => $n->data['type'] ?? 'info',
                    'created_at' => $n->created_at,
                ];
            });

        return response()->json($notifications);
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->noContent();
    }
}