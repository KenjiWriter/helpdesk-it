<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TicketCategory: string implements HasLabel
{
    case Access   = 'access';
    case Hardware = 'hardware';
    case Internet = 'internet';
    case Optima   = 'optima';
    case Grid     = 'grid';
    case Other    = 'other';

    public function getLabel(): string
    {
        return match($this) {
            TicketCategory::Access   => 'Access',
            TicketCategory::Hardware => 'Hardware',
            TicketCategory::Internet => 'Internet',
            TicketCategory::Optima   => 'Optima',
            TicketCategory::Grid     => 'Grid',
            TicketCategory::Other    => 'Other',
        };
    }
}
