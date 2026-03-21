<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasLabel, HasColor
{
    case New           = 'new';
    case InProgress    = 'in_progress';
    case WaitingOnUser = 'waiting_on_user';
    case Suspended     = 'suspended';
    case Resolved      = 'resolved';
    case Closed        = 'closed';

    public function getLabel(): string
    {
        return match($this) {
            TicketStatus::New           => 'New',
            TicketStatus::InProgress    => 'In Progress',
            TicketStatus::WaitingOnUser => 'Waiting on User',
            TicketStatus::Suspended     => 'Suspended',
            TicketStatus::Resolved      => 'Resolved',
            TicketStatus::Closed        => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            TicketStatus::New           => 'info',
            TicketStatus::InProgress    => 'primary',
            TicketStatus::WaitingOnUser => 'warning',
            TicketStatus::Suspended     => 'gray',
            TicketStatus::Resolved      => 'success',
            TicketStatus::Closed        => 'gray',
        };
    }

    public function isTerminal(): bool
    {
        return match($this) {
            TicketStatus::Resolved, TicketStatus::Closed => true,
            default                                      => false,
        };
    }
}
