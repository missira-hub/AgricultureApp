<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user1' => [
                'id' => $this->user1->id,
                'name' => $this->user1->name,
                'avatar' => $this->user1->avatar_url ?? null,
            ],
            'user2' => [
                'id' => $this->user2->id,
                'name' => $this->user2->name,
                'avatar' => $this->user2->avatar_url ?? null,
            ],
            'latest_message' => $this->latestMessage ? [
                'content' => str_limit($this->latestMessage->content, 50),
                'created_at' => $this->latestMessage->created_at
            ] : null,
            'unread_count' => $this->messages->where('read', false)
                ->where('sender_id', '!=', auth()->id())
                ->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}