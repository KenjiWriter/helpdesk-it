---
description: Filament Admin Panel Dashboard Reporting and Internationalization (i18n) Logic
---

# Dashboard Reports and i18n 

This document explains the strategy for dashboard metrics and panel internationalization in the IT Helpdesk application.

## Internationalization (i18n)
All strings in the Filament User and Ticket resources, as well as the dashboard widgets, are fully translated into Polish.

- **Storage**: We use standard JSON translation files at `lang/pl.json`.
- **Implementation Rules**:
  1. Every static string label (e.g., column headers, section titles) in Filament components MUST be wrapped with the `__()` helper.
  2. For model labels within a Resource (`$navigationLabel`, `$modelLabel`, `$pluralModelLabel`), define getter methods like `getNavigationLabel()` and return `__('Translation string')` instead of using standard static variables, as the static variables would be evaluated before the application's locale is initialized. Example:

```php
public static function getModelLabel(): string
{
    return __('Ticket');
}
```

## Dashboard Reporting Widgets

The Admin Panel Dashboard features three custom reporting widgets located in `app/Filament/Widgets`. By default, Filament auto-discovers widgets via `HelpdeskPanelProvider::discoverWidgets()`.

### `TicketStatsOverview`
Displays high-level, aggregate statistics representing current and historical ticket states:
- Total Tickets (`Ticket::count()`)
- Resolved Tickets (`Ticket::where('status', TicketStatus::Resolved)->count()`)
- Unresolved Tickets (`Ticket::query()->open()->count()`)
- **Global Average Resolution Time**: See below for calculation details.

### `ITPerformanceWidget`
A `TableWidget` showing the resolution performance of individual IT Staff members.
- Queries `User::where('role', 'it_staff')`.
- Iterates over each IT staff's assigned tickets to compute the number of resolved tickets and their individual average resolution time.

### `LatestTicketsWidget`
A `TableWidget` showing the 5 most recently created tickets.

### Calculating Average Resolution Time
Since the application uses SQLite (`database/database.sqlite`), determining time differences through raw DB-agnostic SQL queries presents compatibility challenges for scaling.

To reliably compute the duration between `created_at` and `resolved_at` across records regardless of the driver (SQLite, MySQL, PostgreSQL):
1. **Fetch**: Extract the records from the repository into a Collection (`->get(['created_at', 'resolved_at'])`).
2. **Compute**: Iterate over the Collection with `reduce` or mapping and use the Carbon `diffInMinutes()` method to compute the duration reliably.
3. **Format**: Average the total time and mathematically convert minutes to "HHh MMm".
4. **Safety**: Ensure there is no division by zero error by defaulting to `__('N/A')` when the initial query yields a count of 0 resolved tickets.
