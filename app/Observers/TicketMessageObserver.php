<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketMessageNotification;
use App\Services\TicketService;
use Illuminate\Support\Facades\Notification;

class TicketMessageObserver
{
    public function __construct(
        private readonly TicketService $ticketService,
    ) {}

    /**
     * Route a new message notification to the appropriate recipient(s)
     * and update the unread-reply state via the TicketService.
     *
     * - IT Staff / Admin author → notify the ticket owner + mark ticket unread.
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
            $locale = $ticket->user->locale ?? 'pl';
            $ticket->user->notify((new NewTicketMessageNotification($message))->locale($locale));

            // Delegate flag mutation to the Service Layer.
            $this->ticketService->markAsUnread($ticket);

            return;
        }

        // Regular user replied — notify the assigned IT Staff, or all staff if unassigned.
        $this->ticketService->markAsUnreadByUser($ticket);
        
        $userReplyNotification = new \App\Notifications\UserRepliedToTicketNotification($message);

        if ($ticket->assignee_id !== null) {
            $ticket->assignee->notify($userReplyNotification);
        } else {
            $itStaff = User::where('role', UserRole::ItStaff->value)->get();
            Notification::send($itStaff, $userReplyNotification);
        }
    }
}
