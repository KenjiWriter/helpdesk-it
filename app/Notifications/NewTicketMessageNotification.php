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
        $snippet   = $message->body;

        return (new MailMessage)
            ->subject(__('emails.ticket_message.new_reply_subject', ['id' => $ticket->id, 'category' => $ticket->category->getLabel()]))
            ->view('emails.ticket-message', compact(
                'notifiable',
                'ticket',
                'author',
                'ticketUrl',
                'snippet',
            ));
    }
}

