<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Latest Tickets'))
            ->query(
                Ticket::query()->with('user')->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->width('50px'),

                BadgeColumn::make('status')
                    ->label(__('Status')),

                BadgeColumn::make('priority')
                    ->label(__('Priority')),

                BadgeColumn::make('category')
                    ->label(__('Category')),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Zgłaszający'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Opis problemu')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Opened'))
                    ->dateTime('d M Y, H:i'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Zobacz zgłoszenie')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Ticket $record): string => TicketResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->paginated(false);
    }
}
