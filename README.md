# 🏗️ Enterprise IT Helpdesk

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament 5](https://img.shields.io/badge/Filament-5.x-F37021?style=for-the-badge&logo=filament)](https://filamentphp.com)
[![Livewire 4](https://img.shields.io/badge/Livewire-4.x-4e56a6?style=for-the-badge&logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=for-the-badge&logo=tailwindcss)](https://tailwindcss.com)

A highly customizable, enterprise-grade IT Helpdesk ecosystem designed for any medium-to-large organization. This system provides a seamless, high-performance bridge between employees and the IT support department, featuring a state-of-the-art dual-interface architecture.

---

## 🚀 Project Overview

The Enterprise IT Helpdesk is a robust service management platform built for flexibility and scale. It leverages the latest PHP 8.3 and Laravel 12 features to provide a professional, highly interactive experience that can be easily tailored to any corporate environment.

---

## 🌐 Live Demo

Experience the full capabilities of the Enterprise IT Helpdesk at **[https://it-helpdesk.cerasusdigital.pl](https://it-helpdesk.cerasusdigital.pl)**.

### How to explore the demo:

1. **Step 1 (Employee Portal):** Go to [https://it-helpdesk.cerasusdigital.pl/dashboard](https://it-helpdesk.cerasusdigital.pl/dashboard) and log in as a regular user (`user1@example.com` / Password: `password`). Create a new support ticket and explore the user dashboard.
2. **Step 2 (IT Control Center):** Log out, then go to [https://it-helpdesk.cerasusdigital.pl/helpdesk](https://it-helpdesk.cerasusdigital.pl/helpdesk). Log in as an IT Technician (`it1@example.com` / Password: `password`) or Admin (`admin@example.com` / Password: `password`).
3. **Step 3 (Resolve):** Claim the newly created ticket, use the Interactive Status Pipeline to change its status, add a message, and observe the automated Audit Trail recording your actions.

---

## ✨ Key Features

### 🖥️ Dual-Interface Architecture
- **Employee Portal**: A modern, high-speed frontend built with **Livewire 4**, **Volt**, and **Flux UI**. Employees can submit tickets, attach evidence, and chat in real-time with technicians.
- **IT Control Center**: A professional **Filament 5.4.1** administration panel for IT Staff and Admins. It features advanced resource management, filtering, and real-time activity streams.

### 🔄 Interactive Status Pipeline
Tickets follow a strict, logical lifecycle to ensure no request is lost:
`New` ➡️ `In Progress` ➡️ `Waiting on User` ➡️ `Suspended` ➡️ `Resolved` ➡️ `Closed`.

### 📜 Tamper-Proof Audit Trail
Every action (status change, assignment, new message) is automatically logged into a read-only **Ticket History** log via Eloquent Observers. This ensures a 100% reliable audit trail for compliance and quality assurance.

### 📊 Advanced Reporting & Analytics
- **IT Performance Tracking**: Real-time metrics on resolution times and staff workload.
- **Resolution SLAs**: Calculation of average resolution times across all tickets, formatted for executive review.
- **Visual Dashboards**: Interactive widgets showing "Resolved Today", "Urgent/Fire" tickets, and latest system activity.

### 🎨 Customizable Corporate Branding
The UI is fully tailorable to any corporate identity. By default, it utilizes a primary accent color (**Orange #F37021**) and high-quality typography, both of which serve as an easily interchangeable theme to match any brand guidelines.
- **Dynamic Branding Panel**: Admins can instantly change the Application Name, upload a custom brand Logo, and activate a "Show Logo Only" mode to hide text. All of this can be managed directly from the Filament Control Center (`Ustawienia wyglądu`) without touching any code or configuration files.

---

## 🛠️ Tech Stack

- **Core**: Laravel 12.x / PHP 8.3+
- **Frontend (Users)**: Livewire 4, Volt (Functional Components), Flux UI
- **Backend (IT Staff)**: Filament 5.4.1 (Latest)
- **Styling**: Tailwind CSS 4.x
- **Database**: SQLite (Development) / PostgreSQL (Production ready)
- **Security**: Laravel Fortify with Two-Factor Authentication (2FA) support

---

## ⚙️ Setup & Installation

Follow these steps to get the IT Helpdesk running locally:

1. **Clone & Install**:
   ```bash
   git clone <repository-url>
   composer install
   npm install && npm run build
   ```

2. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Initialize Database & Seed Realistic Data**:
   This project includes a **Time-Traveling Seeder** that generates a realistic historical dataset.
   ```bash
   php artisan migrate:fresh --seed
   ```
   *This command will generate 30 tickets with distributed historical events across the last 30 days, providing a perfect "pre-populated" environment for testing.*

---

## 🔐 Test Credentials

| Role | Email | Password | Panel Access |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@example.com` | `password` | `/helpdesk` |
| **IT Staff** | `it1@example.com` | `password` | `/helpdesk` |
| **Regular User** | `user1@example.com` | `password` | `/dashboard` |

---

## 🏛️ Architectural Decisions

- **Custom Relation Managers**: Used for Ticket History and Messaging threads to keep the UI clean and contextual.
- **Single-Column Form Layouts**: Filament forms are optimized into single-column sections for maximum clarity and mobile-friendliness for technicians in the field.
- **Decoupled Business Logic**: Status updates and notifications are handled via **Eloquent Observers** (`TicketObserver`, `TicketMessageObserver`), keeping controllers and components thin.
- **Flux Component Synergy**: Use of the Flux UI library ensures a premium, high-interaction frontend consistent with modern web standards.

---
*Developed with excellence for enterprise service management.*
