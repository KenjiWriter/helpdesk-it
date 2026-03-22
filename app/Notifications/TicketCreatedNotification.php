<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
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
        $ticket = $this->ticket;
        $category = $ticket->category->getLabel();
        $priority = $ticket->priority->getLabel();
        $panelUrl = url('/helpdesk/tickets/' . $ticket->id);

        return (new MailMessage)
            ->subject("New Support Ticket #{$ticket->id}: {$category}")
            ->greeting('New ticket submitted!')
            ->line("A new **{$priority}** priority ticket has been opened.")
            ->line("**Category:** {$category}")
            ->line("**Submitted by:** {$ticket->user->name}")
            ->line("**Description:** " . str($ticket->description)->limit(200))
            ->action("View Ticket #{$ticket->id}", $panelUrl)
            ->line('Please review and assign this ticket as soon as possible.');
    }
}
