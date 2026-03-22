# IT Helpdesk

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5.4.1-FBBF24?style=for-the-badge&logo=filament&logoColor=black)
![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)

## Table of Contents
- [Project Description](#project-description)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Local Installation Guide](#local-installation-guide)
- [Custom CLI Commands](#custom-cli-commands)
- [Application Access](#application-access)

---

## Project Description

This is a tailor-made, internal IT Helpdesk system designed to streamline support ticket management. The application features three distinct roles to strictly enforce access control and operational capabilities:

- **User**: Regular employees who can submit tickets, view their own ticket history via a Livewire frontend, and interact in messaging threads.
- **IT Staff**: Technicians who manage, reply to, and ultimately resolve user tickets via a dedicated admin dashboard (`/helpdesk`).
- **Admin**: System administrators who have read-only statistical overview access to the helpdesk panel and full control over user management.

---

## Key Features

- **Modern User Dashboard & Ticket creation**: Built securely with Livewire 4 + Flux UI.
- **Advanced IT Management Panel**: Powered by the Filament v5.x API for rapid administrative capabilities.
- **Real-time internal messaging thread**: Integrated directly on tickets for seamless communication between users and IT staff.
- **Resolution rating system**: Users can rate interaction (Time & Quality) when a ticket is resolved, automatically triggering Ticket closure.
- **Automated, queued email notifications**: Driven smoothly via Eloquent Observers.
- **Secure Administrator User Management**: Includes a strict `UserPolicy` and a safe Password Reset modal.
- **i18n support**: Fully internationalized with Polish (`pl`) as the default language and English (`en`) as the fallback.

---

## Tech Stack

- **Framework**: Laravel 12
- **Language**: PHP 8.3
- **Frontend Realtime UI**: Livewire 4 + Flux UI
- **Admin System**: Filament 5.4.1
- **Authentication**: Laravel Fortify + TwoFactorAuthenticatable
- **Database**: SQLite (Development environment default)

---

## Local Installation Guide

Follow these step-by-step instructions from a fresh clone to get the application running locally:

1. **Install PHP and Node dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

2. **Environment Setup:**
   Copy the example environment file and generate your application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Note: Ensure your database is configured to use SQLite, and that email queues are set appropriately (`QUEUE_CONNECTION=sync` and `MAIL_MAILER=log` for local testing).*

3. **Database Preparation:**
   Run migrations and seed any necessary default data:
   ```bash
   php artisan migrate:fresh
   ```

4. **Running the Queue Worker:**
   To process automated email notifications triggered by Eloquent Observers in the background, run the queue worker:
   ```bash
   php artisan queue:work
   ```

---

## Custom CLI Commands

To properly bootstrap the initial `admin` or `it_staff` accounts so you can safely access the secure `/helpdesk` administration panel, you can use our built-in customized CLI command: `php artisan user:role {email} {role}`.

**Step 1:** Create a new user manually using Laravel Tinker (or register one through the normal frontend UI if registration is left open).
```bash
php artisan tinker
```
```php
// Inside the Tinker console:
User::create([
    'name' => 'Admin User', 
    'email' => 'admin@example.com', 
    'password' => bcrypt('password')
]);
```

**Step 2:** Assign the appropriate role to the newly created user using the custom CLI command:
```bash
php artisan user:role admin@example.com admin
```
*(Available roles: `user`, `it_staff`, `admin`)*

You can now log in using this account and access the elevated tools.

---

## Application Access

Once the application is served locally (e.g., via `php artisan serve` running on `http://localhost:8000`), you can access the distinct application interfaces:

- **Standard User Interface (Login/Dashboard)**:  
  [http://localhost:8000/](http://localhost:8000/)  
  *(For regular employees — creates and reviews tickets)*

- **IT Management Panel (Admin/IT Staff only)**:  
  [http://localhost:8000/helpdesk](http://localhost:8000/helpdesk)  
  *(For IT Techs and Admins — manage users, view widget stats, and resolve tickets)*
