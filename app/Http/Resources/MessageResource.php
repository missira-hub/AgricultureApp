<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->name,
            'is_me' => $this->sender_id === auth()->id(),
            'read' => $this->read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'time_ago' => $this->created_at->diffForHumans()
        ];
    }
}