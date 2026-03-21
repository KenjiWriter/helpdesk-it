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
            TicketPriority::Normal => 'Normal',
            TicketPriority::Urgent => 'Urgent',
            TicketPriority::Fire   => 'Fire 🔥',
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
