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
            TicketCategory::Access   => __('ticket_category.access'),
            TicketCategory::Hardware => __('ticket_category.hardware'),
            TicketCategory::Internet => __('ticket_category.internet'),
            TicketCategory::Optima   => __('ticket_category.optima'),
            TicketCategory::Grid     => __('ticket_category.grid'),
            TicketCategory::Other    => __('ticket_category.other'),
        };
    }
}
