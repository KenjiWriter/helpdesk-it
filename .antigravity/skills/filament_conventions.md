# Filament Conventions — IT Helpdesk

> Read this file before generating, modifying, or reviewing any Filament resource, page, or panel code.

## Installed Version

**Filament 5.4.1** (installed via `composer require filament/filament -W`)  
Compatible with **Livewire 4.x** and **Laravel 13.x**.

---

## Installation Commands

```bash
# Install Filament (no version constraint needed — resolves latest compatible)
composer require filament/filament -W

# Scaffold the panel provider (interactive — provide panel ID when prompted)
php artisan filament:install --panels

# Generate a resource (without --generate to write form/table yourself)
php artisan make:filament-resource ModelName

# Create a test user for the panel
php artisan make:filament-user
```

---

## API Changes vs Filament v3

The installed version ships with a unified `Filament\Schemas` layer.

| What | Old (v3) | Installed |
|------|----------|-----------|
| Form method signature | `form(Form $form): Form` | `form(Schema $schema): Schema` |
| Schema builder call | `$form->schema([...])` | `$schema->components([...])` |
| Section component | `Filament\Forms\Components\Section` | `Filament\Schemas\Components\Section` |
| Form import | `use Filament\Forms\Form;` | `use Filament\Schemas\Schema;` |
| Table method | `table(Table $table): Table` | unchanged |
| `Fieldset`, `Grid`, `Group`, `Tabs` | `Filament\Forms\Components\*` | `Filament\Schemas\Components\*` |

Form field components (`TextInput`, `Select`, `Textarea`, `DateTimePicker`, etc.) remain in `Filament\Forms\Components\*`.

---

## Property Type Rules

In the installed Filament, the `Resource` base class defines typed properties via traits. Override types **must exactly match** the trait declaration:

| Property | Correct type in child class |
|----------|-----------------------------|
| `$model` | `protected static ?string` |
| `$navigationIcon` | `protected static string\|BackedEnum\|null` |
| `$navigationLabel` | `protected static ?string` |
| `$modelLabel` | `protected static ?string` |
| `$pluralModelLabel` | `protected static ?string` |
| `$navigationSort` | `protected static ?int` |
| `$navigationGroup` | `protected static string\|UnitEnum\|null` |

> **Never** use `protected static $property` (untyped) — PHP will complain about type incompatibility.

---

## Panel Structure

```
app/Providers/Filament/
    HelpdeskPanelProvider.php   ← sole panel; path: /helpdesk; id: helpdesk

app/Filament/
    Resources/
        TicketResource.php
        TicketResource/
            Pages/
                ListTickets.php
                CreateTicket.php
                EditTicket.php
                ViewTicket.php
```

---

## Panel Access Security

The panel is guarded by the `FilamentUser` interface on `App\Models\User`:

```php
// app/Models/User.php
implements FilamentUser

public function canAccessPanel(Panel $panel): bool
{
    return $this->role?->canAccessPanel() ?? false;
}
```

`UserRole::canAccessPanel()` returns `true` for `ItStaff` and `Admin` only.  
Regular `User` role → HTTP 403 on any `/helpdesk/*` request.

---

## Enum Colors in Tables

Enums implement `Filament\Support\Contracts\HasColor` and `HasLabel`.  
`BadgeColumn` automatically picks up `getColor()` and `getLabel()`:

```php
// In a Resource table:
BadgeColumn::make('priority'),   // color from TicketPriority::getColor()
BadgeColumn::make('status'),     // color from TicketStatus::getColor()
```

Color return values map to Filament's semantic palette:
`'success'`, `'warning'`, `'danger'`, `'info'`, `'primary'`, `'gray'`

---

## Generating Resources

```bash
php artisan make:filament-resource Ticket
```

After generation, always:
1. Replace `use Filament\Forms\Form;` with `use Filament\Schemas\Schema;`
2. Change `form(Form $form): Form` → `form(Schema $schema): Schema`
3. Change `$form->schema([...])` → `$schema->components([...])`
4. Move `Section`, `Grid`, `Tabs` imports to `Filament\Schemas\Components\*`
5. Verify all overridden property types match the table above

---

## Relation Managers

Create relation managers using:
```bash
php artisan make:filament-relation-manager ResourceName relationshipName titleColumn
```

1. Remember to update the generated class's imports similarly to regular Resources (e.g., `Form` -> `Schema`).
2. When managing child records where a related property logic applies (such as assigning ticket owners automatically), you can use lifecycle hooks inside table actions (`after(function ($livewire) {...})`) or `mutateFormDataBeforeCreate`.
3. Use `Hidden::make('field')->default(...)` for auto-set relations without exposing UI.
4. Ensure related lists are ordered correctly using `defaultSort()`.

---

## Edit Page Quick Actions

Add custom workflow buttons by returning `Filament\Actions\Action` instances in the `getHeaderActions()` method on Edit pages.
- Complex actions can have their own forms via `->form([...])`.
- In the `->action(function (array $data, Model $record) {...})` closure, you can safely update the model, spawn related models (using relations like `$record->messages()->create(...)`), manage uploads, and send a `Notification::make()->success()->send()`.

