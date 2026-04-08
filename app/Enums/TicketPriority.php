<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketPriority: string implements HasLabel, HasColor
{
    case Normal = 'normal';
    case Urgent = 'urgent';
    case Fire   = 'fire';

    public function getLabel(): string
    {
        return match($this) {
            TicketPriority::Normal => __('ticket_priority.normal'),
            TicketPriority::Urgent => __('ticket_priority.urgent'),
            TicketPriority::Fire   => __('ticket_priority.fire'),
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            TicketPriority::Normal => 'success',
            TicketPriority::Urgent => 'warning',
            TicketPriority::Fire   => 'danger',
        };
    }
}
