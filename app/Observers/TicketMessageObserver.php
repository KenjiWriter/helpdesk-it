<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketMessageNotification;
use Illuminate\Support\Facades\Notification;

class TicketMessageObserver
{
    /**
     * Route a new message notification to the appropriate recipient(s).
     *
     * - IT Staff / Admin author → notify the ticket owner.
     * - User author, assigned ticket → notify the assignee.
     * - User author, unassigned ticket → notify all IT Staff.
     */
    public function created(TicketMessage $message): void
    {
        $message->loadMissing(['user', 'ticket.user', 'ticket.assignee']);

        $author = $message->user;
        $ticket = $message->ticket;

        $notification = new NewTicketMessageNotification($message);

        if (in_array($author->role, [UserRole::ItStaff, UserRole::Admin], strict: true)) {
            // IT Staff / Admin replied — notify the ticket owner.
            $ticket->user->notify($notification);

            return;
        }

        // Regular user replied — notify the assigned IT Staff, or all staff if unassigned.
        if ($ticket->assignee_id !== null) {
            $ticket->assignee->notify($notification);
        } else {
            $itStaff = User::where('role', UserRole::ItStaff->value)->get();
            Notification::send($itStaff, $notification);
        }
    }
}
