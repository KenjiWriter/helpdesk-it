<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatus $oldStatus,
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
        $ticket    = $this->ticket;
        $newStatus = $ticket->status->getLabel();
        $oldStatus = $this->oldStatus->getLabel();
        $ticketUrl = url('/tickets/' . $ticket->id);

        $message = (new MailMessage)
            ->subject("Your Ticket #{$ticket->id} status changed to {$newStatus}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line("The status of your support ticket **#{$ticket->id}** has been updated.")
            ->line("**Previous status:** {$oldStatus}")
            ->line("**New status:** {$newStatus}");

        if ($ticket->status === TicketStatus::Resolved) {
            $message->line('Your issue has been marked as resolved. Please let us know if you need further assistance or rate the support you received.');
        }

        return $message
            ->action("View Your Ticket #{$ticket->id}", $ticketUrl)
            ->line('Thank you for using the IT Helpdesk.');
    }
}
