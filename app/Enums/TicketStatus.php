<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasLabel, HasColor
{
    case New        = 'new';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';
    case Closed     = 'closed';

    public function getLabel(): string
    {
        return match($this) {
            TicketStatus::New        => __('Nowy'),
            TicketStatus::InProgress => __('W trakcie'),
            TicketStatus::Resolved   => __('Rozwiązany'),
            TicketStatus::Closed     => __('Zamknięty'),
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            TicketStatus::New        => 'info',
            TicketStatus::InProgress => 'primary',
            TicketStatus::Resolved   => 'success',
            TicketStatus::Closed     => 'gray',
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
