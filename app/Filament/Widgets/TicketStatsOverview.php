<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $resolvedTickets = Ticket::whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
        
        $avgResolutionTime = __('N/A');
        if ($resolvedTickets->isNotEmpty()) {
            $totalMinutes = $resolvedTickets->reduce(function ($carry, $ticket) {
                return $carry + $ticket->created_at->diffInMinutes($ticket->resolved_at);
            }, 0);
            
            $avgMinutes = $totalMinutes / $resolvedTickets->count();
            
            $hours = floor($avgMinutes / 60);
            $minutes = round($avgMinutes % 60);
            
            if ($hours > 0) {
                $avgResolutionTime = "{$hours}h {$minutes}m";
            } else {
                $avgResolutionTime = "{$minutes}m";
            }
        }

        return [
            Stat::make(__('Total Tickets'), Ticket::count())
                ->description(__('All tickets in the system'))
                ->descriptionIcon('heroicon-m-inbox', 'before')
                ->color('primary'),

            Stat::make(__('Resolved Tickets'), Ticket::where('status', TicketStatus::Resolved)->count())
                ->description(__('Tickets that have been resolved'))
                ->descriptionIcon('heroicon-m-check-circle', 'before')
                ->color('success'),

            Stat::make(__('Unresolved Tickets'), Ticket::query()->open()->count())
                ->description(__('Tickets awaiting resolution'))
                ->descriptionIcon('heroicon-m-exclamation-triangle', 'before')
                ->color('danger'),

            Stat::make(__('Global Average Resolution Time'), $avgResolutionTime)
                ->description(__('Across all resolved tickets'))
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->color('info'),
        ];
    }
}
