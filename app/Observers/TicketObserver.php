<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class TicketObserver
{
    /**
     * Notify all IT Staff members when a new ticket is created.
     */
    public function created(Ticket $ticket): void
    {
        $ticket->loadMissing('user');

        $itStaff = User::where('role', UserRole::ItStaff->value)->get();

        Notification::send($itStaff, new TicketCreatedNotification($ticket));
    }

    /**
     * Notify the ticket owner when its status changes.
     */
    public function updated(Ticket $ticket): void
    {
        if (! $ticket->wasChanged('status')) {
            return;
        }

        $ticket->loadMissing('user');

        $originalStatus = $ticket->getOriginal('status');
        $oldStatus = $originalStatus instanceof TicketStatus
            ? $originalStatus
            : TicketStatus::from($originalStatus);

        $ticket->user->notify(new TicketStatusChangedNotification($ticket, $oldStatus));
    }
}
