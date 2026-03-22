<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\Width;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return 'full';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignToMe')
                ->label(__('Assign to me'))
                ->icon('heroicon-o-user-plus')
                ->hidden(fn (Ticket $record) => $record->assignee_id === auth()->id())
                ->action(function (Ticket $record) {
                    $record->update(['assignee_id' => auth()->id()]);
                    Notification::make()
                        ->title(__('Ticket assigned to you.'))
                        ->success()
                        ->send();
                }),

            Action::make('resolveTicket')
                ->label(__('Resolve Ticket'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->hidden(fn (Ticket $record) => $record->status === TicketStatus::Resolved || $record->status === TicketStatus::Closed)
                ->form([
                    Textarea::make('note')
                        ->label(__('Resolution Note'))
                        ->required()
                        ->rows(4),
                    FileUpload::make('attachment')
                        ->label(__('Attachment'))
                        ->disk('public')
                        ->directory('ticket-attachments')
                        ->nullable(),
                ])
                ->action(function (array $data, Ticket $record) {
                    $record->messages()->create([
                        'user_id' => auth()->id(),
                        'body' => $data['note'],
                    ]);

                    if (!empty($data['attachment'])) {
                        $path = is_array($data['attachment']) ? array_values($data['attachment'])[0] : $data['attachment'];

                        $record->attachments()->create([
                            'user_id' => auth()->id(),
                            'filename' => basename($path),
                            'path' => $path,
                            'mime_type' => Storage::disk('public')->mimeType($path),
                            'size' => Storage::disk('public')->size($path),
                        ]);
                    }

                    $record->update([
                        'status' => TicketStatus::Resolved->value,
                        'resolved_at' => now(),
                        'assignee_id' => $record->assignee_id ?? auth()->id(),
                    ]);

                    Notification::make()
                        ->title(__('Ticket Resolved'))
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-assign if not assigned currently
        if (empty($data['assignee_id']) && $this->record->assignee_id === null) {
            $data['assignee_id'] = auth()->id();
        }

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
