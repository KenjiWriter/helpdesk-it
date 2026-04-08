<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;

/**
 * Owns all business-logic mutations for the Ticket model.
 *
 * Observers and Volt components must NOT write ticket state directly —
 * they delegate through this service so logic remains centralised and testable.
 */
class TicketService
{
    /**
     * Mark a ticket as having an unread IT reply.
     *
     * Called by TicketMessageObserver when an IT Staff member or Admin
     * posts a new message to a ticket owned by a regular user.
     */
    public function markAsUnread(Ticket $ticket): void
    {
        if (! $ticket->has_unread_reply) {
            $ticket->update(['has_unread_reply' => true]);
        }
    }

    /**
     * Mark a ticket's unread-reply indicator as cleared.
     *
     * Called by the ⚡show.blade.php Volt component when the ticket owner
     * opens the ticket detail page.
     */
    public function markAsRead(Ticket $ticket): void
    {
        if ($ticket->has_unread_reply) {
            $ticket->update(['has_unread_reply' => false]);
        }
    }
}
