---
name: authorization_and_widgets
description: Documentation on the Ticket authorization policies and Filament widget structure.
---

# Authorization & Dashboard Widgets — IT Helpdesk

> Read this file before modifying Laravel Policies or Filament Dashboard Widgets.

---

## Authorization (TicketPolicy)

The IT Helpdesk manages permissions via the `TicketPolicy` (`app/Policies/TicketPolicy.php`).

### Intercepting Checks (`before` method)

Filament integrates automatically with Laravel Policies. To ensure IT Staff and Admins always have access to the `TicketResource` within the panel, the `TicketPolicy` intercepts all checks using the `before()` method:

```php
public function before(User $user, string $ability): ?bool
{
    // System-wide bypass for IT Staff and Admins
    if (in_array($user->role, [UserRole::ItStaff, UserRole::Admin], true)) {
        return true;
    }

    // Returning null falls back to the specific ability methods below
    return null;
}
```

### Regular User Checks

For the `UserRole::User` (regular employees accessing the Livewire frontend):
- **`viewAny` & `create`**: Allowed.
- **`view` & `update`**: Only allowed if `$user->id === $ticket->user_id`.
- **`delete`, `restore`, `forceDelete`**: Denied.

**Livewire Frontend Enforcement**:
The `⚡show.blade.php` Livewire component uses standard Laravel Gate authorization in its `mount()` method:
```php
\Illuminate\Support\Facades\Gate::authorize('view', $ticket);
```

---

## Filament Dashboard Widgets

The IT Helpdesk panel at `/helpdesk` features key metrics via widgets.

### Widget Location & Auto-Discovery
- **Path**: `app/Filament/Widgets/TicketStatsOverview.php`
- **Class**: Extends `Filament\Widgets\StatsOverviewWidget`
- **Registration**: **Widgets in `app/Filament/Widgets` are auto-discovered by Filament.** Do NOT manually add them to the `widgets()` array in `HelpdeskPanelProvider.php`, as this will cause them to render twice on the dashboard.

### `TicketStatsOverview` Metrics

The overview widget provides real-time counts:
1. **Total Open Tickets**: Queries `Ticket::query()->open()`.
2. **Resolved Today**: Queries `TicketStatus::Resolved` where `resolved_at` is today.
3. **Urgent/Fire Tickets**: Queries open tickets with `TicketPriority::Urgent` or `TicketPriority::Fire`.
