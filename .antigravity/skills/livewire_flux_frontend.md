---
name: livewire_flux_frontend
description: How the user-facing Livewire/Flux frontend is structured, how routes are protected, and how file uploads are handled.
---

# Livewire + Flux User Frontend — IT Helpdesk

> Read this file before creating or modifying any user-facing Livewire pages, routes, file upload logic, or sidebar navigation.

---

## Architecture Overview

Regular employees (`UserRole::User`) use a **Livewire 4 + Flux** frontend at `/dashboard` and `/tickets/*`. IT staff and admins use the **Filament panel** at `/helpdesk`. These two areas are completely separate.

---

## Route Protection

All user-facing routes are wrapped in three middleware layers:

```php
Route::middleware(['auth', 'verified', 'role.user'])->group(function () {
    Route::livewire('dashboard',         'pages::dashboard')      ->name('dashboard');
    Route::livewire('tickets/create',    'pages::tickets.create') ->name('tickets.create');
    Route::livewire('tickets/{ticket}',  'pages::tickets.show')   ->name('tickets.show');
});
```

- `auth` — must be logged in.
- `verified` — email must be verified.
- `role.user` — `App\Http\Middleware\EnsureUserRole` — aborts 403 if `$user->role !== UserRole::User`.

**IT staff / admin hitting `/dashboard` get a 403.** They should use `/helpdesk`.

---

## Component Naming Convention

| Livewire class | Blade view |
|----------------|------------|
| `App\Livewire\Pages\Dashboard` | `resources/views/pages/⚡dashboard.blade.php` |
| `App\Livewire\Pages\Tickets\Create` | `resources/views/pages/tickets/⚡create.blade.php` |
| `App\Livewire\Pages\Tickets\Show` | `resources/views/pages/tickets/⚡show.blade.php` |

All components use `#[Layout('components.layouts.app')]` (the Flux sidebar layout) and `#[Title('...')]` attributes.

---

## File Upload Pattern

The `Create` component uses Livewire's `WithFileUploads` trait.

```php
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    #[Rule(['attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:4096'])]
    public array $attachments = [];
```

### Storage

Attachments are stored on the **`public` disk** (requires `php artisan storage:link` run once):

```php
$path = $file->storePublicly('ticket-attachments', 'public');
// stored at: storage/app/public/ticket-attachments/<hash>.<ext>
// public URL: Storage::disk('public')->url($path)
```

A `TicketAttachment` record is created for each file:

```php
TicketAttachment::create([
    'ticket_id' => $ticket->id,
    'user_id'   => $user->id,
    'filename'  => $file->getClientOriginalName(),
    'path'      => $path,
    'mime_type' => $file->getMimeType(),
    'size'      => $file->getSize(),
]);
```

**Never** store attachments on the `local` disk — they won't be publicly accessible.

---

## Sidebar Navigation

Defined in `resources/views/layouts/app/sidebar.blade.php`. Current items:

```
Helpdesk (group heading)
  ├── My Tickets    (icon: home)        → route('dashboard')
  └── New Ticket    (icon: plus-circle) → route('tickets.create')
```

To add more nav items, add `<flux:sidebar.item>` inside the `Helpdesk` group.

---

## Flux UI Conventions Used

| Purpose | Component |
|---------|-----------|
| Page layout | `<x-layouts::app>` wrapping `<flux:main>` |
| Select field | `<flux:select>` + `<flux:select.option>` |
| Text area | `<flux:textarea>` |
| Text input | `<flux:input>` |
| File input | Native `<input type="file" wire:model="...">` (Flux has no file input component) |
| Validation error | `<flux:error name="fieldName" />` |
| Field wrapper | `<flux:field>` + `<flux:label>` + `<flux:description>` |
| Flash messages | `<flux:callout variant="success">` |
| Buttons | `<flux:button variant="primary|ghost|outline">` |
| Badges/Pills | Tailwind inline `<span>` (Flux badge not used for status/priority rows) |

---

## Ownership Policy

`Show` component enforces ownership manually:

```php
public function mount(Ticket $ticket): void
{
    if ($ticket->user_id !== auth()->id()) {
        abort(403);
    }
}
```

There is **no Laravel Policy class** yet. If a Policy is added later, replace the manual check with `$this->authorize('view', $ticket)`.

---

## Enum Usage in Views

Priority and Category enums are iterated with `::cases()` to populate selects:

```blade
@foreach ($priorities as $p)
    <flux:select.option value="{{ $p->value }}">{{ $p->getLabel() }}</flux:select.option>
@endforeach
```

Status/Priority badge colors are mapped with a PHP `$colorMap` array inside `@php` blocks in Blade — **not** via Filament's `getColor()` method (which resolves to Filament semantic tokens, not Tailwind classes).
