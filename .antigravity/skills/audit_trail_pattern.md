# Audit Trail Pattern (History Log)

This document outlines the standard pattern for implementing and maintaining the Ticket History Log (Audit Trail) in the IT Helpdesk application.

## 1. Data Structure

All history records are stored in the `ticket_histories` table, represented by the `TicketHistory` model.

- **`ticket_id`**: Foreign key to the `tickets` table (cascade on delete).
- **`user_id`**: Foreign key to the `users` table (nullable, set null on delete). Represents who performed the action. Defaults to `null` (System) for automated actions.
- **`description`**: A string/text field containing the description of the event in Polish.
- **`timestamps`**: Standard `created_at` and `updated_at`.

## 2. Implementation Patterns

### A. Automatic Logging via Observers

For events that occur at the model level (like creation), use the `TicketObserver`.

```php
public function created(Ticket $ticket): void
{
    $ticket->histories()->create([
        'user_id' => $ticket->user_id, // or auth()->id() if applicable
        'description' => __('Zgłoszenie utworzone'),
    ]);
    // ... notifications
}
```

### B. Action-Based Logging (Filament)

For manual actions performed in the Filament panel, inject the logging logic directly into the action's `action()` or `after()` hooks.

**Example in `EditTicket.php`:**

```php
Action::make('assignToMe')
    ->action(function (Ticket $record) {
        $record->update(['assignee_id' => auth()->id()]);
        $record->histories()->create([
            'user_id' => auth()->id(),
            'description' => __('Przypisano do: :user', ['user' => auth()->user()->name]),
        ]);
    })
```

### C. Relation Manager Triggers

For events occurring in related resources (like messages), use the `after()` hook in the `CreateAction` or `EditAction` of the Relation Manager.

```php
CreateAction::make()
    ->after(function (RelationManager $livewire, Model $record) {
        $ticket = $livewire->getOwnerRecord();
        $ticket->histories()->create([
            'user_id' => auth()->id(),
            'description' => __('Dodano nową wiadomość'),
        ]);
    })
```

## 3. UI Display

History is displayed via a dedicated `TicketHistoriesRelationManager` in the `TicketResource`.

- **Strictly Read-Only**: The UI must not allow creating, editing, or deleting history records to ensure the audit trail remains tamper-proof.
- **Sorting**: Displayed in descending chronological order (`created_at` desc) to show the latest actions first.
- **Translations**: All descriptions must be wrapped in `__()` for Polish localization.
