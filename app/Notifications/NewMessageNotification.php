<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New message from '.$this->message->user->name)
            ->line($this->message->user->name.' sent you a message:')
            ->line($this->message->body)
            ->action('View Conversation', url('/messages/'.$this->message->conversation_id))
            ->line('Thank you for using our marketplace!');
    }

    public function toArray($notifiable)
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->user_id,
            'sender_name' => $this->message->user->name,
            'body_preview' => str_limit($this->message->body, 100),
            'sent_at' => $this->message->created_at,
        ];
    }
}