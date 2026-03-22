<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TicketMessage $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message   = $this->message;
        $ticket    = $message->ticket;
        $author    = $message->user;
        $ticketUrl = url('/tickets/' . $ticket->id);
        $snippet   = str($message->body)->limit(300);

        return (new MailMessage)
            ->subject("New reply on Ticket #{$ticket->id}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line("**{$author->name}** has replied to support ticket **#{$ticket->id}**.")
            ->line("> {$snippet}")
            ->action("View Ticket #{$ticket->id}", $ticketUrl)
            ->line('Please log in to view the full message thread and respond.');
    }
}
