<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\Feedback;

class NewFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $feedback;

    /**
     * Create a new notification instance.
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail']; // You can also add 'broadcast' if using real-time
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Feedback Received')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received new feedback on one of your products.')
            ->line('Rating: ' . $this->feedback->rating . '/5')
            ->line('Comment: ' . ($this->feedback->comment ?: 'No comment provided'))
            ->action('View Product', url('/dashboard/products/' . $this->feedback->product_id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Store in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'You received new feedback on your product.',
            'product_id' => $this->feedback->product_id,
            'rating' => $this->feedback->rating,
            'comment' => $this->feedback->comment,
            'user_id' => $this->feedback->user_id,
            'feedback_id' => $this->feedback->id,
        ];
    }

    /**
     * Broadcast (optional if using Laravel Echo).
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
