<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Open Tickets', Ticket::query()->open()->count())
                ->description('All unresolved and unclosed tickets')
                ->descriptionIcon('heroicon-m-inbox', 'before')
                ->color('primary'),

            Stat::make('Resolved Today', Ticket::where('status', TicketStatus::Resolved)->whereDate('resolved_at', today())->count())
                ->description('Tickets resolved so far today')
                ->descriptionIcon('heroicon-m-check-circle', 'before')
                ->color('success'),

            Stat::make('Urgent/Fire Tickets', Ticket::query()->open()->whereIn('priority', [TicketPriority::Urgent, TicketPriority::Fire])->count())
                ->description('Open tickets requiring immediate attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle', 'before')
                ->color('danger'),
        ];
    }
}
