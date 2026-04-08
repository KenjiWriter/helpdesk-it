<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Enums\TicketStatus;
use App\Services\TicketService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

use Livewire\WithFileUploads;

new #[Title('Podgląd zgłoszenia')] class extends Component {
    use WithFileUploads;

    public Ticket $ticket;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

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

        // Clear the unread-reply flag via the Service Layer when the user opens the ticket.
        if ($this->ticket->has_unread_reply) {
            app(TicketService::class)->markAsRead($this->ticket);
        }
    }

    public function addMessage(): void
    {
        $this->ticket->refresh();

        if ($this->ticket->status->isTerminal()) {
            return;
        }

        $this->validateOnly('newMessageBody');

        $message = TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'body' => $this->newMessageBody,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->storePublicly('ticket-attachments', 'public');
            $this->ticket->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->newMessageBody = '';
        $this->attachments = [];
        $this->ticket->load(['messages.user', 'attachments']);

        session()->flash('success', __('Message added successfully.'));
    }

    public function closeTicket(): void
    {
        $this->ticket->refresh();

        if ($this->ticket->status->isTerminal()) {
            return;
        }

        $this->ticket->update([
            'status' => TicketStatus::Closed,
        ]);

        $this->ticket->histories()->create([
            'user_id' => auth()->id(),
            'description' => __('Status zmieniony na: :status przez użytkownika', ['status' => TicketStatus::Closed->getLabel()]),
        ]);

        session()->flash('success', __('The ticket is now closed.'));
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

        session()->flash('success', __('Thank you for your rating. The ticket is now closed.'));
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <!-- Back button -->
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('dashboard') }}" wire:navigate>
            {{ __('Back to My Tickets') }}
        </flux:button>
        
        @if (! $ticket->status->isTerminal())
            <flux:button variant="danger" wire:click="closeTicket" wire:confirm="{{ __('Are you sure you want to close this ticket?') }}">
                {{ __('Close Ticket') }}
            </flux:button>
        @endif
    </div>

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
                            {{ __('Ticket #:id', ['id' => $ticket->id]) }}
                            <span class="ml-2 font-normal text-zinc-400">— {{ $ticket->category->getLabel() }}</span>
                        </flux:heading>
                        <flux:text size="sm" class="mt-0.5 text-zinc-500 dark:text-zinc-400">
                            {{ __('Submitted :date', ['date' => $ticket->created_at->format('d M Y, H:i')]) }}
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
                            {{ __(':priority Priority', ['priority' => $ticket->priority->getLabel()]) }}
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
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">{{ __('Department') }}</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->department?->name ?? '—' }}
                    </flux:text>
                </div>
                <div class="px-6 py-4">
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">{{ __('Hardware / Asset') }}</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->hardware_name ?? '—' }}
                    </flux:text>
                </div>
            </div>

            @if ($ticket->assignee)
                <div class="border-t border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <flux:text size="xs" class="font-medium uppercase tracking-wider text-zinc-400">{{ __('Assigned IT Technician') }}</flux:text>
                    <flux:text class="mt-1 font-medium text-zinc-900 dark:text-white">
                        {{ $ticket->assignee->name }}
                    </flux:text>
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="mb-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-6 py-3 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Description') }}</flux:heading>
            </div>
            <div class="px-6 py-4">
                <flux:text class="whitespace-pre-wrap leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $ticket->description }}</flux:text>
            </div>
        </div>

        <!-- Attachments -->
        @if ($ticket->attachments->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-6 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Attachments (:count)', ['count' => $ticket->attachments->count()]) }}</flux:heading>
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
                                {{ __('Download') }}
                            </flux:button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Messages Thread -->
        <div class="mt-8 space-y-6">
            <flux:heading size="lg">{{ __('Messages') }}</flux:heading>

            @if ($ticket->messages->isEmpty())
                <flux:text class="text-zinc-500">{{ __('No messages yet.') }}</flux:text>
            @else
                <div class="space-y-4">
                    @foreach ($ticket->messages as $message)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-2 flex items-center justify-between">
                                <flux:text class="font-medium text-zinc-900 dark:text-white">
                                    {{ $message->user->name }}
                                    @if ($message->user->role !== \App\Enums\UserRole::User)
                                    <span class="ml-2 inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ __('Staff') }}</span>
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
                    <flux:heading size="sm" class="mb-4">{{ __('Post a Reply') }}</flux:heading>
                    <form wire:submit="addMessage" class="space-y-4">
                        <flux:field>
                            <flux:textarea wire:model="newMessageBody" rows="4" :placeholder="__('Type your message here...')" />
                            <flux:error name="newMessageBody" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Opis / Załączniki (Opcjonalnie)') }}</flux:label>
                            <input type="file" wire:model="attachments" multiple class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                            <flux:error name="attachments.*" />
                        </flux:field>
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">{{ __('Send Message') }}</flux:button>
                        </div>
                    </form>
                </div>
            @elseif ($ticket->status === \App\Enums\TicketStatus::Resolved && $ticket->rating_time === null)
                <!-- Rating Form -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:heading size="sm" class="mb-2">{{ __('Ticket Resolved - Please Rate Our Service') }}</flux:heading>
                    <flux:text size="sm" class="mb-6 text-zinc-500">{{ __('Please provide a rating from 1 (Poor) to 6 (Excellent). Rating this ticket will close it permanently.') }}</flux:text>

                    <form wire:submit="submitRating" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>{{ __('Response Time') }}</flux:label>
                                <flux:select wire:model="ratingTime" :placeholder="__('Select rating...')">
                                    <flux:select.option value="1">1 - {{ __('Very Slow') }}</flux:select.option>
                                    <flux:select.option value="2">2 - {{ __('Slow') }}</flux:select.option>
                                    <flux:select.option value="3">3 - {{ __('Average') }}</flux:select.option>
                                    <flux:select.option value="4">4 - {{ __('Good') }}</flux:select.option>
                                    <flux:select.option value="5">5 - {{ __('Fast') }}</flux:select.option>
                                    <flux:select.option value="6">6 - {{ __('Very Fast') }}</flux:select.option>
                                </flux:select>
                                <flux:error name="ratingTime" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Service Quality') }}</flux:label>
                                <flux:select wire:model="ratingQuality" :placeholder="__('Select rating...')">
                                    <flux:select.option value="1">1 - {{ __('Very Poor') }}</flux:select.option>
                                    <flux:select.option value="2">2 - {{ __('Poor') }}</flux:select.option>
                                    <flux:select.option value="3">3 - {{ __('Average') }}</flux:select.option>
                                    <flux:select.option value="4">4 - {{ __('Good') }}</flux:select.option>
                                    <flux:select.option value="5">5 - {{ __('Excellent') }}</flux:select.option>
                                    <flux:select.option value="6">6 - {{ __('Outstanding') }}</flux:select.option>
                                </flux:select>
                                <flux:error name="ratingQuality" />
                            </flux:field>
                        </div>
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">{{ __('Submit Rating') }}</flux:button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Informational Callout for Closed Ticket -->
                <flux:callout variant="danger" icon="lock-closed">
                    {{ __('This ticket is :status and cannot receive new replies.', ['status' => strtolower($ticket->status->getLabel())]) }}
                    @if($ticket->rating_time)
                        <span class="mt-1 block">{{ __('Thank you for rating our service: Response Time (:time/6), Quality (:quality/6).', ['time' => $ticket->rating_time, 'quality' => $ticket->rating_quality]) }}</span>
                    @endif
                </flux:callout>
            @endif
        </div>
    </div>
</div>
