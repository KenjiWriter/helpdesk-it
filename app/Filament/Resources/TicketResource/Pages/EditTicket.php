<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-set resolved_at when status transitions to Resolved
        if (
            $data['status'] === TicketStatus::Resolved->value
            && $this->record->status !== TicketStatus::Resolved
            && $this->record->resolved_at === null
        ) {
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
