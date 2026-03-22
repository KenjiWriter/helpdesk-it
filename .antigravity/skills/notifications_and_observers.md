---
name: notifications_and_observers
description: Documentation on email notification triggers and Eloquent Observers.
---

# Notifications & Observers — IT Helpdesk

> Read this file before modifying ticket-related email notifications or observers.

---

## Eloquent Observers

The system automatically handles email notification dispatches using Eloquent Observers. The observers are bound directly to the models using Laravel 12's `#[ObservedBy()]` attribute.

- **`Ticket`** → `#[ObservedBy(TicketObserver::class)]`
- **`TicketMessage`** → `#[ObservedBy(TicketMessageObserver::class)]`

---

## Notification Triggers & Routing Matrix

All notification classes extend `Notification` and implement `ShouldQueue` to ensure asynchronous delivery in production.

| Trigger Event | Class Dispactcher | Notification Class | Sent To |
|--------------|-------------------|--------------------|---------|
| **New Ticket Created** | `TicketObserver::created` | `TicketCreatedNotification` | All `UserRole::ItStaff` users |
| **Ticket Status Changed** | `TicketObserver::updated` | `TicketStatusChangedNotification` | Ticket owner (`$ticket->user`) *Skipped if status unchanged.* |
| **New Reply (by IT Staff/Admin)** | `TicketMessageObserver::created` | `NewTicketMessageNotification` | Ticket owner |
| **New Reply (by User)** | `TicketMessageObserver::created` | `NewTicketMessageNotification` | The assigned IT Staff (`$ticket->assignee`) OR all IT Staff if ticket is unassigned. |

---

## Local Testing

To manually test these queues locally, ensure your `.env` contains:
```env
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

The emails will appear directly in `storage/logs/laravel.log`.

### Automated Testing
Run `php artisan test --filter=NotificationsTest` to execute all Notification coverage scenarios. Tests utilize `Notification::fake()` to verify dispatch logic without touching the mailer.
