---
name: messaging_and_ratings
description: Documentation on the user ticket messaging thread and rating system.
---

# Ticket Messaging and Ratings — IT Helpdesk

> Read this file before modifying ticket interactions or adding new ticket status logic.

---

## Messaging Thread Overview

The messaging thread allows `User`s and `ItStaff`/`Admin`s to communicate within the context of a specific `Ticket`.

- **Model:** `App\Models\TicketMessage`
- **Frontend Component:** `resources/views/pages/tickets/⚡show.blade.php` (for users).
- **Relationships:** A `TicketMessage` belongs to a `Ticket` and to a `User`.
- **Chronological Display:** Messages are displayed below the ticket description and attachments in chronological order.

### Reply Flow (User Context)

1. The Livewire Volt component (`⚡show.blade.php`) handles the `addMessage()` action.
2. The user submits a body via a `<flux:textarea>`.
3. Validation ensures the string is required and max 2000 characters.
4. If valid, a `TicketMessage` is saved linked to the currently authenticated user.
5. **Terminal Safeguard:** If `$ticket->status->isTerminal()` (`Resolved` or `Closed`), the `addMessage()` action will abort early and the thread reply form is hidden from the user interface.

---

## Ticket Rating System

Once a ticket has been marked as `TicketStatus::Resolved`, users have the opportunity to rate the IT interaction.

- **Data Storage:** Ratings are stored directly on the `Ticket` model, avoiding a separate ratings table for simplicity.
  - `rating_time` (int, 1-6 scale)
  - `rating_quality` (int, 1-6 scale)

### Rating Flow (User Context)

1. When a ticket status is `Resolved` and `rating_time` is null, the Livewire user UI dynamically displays the Rating block.
2. Users select a value representing "Response Time" and "Service Quality" using `<flux:select>` components.
3. The `submitRating()` action runs upon form submission.
4. Validation ensures the input is between 1 and 6.
5. **Auto-Closure on Completion:** Submitting a successful rating saves the rating components AND automatically updates the ticket `status` to `TicketStatus::Closed`.
6. Once closed, neither the reply form nor the rating form is visible, replaced by a closed informational readout.

---

## UI Components

This system heavily relies on `Flux UI` elements.
- Validation visual feedback utilizes `<flux:error name="...">` mapped to the corresponding property attributes.
- Informational boxes are built with `<flux:callout>` to indicate closed or resolved states effortlessly.
