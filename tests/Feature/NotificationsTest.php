<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketMessageNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeUser(UserRole $role = UserRole::User): User
{
    return User::factory()->create(['role' => $role->value]);
}

function makeTicket(User $owner, ?User $assignee = null): Ticket
{
    return Ticket::factory()->create([
        'user_id'     => $owner->id,
        'assignee_id' => $assignee?->id,
    ]);
}

// ─── TicketCreatedNotification ────────────────────────────────────────────────

test('ticket_created_notifies_all_it_staff', function (): void {
    Notification::fake();

    $staff1 = makeUser(UserRole::ItStaff);
    $staff2 = makeUser(UserRole::ItStaff);
    $owner  = makeUser(UserRole::User);

    $ticket = makeTicket($owner);

    Notification::assertSentTo($staff1, TicketCreatedNotification::class);
    Notification::assertSentTo($staff2, TicketCreatedNotification::class);
    Notification::assertNotSentTo($owner, TicketCreatedNotification::class);
});

// ─── TicketStatusChangedNotification ─────────────────────────────────────────

test('ticket_status_change_notifies_owner', function (): void {
    Notification::fake();

    $owner = makeUser(UserRole::User);
    $staff = makeUser(UserRole::ItStaff);
    $ticket = makeTicket($owner, $staff);

    $ticket->update(['status' => TicketStatus::InProgress->value]);

    Notification::assertSentTo(
        $owner,
        TicketStatusChangedNotification::class,
        fn (TicketStatusChangedNotification $n) =>
            $n->ticket->id === $ticket->id
            && $n->oldStatus === TicketStatus::New,
    );
});

test('ticket_status_unchanged_does_not_notify', function (): void {
    Notification::fake();

    $owner = makeUser(UserRole::User);
    $ticket = makeTicket($owner);

    // Update a field other than status — no status notification should fire.
    $ticket->update(['hardware_name' => 'Dead keyboard']);

    Notification::assertNotSentTo($owner, TicketStatusChangedNotification::class);
});

// ─── NewTicketMessageNotification ────────────────────────────────────────────

test('it_staff_message_notifies_ticket_owner', function (): void {
    Notification::fake();

    $owner  = makeUser(UserRole::User);
    $staff  = makeUser(UserRole::ItStaff);
    $ticket = makeTicket($owner, $staff);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id'   => $staff->id,
    ]);

    Notification::assertSentTo(
        $owner,
        NewTicketMessageNotification::class,
        fn (NewTicketMessageNotification $n) => $n->message->ticket_id === $ticket->id,
    );
    Notification::assertNotSentTo($staff, NewTicketMessageNotification::class);
});

test('user_message_notifies_assigned_staff', function (): void {
    Notification::fake();

    $owner  = makeUser(UserRole::User);
    $staff  = makeUser(UserRole::ItStaff);
    $ticket = makeTicket($owner, $staff);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id'   => $owner->id,
    ]);

    Notification::assertSentTo(
        $staff,
        \App\Notifications\UserRepliedToTicketNotification::class,
        fn (\App\Notifications\UserRepliedToTicketNotification $n) => $n->message->ticket_id === $ticket->id,
    );
    Notification::assertNotSentTo($owner, \App\Notifications\UserRepliedToTicketNotification::class);
});

test('user_message_notifies_all_staff_when_unassigned', function (): void {
    Notification::fake();

    $owner  = makeUser(UserRole::User);
    $staff1 = makeUser(UserRole::ItStaff);
    $staff2 = makeUser(UserRole::ItStaff);

    // Unassigned ticket (no assignee_id).
    $ticket = makeTicket($owner, null);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id'   => $owner->id,
    ]);

    Notification::assertSentTo($staff1, \App\Notifications\UserRepliedToTicketNotification::class);
    Notification::assertSentTo($staff2, \App\Notifications\UserRepliedToTicketNotification::class);
    Notification::assertNotSentTo($owner, \App\Notifications\UserRepliedToTicketNotification::class);
});
