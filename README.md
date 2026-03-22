# IT Helpdesk

A comprehensive IT Helpdesk ticketing system built with Laravel 12, Livewire 4 (Volt), Flux UI, and Filament PHP 5.

## Key Features

- **Role-Based Access Control**: Strict separation between Users (Frontend Dashboard) and IT Staff/Admins (Filament Panel). 
- **Custom Post-Login Redirection**: Admins and IT Staff are intelligently routed to their administrative panel (`/helpdesk`), while regular users land on the standard Dashboard (`/dashboard`).
- **Ticketing & Messaging**: Full support for creating, assigning, status tracking, and chronological threaded messaging on tickets. Includes a terminal-state auto-lock mechanism upon ticket resolution.
- **Service Rating**: Integrated dynamic rating system for solved tickets allowing users to explicitly rate response time and service quality. This action auto-closes the ticket on submission.
- **File Uploads**: Native ticket attachment support utilizing temporary uploaded files seamlessly integrated with the public disk storage route.
- **Event-Driven Notifications**: Asynchronous email delivery utilizing Eloquent Observers tied strictly to `Ticket` and `TicketMessage` model events (e.g., ticket creation, status updates, new replies).
- **Internationalization (i18n)**: Out-of-the-box Polish (`pl`) default localization with English fallback support.
- **Dynamic Stats Dashboard**: Real-time Filament widgets tracking Open, Resolved Today, and Urgent/Fire priority tickets.
- **Strict User Management**: Admin-only access to manage user accounts with built-in forced password reset functionalities dynamically hashed into the database.

## Architecture & Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 4 (Volt functional API) & Flux UI elements (e.g. `<flux:callout>`, `<flux:textarea>`)
- **Admin Panel**: Filament v5.4.1 
- **Database**: SQLite (or any supported Laravel PDO)
- **Local Settings**: Requires `QUEUE_CONNECTION=sync` & `MAIL_MAILER=log` for local notification testing.

## System Roles & Permissions

Implemented via `UserRole` enum and `TicketPolicy` / `UserPolicy`:

- **User (`user`)**: Can create tickets, view assigned dashboard, and reply to open tickets they own.
- **IT Staff (`it_staff`)**: Can access the `/helpdesk` Filament panel to explicitly manage, resolve, and reply to tickets. Receives staff-focused notifications.
- **Admin (`admin`)**: Inherits all IT Staff abilities, plus full User Management (create users, reset passwords).

## Setup & Installation

1. Clone the repository and install dependencies:
   ```bash
   composer install
   npm install && npm run build
   ```
2. Set up environment variables locally:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Prepare storage links for attachments:
   ```bash
   php artisan storage:link
   ```
4. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```
5. Start the application:
   ```bash
   php artisan serve
   composer run dev
   ```

## Application Access Points
- **Standard User Dashboard**: [http://localhost:8000/dashboard](http://localhost:8000/dashboard)
- **IT/Admin Panel**: [http://localhost:8000/helpdesk](http://localhost:8000/helpdesk)

## Automated Testing

Maintain quality logic via the PHPUnit / Pest testing infrastructure:
- **Notifications**: `php artisan test --filter=NotificationsTest` 
- **Authentication/Redirection**: `php artisan test --filter=AuthenticationTest` and `DashboardTest`

---
*For in-depth architectural decisions, consult the markdown references located in the `.antigravity/skills/` directory.*
