<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return 'full';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Default status to 'new' when IT staff creates a ticket on behalf of a user
        $data['status'] ??= TicketStatus::New->value;

        return $data;
    }
}
