---
name: livewire_flux_frontend
description: How the user-facing Livewire/Volt frontend is structured, how routes are protected, and how file uploads are handled.
---

# Livewire + Volt User Frontend — IT Helpdesk

> Read this file before creating or modifying any user-facing Livewire pages, routes, file upload logic, or sidebar navigation.

---

## Architecture Overview

Regular employees (`UserRole::User`) use a **Livewire 4 / Volt + Flux** frontend at `/dashboard` and `/tickets/*`. IT staff and admins use the **Filament panel** at `/helpdesk`. These two areas are completely separate.

---

## Route Protection

All user-facing routes use three middleware layers:

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

**IT staff / admin hitting `/dashboard` get a 403.** They use `/helpdesk`.

---

## Volt Component Pattern

This project uses **Volt** (functional style: PHP class inline in the Blade file), matching the existing `⚡settings/*.blade.php` convention.

### ✅ Correct Volt structure

```blade
<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Page Title')] class extends Component {

    // Data for the template — use with() NOT render()
    public function with(): array
    {
        return ['items' => Item::all()];
    }

}; ?>

<div>  {{-- ← single root element, REQUIRED --}}
    {{-- template here — $items is available --}}
</div>
```

### ❌ Wrong — do NOT do this

```blade
{{-- WRONG: calling render() tries to find a separate .blade.php file --}}
public function render(): \Illuminate\View\View
{
    return $this->view('pages.dashboard', [...]);
}

{{-- WRONG: Route::livewire() already applies the layout —
     wrapping in <x-layouts::app> causes MultipleRootElementsDetectedException --}}
<x-layouts::app>
    <flux:main> ... </flux:main>
</x-layouts::app>
```

### Key rules

| Rule | Detail |
|------|--------|
| **Single root element** | Template must start with exactly one `<div>` or other HTML element |
| **Use `with()`** | Return view data from `with(): array` — do NOT define `render()` |
| **No layout wrapper** | `Route::livewire()` automatically applies the default layout (`components.layouts.app`); never add `<x-layouts::app>` in the template |
| **No `<flux:main>`** | Already included by the layout; don't add it again inside the component |
| **`#[Title]` works** | Volt components support `#[Title('...')]` and other attributes |
| **Public properties** | All `public` properties are automatically available in the template |

---

## File Lookup Path

`Route::livewire('tickets/create', 'pages::tickets.create')` resolves to:

```
resources/views/pages/tickets/⚡create.blade.php
```

The `⚡` prefix marks Volt components. The `pages::` namespace maps to `resources/views/pages/`.

---

## Component Inventory

| Route | Volt file | Purpose |
|-------|-----------|---------|
| `/dashboard` | `pages/⚡dashboard.blade.php` | User ticket history + stats |
| `/tickets/create` | `pages/tickets/⚡create.blade.php` | Ticket submission form |
| `/tickets/{ticket}` | `pages/tickets/⚡show.blade.php` | Read-only ticket detail |

---

## File Upload Pattern

The `⚡create.blade.php` component uses Livewire's `WithFileUploads` trait:

```php
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];
```

### Storage flow

Attachments are stored on the **`public` disk** (requires `php artisan storage:link` once):

```php
$path = $file->storePublicly('ticket-attachments', 'public');
// URL: Storage::disk('public')->url($path)
```

A `TicketAttachment` record is created per file:

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

---

## Sidebar Navigation

Defined in `resources/views/layouts/app/sidebar.blade.php`:

```html
<flux:sidebar.group :heading="__('Helpdesk')" class="grid">
    <flux:sidebar.item icon="home"        :href="route('dashboard')"       ...>My Tickets</flux:sidebar.item>
    <flux:sidebar.item icon="plus-circle" :href="route('tickets.create')"  ...>New Ticket</flux:sidebar.item>
</flux:sidebar.group>
```

---

## UserFactory — Role Default

`database/factories/UserFactory.php` explicitly sets `'role' => UserRole::User` in `definition()`. This is required — relying on the SQLite DB column default during factory mass-insert is unreliable in tests.

Factory states available: `->itStaff()`, `->admin()`, `->unverified()`, `->withTwoFactor()`.

---

## Ownership Policy

`⚡show.blade.php` enforces ownership in `mount()`:

```php
public function mount(Ticket $ticket): void
{
    if ($ticket->user_id !== auth()->id()) {
        abort(403);
    }
    $this->ticket = $ticket->load(['department', 'attachments', 'assignee']);
}
```

There is **no Laravel Policy class** yet. If a Policy is added, replace the manual check with `$this->authorize('view', $ticket)`.

---

## Flux UI Conventions

| Purpose | Component |
|---------|-----------|
| Select field | `<flux:select>` + `<flux:select.option>` |
| Text area | `<flux:textarea>` |
| Text input | `<flux:input>` |
| File input | Native `<input type="file" wire:model="...">` (Flux has no file component) |
| Validation errors | `<flux:error name="fieldName" />` |
| Field wrapper | `<flux:field>` + `<flux:label>` + `<flux:description>` |
| Flash messages | `<flux:callout variant="success\|danger">` |
| Buttons | `<flux:button variant="primary\|ghost\|outline">` |
