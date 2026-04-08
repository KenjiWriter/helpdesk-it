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

    protected ?string $heading = 'Podgląd Zgłoszenia';

    public function getTitle(): string
    {
        return __('Podgląd Zgłoszenia');
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return 'full';
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->has_unread_user_reply) {
            app(\App\Services\TicketService::class)->markAsReadByItStaff($this->record);
        }
    }

    protected function getHeaderActions(): array
    {
        $statusActions = array_map(function (\App\Enums\TicketStatus $status) {
            return \Filament\Actions\Action::make('setStatus' . $status->name)
                ->label($status->getLabel())
                ->color($status->getColor())
                ->outlined(fn ($record) => $record->status !== $status) // highlight current status
                ->form([
                    \Filament\Forms\Components\Textarea::make('message')
                        ->label(__('Wiadomość do zgłaszającego (opcjonalnie)'))
                ])
                ->action(function (array $data, $record) use ($status) {
                    $updateData = ['status' => $status];
                    
                    if ($status === \App\Enums\TicketStatus::Resolved && $record->status !== \App\Enums\TicketStatus::Resolved) {
                        $updateData['resolved_at'] = now();
                    }

                    $record->update($updateData);

                    $record->histories()->create([
                        'user_id' => auth()->id(),
                        'description' => __('Status zmieniony na: :status', ['status' => $status->getLabel()]),
                    ]);

                    if (!empty($data['message'])) {
                        \App\Models\TicketMessage::create([
                            'ticket_id' => $record->id,
                            'user_id' => auth()->id(),
                            'body' => $data['message'],
                        ]);

                        $record->histories()->create([
                            'user_id' => auth()->id(),
                            'description' => __('Dodano nową wiadomość'),
                        ]);
                    }
                    \Filament\Notifications\Notification::make()->success()->title(__('Status zaktualizowany'))->send();
                });
        }, \App\Enums\TicketStatus::cases());

        return array_merge([
            Action::make('assignToMe')
                ->label(__('Assign to me'))
                ->icon('heroicon-o-user-plus')
                ->hidden(fn (Ticket $record) => $record->assignee_id === auth()->id())
                ->action(function (Ticket $record) {
                    $record->update(['assignee_id' => auth()->id()]);
                    $record->histories()->create([
                        'user_id' => auth()->id(),
                        'description' => __('Przypisano do: :user', ['user' => auth()->user()->name]),
                    ]);
                    Notification::make()
                        ->title(__('Ticket assigned to you.'))
                        ->success()
                        ->send();
                }),
        ], $statusActions, [
            DeleteAction::make(),
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-assign if not assigned currently
        if (empty($data['assignee_id']) && $this->record->assignee_id === null) {
            $data['assignee_id'] = auth()->id();
            
            $this->record->histories()->create([
                'user_id' => auth()->id(),
                'description' => __('Automatycznie przypisano do: :user', ['user' => auth()->user()->name]),
            ]);
        }

        // Auto-set resolved_at when status transitions to Resolved (only if submitted in form data)
        if (
            array_key_exists('status', $data)
            && $data['status'] === TicketStatus::Resolved->value
            && $this->record->status !== TicketStatus::Resolved
            && $this->record->resolved_at === null
        ) {
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
