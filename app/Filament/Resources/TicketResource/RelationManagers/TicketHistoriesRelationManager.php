<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Historia zgłoszenia');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only, no form needed
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Data'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('Użytkownik'))
                    ->default('System')
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('Zdarzenie'))
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Read-only
            ])
            ->actions([
                // Read-only
            ])
            ->bulkActions([
                // Read-only
            ]);
    }
}
