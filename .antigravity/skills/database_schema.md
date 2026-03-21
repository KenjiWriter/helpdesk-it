# Database Schema — IT Helpdesk

> Always read this file before writing queries, migrations, or touching model relationships.

## Enums (`app/Enums/`)

| Enum | Values (raw string) | Implements |
|------|---------------------|------------|
| `UserRole` | `user`, `it_staff`, `admin` | — |
| `TicketPriority` | `normal`, `urgent`, `fire` | `HasLabel`, `HasColor` (Filament) |
| `TicketCategory` | `access`, `hardware`, `internet`, `optima`, `grid`, `other` | `HasLabel` |
| `TicketStatus` | `new`, `in_progress`, `waiting_on_user`, `suspended`, `resolved`, `closed` | `HasLabel`, `HasColor` |

`TicketStatus::isTerminal()` → `true` for `resolved` and `closed`.

---

## Tables

### `users`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigInt PK | |
| `name` | string | |
| `email` | string unique | |
| `password` | string hashed | |
| `role` | string | default `'user'`, cast → `UserRole` |
| `department_id` | FK → departments | nullable, nullOnDelete |
| `email_verified_at` | timestamp | nullable |
| `remember_token` | string | |
| `timestamps` | | |

### `departments`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigInt PK | |
| `name` | string unique | |
| `timestamps` | | |

### `tickets`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigInt PK | |
| `user_id` | FK → users | cascadeOnDelete, cast NOT applied (use relation) |
| `department_id` | FK → departments | nullable, nullOnDelete |
| `assignee_id` | FK → users | nullable, nullOnDelete |
| `priority` | string | default `'normal'`, cast → `TicketPriority` |
| `category` | string | cast → `TicketCategory` |
| `status` | string | default `'new'`, cast → `TicketStatus` |
| `description` | text | |
| `hardware_name` | string | nullable |
| `resolved_at` | timestamp | nullable, cast → `datetime` |
| `rating_time` | tinyInt unsigned | nullable, 1–6 |
| `rating_quality` | tinyInt unsigned | nullable, 1–6 |
| `timestamps` | | |

### `ticket_messages`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigInt PK | |
| `ticket_id` | FK → tickets | cascadeOnDelete |
| `user_id` | FK → users | cascadeOnDelete |
| `body` | text | |
| `timestamps` | | |

### `ticket_attachments`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigInt PK | |
| `ticket_id` | FK → tickets | cascadeOnDelete |
| `user_id` | FK → users | cascadeOnDelete |
| `filename` | string | original filename |
| `path` | string | storage path (use `Storage::url($path)`) |
| `mime_type` | string | nullable |
| `size` | unsignedBigInt | nullable, bytes |
| `timestamps` | | |

---

## Model Relationships

```
User
  ├── belongsTo   Department
  ├── hasMany     Ticket              (as creator, FK: user_id)
  ├── hasMany     Ticket              (as assignee, FK: assignee_id) → assignedTickets()
  └── hasMany     TicketMessage

Department
  ├── hasMany     User
  └── hasMany     Ticket

Ticket
  ├── belongsTo   User               (creator)
  ├── belongsTo   User               (assignee)
  ├── belongsTo   Department
  ├── hasMany     TicketMessage
  └── hasMany     TicketAttachment

TicketMessage
  ├── belongsTo   Ticket
  └── belongsTo   User

TicketAttachment
  ├── belongsTo   Ticket
  └── belongsTo   User
```

## Ticket Scopes

```php
Ticket::query()->open()               // excludes resolved + closed
Ticket::query()->assignedTo($userId)  // filter by assignee_id
```

## Access Control

`User` implements `FilamentUser`. `canAccessPanel()` returns `true` only when
`$this->role` is `UserRole::ItStaff` or `UserRole::Admin`.

Regular users (`UserRole::User`) cannot access the `/helpdesk` panel at all.
