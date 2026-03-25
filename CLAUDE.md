# CLAUDE.md — IT Helpdesk Project Context

## Project Overview

A custom IT Helpdesk application. Users submit support tickets; IT Staff manage and resolve them via a dedicated Filament panel. Admins have overview access.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 / PHP 8.3 |
| Realtime UI | Livewire 4 + Flux (user-facing frontend) |
| Admin Panel | **Filament 5.4.1** (installed, active) — IT Staff panel |
| Auth | Laravel Fortify + TwoFactorAuthenticatable |
| DB (dev) | SQLite (`database/database.sqlite`) |

---

## Roles

| Role value | Description |
|------------|-------------|
| `user` | Regular employee — creates tickets via Livewire frontend |
| `it_staff` | IT technician — manages tickets via `/helpdesk` Filament panel |
| `admin` | Read-only stats viewer — also accesses `/helpdesk` panel |

---

## Key Directories

```
app/
  Enums/           UserRole, TicketPriority, TicketCategory, TicketStatus
  Models/          User, Department, Ticket, TicketMessage, TicketAttachment, Setting
  Filament/
    Pages/         ManageBranding (System Ustawienia)
    Resources/     TicketResource (+ Pages/)
  Providers/
    Filament/      HelpdeskPanelProvider  (/helpdesk path)
  Http/
    Middleware/    EnsureUserRole  (role.user alias — user-facing gate)
  Livewire/
    Pages/         Dashboard, Tickets/Create, Tickets/Show

database/migrations/
  2026_03_21_000001_create_departments_table
  2026_03_21_000002_add_role_department_to_users_table
  2026_03_21_000003_create_tickets_table
  2026_03_21_000004_create_ticket_messages_table
  2026_03_21_000005_create_ticket_attachments_table

.antigravity/skills/
  database_schema.md           ← DB schema, enums, relationships
  filament_conventions.md      ← Filament API rules, property types, install steps
  livewire_flux_frontend.md    ← User frontend: routes, components, file uploads, Flux UI
  messaging_and_ratings.md     ← Documentation on the user ticket messaging thread and rating system
  authorization_and_widgets.md ← Ticket authorization policies and Filament widget structure
  notifications_and_observers.md ← Documentation on email notification triggers and Eloquent Observers
  ui_and_branding.md           ← Reganta brand colors, Tailwind config, and Filament branding settings
  dashboard_reports_and_i18n.md ← Translation strategy and advanced reporting metrics for the dashboard
  audit_trail_pattern.md        ← Audit trail (history log) patterns and implementation rules
```

---

## Agent Skill Directives

> **CRITICAL**: Before performing any of the following actions, you MUST first read the corresponding skill file.

| Action | Read first |
|--------|-----------|
| Writing/editing migrations | `.antigravity/skills/database_schema.md` |
| Querying models or adding relationships | `.antigravity/skills/database_schema.md` |
| Creating/editing Filament resources, pages, or panels | `.antigravity/skills/filament_conventions.md` |
| Adding Filament form fields, table columns, or filters | `.antigravity/skills/filament_conventions.md` |
| Creating/editing Livewire user pages or file uploads | `.antigravity/skills/livewire_flux_frontend.md` |
| Modifying sidebar nav or Flux UI components | `.antigravity/skills/livewire_flux_frontend.md` |
| Working with Ticket Policies or Filament Widgets | `.antigravity/skills/authorization_and_widgets.md` |
| Modifying ticket notifications or observer logic | `.antigravity/skills/notifications_and_observers.md` |
| Changing locale strings or user policy access rules | `.antigravity/skills/i18n_and_user_management.md` |
| Modifying UI colors, branding, and loaders | `.antigravity/skills/ui_and_branding.md` |
| Handling translation keys or reporting calculations | `.antigravity/skills/dashboard_reports_and_i18n.md` |
| Working with History Logs / Audit Trails | `.antigravity/skills/audit_trail_pattern.md` |

---

## Common Artisan Commands

```bash
php artisan migrate:fresh          # reset and re-run all migrations
php artisan make:filament-resource # scaffold a new Filament resource
php artisan make:filament-user     # create an IT staff user for panel login
php artisan serve                  # start dev server
```

## Development Server

The Filament IT panel is available at: `http://localhost:8000/helpdesk`

---
## What Is NOT Built Yet

All defined baseline features are complete!
