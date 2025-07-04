<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;

    public function __construct($senderId, $receiverId)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.' . $this->receiverId);
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }
}
