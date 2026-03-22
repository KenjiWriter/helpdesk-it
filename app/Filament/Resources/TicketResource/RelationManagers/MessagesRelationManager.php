<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\Ticket;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Messages');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                Textarea::make('body')
                    ->label(__('Message'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Author'))
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('body')
                    ->label(__('Message'))
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label(__('Posted At'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('New Message'))
                    ->after(function (RelationManager $livewire) {
                        $ticket = $livewire->getOwnerRecord();
                        if ($ticket instanceof Ticket) {
                            $ticket->histories()->create([
                                'user_id' => auth()->id(),
                                'description' => __('Dodano nową wiadomość'),
                            ]);

                            if ($ticket->assignee_id === null) {
                                $ticket->update(['assignee_id' => auth()->id()]);
                            }
                        }
                    }),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
