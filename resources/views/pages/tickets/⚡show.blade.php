<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Ticket Details')] class extends Component {
    public Ticket $ticket;

    #[Validate('required|string|max:2000')]
    public string $newMessageBody = '';

    #[Validate('required|integer|between:1,6')]
    public ?int $ratingTime = null;

    #[Validate('required|integer|between:1,6')]
    public ?int $ratingQuality = null;

    public function mount(Ticket $ticket): void
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $ticket);

        $this->ticket = $ticket->load(['department', 'attachments', 'assignee', 'messages.user']);
    }

    public function addMessage(): void
    {
        $this->ticket->refresh();

        if ($this->ticket->status->isTerminal()) {
            return;
        }

        $this->validateOnly('newMessageBody');

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'body' => $this->newMessageBody,
        ]);

        $this->newMessageBody = '';
        $this->ticket->load('messages.user');

        session()->flash('success', 'Message added successfully.');
    }

    public function submitRating(): void
    {
        $this->ticket->refresh();

        if ($this->ticket->status !== TicketStatus::Resolved) {
            return;
        }

        if ($this->ticket->rating_time !== null) {
            return;
        }

        $this->validateOnly('ratingTime');
        $this->validateOnly('ratingQuality');

        $this->ticket->update([
            'rating_time' => $this->ratingTime,
            'rating_quality' => $this->ratingQuality,
            'status' => TicketStatus::Closed, // Auto-close on rating as requested
        ]);

        session()->flash('success', 'Thank you for your rating. The ticket is now closed.');
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

        <!-- Messages Thread -->
        <div class="mt-8 space-y-6">
            <flux:heading size="lg">Messages</flux:heading>

            @if ($ticket->messages->isEmpty())
                <flux:text class="text-zinc-500">No messages yet.</flux:text>
            @else
                <div class="space-y-4">
                    @foreach ($ticket->messages as $message)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-2 flex items-center justify-between">
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $message->user->name }}
                                    @if ($message->user->role !== \App\Enums\UserRole::User)
                                        <span class="ml-2 inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Staff</span>
                                    @endif
                                </flux:text>
                                <flux:text size="sm" class="text-zinc-500">
                                    {{ $message->created_at->format('M d, H:i') }}
                                </flux:text>
                            </div>
                            <flux:text class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $message->body }}</flux:text>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Forms: Reply or Rating -->
        <div class="mt-8">
            @if (! $ticket->status->isTerminal())
                <!-- Reply Form -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:heading size="sm" class="mb-4">Post a Reply</flux:heading>
                    <form wire:submit="addMessage" class="space-y-4">
                        <flux:field>
                            <flux:textarea wire:model="newMessageBody" rows="4" placeholder="Type your message here..." />
                            <flux:error name="newMessageBody" />
                        </flux:field>
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">Send Message</flux:button>
                        </div>
                    </form>
                </div>
            @elseif ($ticket->status === \App\Enums\TicketStatus::Resolved && $ticket->rating_time === null)
                <!-- Rating Form -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:heading size="sm" class="mb-2">Ticket Resolved - Please Rate Our Service</flux:heading>
                    <flux:text size="sm" class="mb-6 text-zinc-500">Please provide a rating from 1 (Poor) to 6 (Excellent). Rating this ticket will close it permanently.</flux:text>

                    <form wire:submit="submitRating" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>Response Time</flux:label>
                                <flux:select wire:model="ratingTime" placeholder="Select rating...">
                                    <flux:select.option value="1">1 - Very Slow</flux:select.option>
                                    <flux:select.option value="2">2 - Slow</flux:select.option>
                                    <flux:select.option value="3">3 - Average</flux:select.option>
                                    <flux:select.option value="4">4 - Good</flux:select.option>
                                    <flux:select.option value="5">5 - Fast</flux:select.option>
                                    <flux:select.option value="6">6 - Very Fast</flux:select.option>
                                </flux:select>
                                <flux:error name="ratingTime" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Service Quality</flux:label>
                                <flux:select wire:model="ratingQuality" placeholder="Select rating...">
                                    <flux:select.option value="1">1 - Very Poor</flux:select.option>
                                    <flux:select.option value="2">2 - Poor</flux:select.option>
                                    <flux:select.option value="3">3 - Average</flux:select.option>
                                    <flux:select.option value="4">4 - Good</flux:select.option>
                                    <flux:select.option value="5">5 - Excellent</flux:select.option>
                                    <flux:select.option value="6">6 - Outstanding</flux:select.option>
                                </flux:select>
                                <flux:error name="ratingQuality" />
                            </flux:field>
                        </div>
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">Submit Rating</flux:button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Informational Callout for Closed Ticket -->
                <flux:callout variant="danger" icon="lock-closed">
                    This ticket is {{ strtolower($ticket->status->getLabel()) }} and cannot receive new replies.
                    @if($ticket->rating_time)
                        <span class="mt-1 block">Thank you for rating our service: Response Time ({{ $ticket->rating_time }}/6), Quality ({{ $ticket->rating_quality }}/6).</span>
                    @endif
                </flux:callout>
            @endif
        </div>
    </div>
</div>
