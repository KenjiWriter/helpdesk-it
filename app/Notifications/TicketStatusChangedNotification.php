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
        $newStatus = $ticket->status;
        $oldStatus = $this->oldStatus;
        $ticketUrl = url('/tickets/' . $ticket->id);
        $isResolved = $newStatus === TicketStatus::Resolved;

        // Map status values to template CSS class names.
        $newStatusClass = match ($newStatus) {
            TicketStatus::Resolved   => 'new-resolved',
            TicketStatus::Closed     => 'new-closed',
            TicketStatus::InProgress => 'new-in_progress',
            TicketStatus::New        => 'new-new',
        };

        // Map status to the header accent bar CSS class.
        $accentClass = match ($newStatus) {
            TicketStatus::Resolved   => 'resolved',
            TicketStatus::Closed     => 'closed',
            TicketStatus::InProgress => 'in_progress',
            TicketStatus::New        => 'default',
        };

        return (new MailMessage)
            ->subject(__('emails.ticket_status.updated_subject', ['id' => $ticket->id, 'status' => $newStatus->getLabel()]))
            ->view('emails.ticket-status-changed', compact(
                'notifiable',
                'ticket',
                'newStatus',
                'oldStatus',
                'ticketUrl',
                'isResolved',
                'newStatusClass',
                'accentClass',
            ));
    }
}

