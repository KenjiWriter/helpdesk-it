<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ticket Details')] class extends Component {
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $this->ticket = $ticket->load(['department', 'attachments', 'assignee']);
    }
}; ?>

<div>
    <!-- Back button -->
    <flux:button variant="ghost" icon="arrow-left" href="{{ route('dashboard') }}" wire:navigate class="mb-6">
        Back to My Tickets
    </flux:button>

    <!-- Flash success -->
    @if (session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mx-auto max-w-3xl">
        <!-- Header card -->
        <div class="mb-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg" level="1">
                            Ticket #{{ $ticket->id }}
                            <span class="ml-2 font-normal text-zinc-400">— {{ $ticket->category->getLabel() }}</span>
                        </flux:heading>
                        <flux:text size="sm" class="mt-0.5 text-zinc-500 dark:text-zinc-400">
                            Submitted {{ $ticket->created_at->format('d M Y, H:i') }}
                            · {{ $ticket->created_at->diffForHumans() }}
                        </flux:text>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $priorityColors = [
                                'normal' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                'urgent' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                'fire'   => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                            ];
                            $statusColors = [
                                'new'             => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                'in_progress'     => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                'waiting_on_user' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                'suspended'       => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
                                'resolved'        => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                'closed'          => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $priorityColors[$ticket->priority->value] ?? '' }}">
                            {{ $ticket->priority->getLabel() }} Priority
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$ticket->status->value] ?? '' }}">
                            {{ $ticket->status->getLabel() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Details grid -->
            <div class="grid grid-cols-1 divide-y divide-zinc-100 dark:divide-zinc-800 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                <div class="px-6 py-4">
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">Department</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->department?->name ?? '—' }}
                    </flux:text>
                </div>
                <div class="px-6 py-4">
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">Hardware / Asset</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->hardware_name ?? '—' }}
                    </flux:text>
                </div>
            </div>

            @if ($ticket->assignee)
                <div class="border-t border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">Assigned IT Technician</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->assignee->name }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="mb-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-6 py-3 dark:border-zinc-700">
                <flux:heading size="sm">Description</flux:heading>
            </div>
            <div class="px-6 py-4">
                <flux:text class="whitespace-pre-wrap leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $ticket->description }}</flux:text>
            </div>
        </div>

        <!-- Attachments -->
        @if ($ticket->attachments->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-6 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">Attachments ({{ $ticket->attachments->count() }})</flux:heading>
                </div>
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($ticket->attachments as $attachment)
                        <li class="flex items-center justify-between px-6 py-3">
                            <div class="flex items-center gap-3">
                                <flux:icon.paper-clip class="size-5 shrink-0 text-zinc-400" />
                                <div>
                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $attachment->filename }}</flux:text>
                                    <flux:text size="xs" class="text-zinc-400">
                                        {{ $attachment->mime_type }} · {{ number_format($attachment->size / 1024, 1) }} KB
                                    </flux:text>
                                </div>
                            </div>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="arrow-down-tray"
                                href="{{ Storage::disk('public')->url($attachment->path) }}"
                                target="_blank"
                            >
                                Download
                            </flux:button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
