<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ITPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('IT Performance'))
            ->query(
                User::query()->where('role', UserRole::ItStaff->value)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Staff Name')),
                    
                Tables\Columns\TextColumn::make('resolved_tickets_count')
                    ->label(__('Resolved Tickets'))
                    ->state(function (User $record): int {
                        return Ticket::where('assignee_id', $record->id)
                            ->whereNotNull('resolved_at')
                            ->count();
                    }),
                    
                Tables\Columns\TextColumn::make('avg_resolution_time')
                    ->label(__('Average Resolution Time'))
                    ->state(function (User $record): string {
                        $resolved = Ticket::where('assignee_id', $record->id)
                            ->whereNotNull('resolved_at')
                            ->get(['created_at', 'resolved_at']);
                            
                        if ($resolved->isEmpty()) {
                            return __('N/A');
                        }
                        
                        $totalMinutes = $resolved->reduce(fn ($carry, $ticket) => $carry + $ticket->created_at->diffInMinutes($ticket->resolved_at), 0);
                        $avgMinutes = $totalMinutes / $resolved->count();
                        
                        $hours = floor($avgMinutes / 60);
                        $minutes = round($avgMinutes % 60);
                        
                        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                    }),
            ])
            ->paginated(false);
    }
}
