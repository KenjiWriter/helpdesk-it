<?php

use App\Enums\TicketStatus;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Moje zgłoszenia')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $tickets = $user->tickets()
            ->with(['department'])
            ->latest()
            ->paginate(10);

        $totalCount    = $user->tickets()->count();
        $openCount     = $user->tickets()->open()->count();
        $resolvedCount = $user->tickets()->whereIn('status', [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ])->count();
        $unreadCount = $user->tickets()->where('has_unread_reply', true)->count();

        return compact('tickets', 'totalCount', 'openCount', 'resolvedCount', 'unreadCount');
    }
}; ?>

<div>
    <!-- Flash success -->
    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    <!-- Page header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('My Tickets') }}</flux:heading>
            <flux:subheading>{{ __('Track all the IT support requests you have submitted.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" href="{{ route('tickets.create') }}" wire:navigate>
            {{ __('New Ticket') }}
        </flux:button>
    </div>

    <!-- Stats row (4 columns) -->
    <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Total submitted') }}</flux:text>
            <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Currently open') }}</flux:text>
            <p class="mt-1 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $openCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Resolved / Closed') }}</flux:text>
            <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">{{ $resolvedCount }}</p>
        </div>
        {{-- Unread IT replies stat — glows orange when > 0 --}}
        <div class="rounded-xl border p-5 transition-colors {{ $unreadCount > 0 ? 'border-accent/40 bg-accent/5 dark:border-accent/30 dark:bg-accent/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">
            <flux:text size="sm" class="{{ $unreadCount > 0 ? 'text-accent' : 'text-zinc-500 dark:text-zinc-400' }}">
                {{ __('Unread IT Replies') }}
            </flux:text>
            <p class="mt-1 text-3xl font-bold {{ $unreadCount > 0 ? 'text-accent' : 'text-zinc-900 dark:text-white' }}">
                {{ $unreadCount }}
            </p>
        </div>
    </div>

    @if ($tickets->isEmpty())
        <!-- Empty state -->
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-white px-6 py-20 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon.ticket class="mb-4 size-12 text-zinc-400" />
            <flux:heading size="lg">{{ __('No tickets yet') }}</flux:heading>
            <flux:subheading class="mt-1 max-w-sm">
                {{ __('Run into a tech problem? Submit your first support ticket and our IT team will get right on it.') }}
            </flux:subheading>
            <flux:button variant="primary" icon="plus" class="mt-6" href="{{ route('tickets.create') }}" wire:navigate>
                {{ __('Submit a Ticket') }}
            </flux:button>
        </div>
    @else
        <!-- Tickets table -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">#</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Priority') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Department') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Submitted') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($tickets as $ticket)
                        @php $hasUnread = $ticket->has_unread_reply; @endphp
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $hasUnread ? 'ticket-unread-row' : '' }}">
                            <td class="px-4 py-3 font-mono text-zinc-400 dark:text-zinc-500">
                                #{{ $ticket->id }}
                            </td>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    {{ $ticket->category->getLabel() }}
                                    @if ($hasUnread)
                                        <span class="bell-ring-animate inline-flex items-center" title="{{ __('New IT Reply') }}">
                                            <flux:icon.bell class="size-4 text-accent" />
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-accent px-2 py-0.5 text-xs font-semibold text-white">
                                            {{ __('New Reply') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $priorityColors = [
                                        'normal' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'urgent' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                        'fire'   => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityColors[$ticket->priority->value] ?? '' }}">
                                    {{ $ticket->priority->getLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'new'         => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'in_progress' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                        'resolved'    => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'closed'      => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$ticket->status->value] ?? '' }}">
                                    {{ $ticket->status->getLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                                {{ $ticket->department?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                                {{ $ticket->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button
                                    size="sm"
                                    variant="{{ $hasUnread ? 'primary' : 'ghost' }}"
                                    href="{{ route('tickets.show', $ticket) }}"
                                    wire:navigate
                                >
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($tickets->hasPages())
                <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
