<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('New Ticket')] class extends Component {
    use WithFileUploads;

    public string $priority = 'normal';
    public string $category = '';
    public string $description = '';
    public ?int $department_id = null;
    public string $hardware_name = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    protected function rules(): array
    {
        $priorityValues = implode(',', array_column(TicketPriority::cases(), 'value'));
        $categoryValues = implode(',', array_column(TicketCategory::cases(), 'value'));

        return [
            'priority'      => "required|string|in:{$priorityValues}",
            'category'      => "required|string|in:{$categoryValues}",
            'description'   => 'required|string|min:10|max:5000',
            'department_id' => 'nullable|exists:departments,id',
            'hardware_name' => 'nullable|string|max:255',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:4096',
        ];
    }

    protected function messages(): array
    {
        return [
            'category.required' => 'Please select a problem category.',
            'description.min'   => 'Description must be at least 10 characters.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $ticket = Ticket::create([
            'user_id'       => $user->id,
            'department_id' => $this->department_id,
            'priority'      => $this->priority,
            'category'      => $this->category,
            'status'        => 'new',
            'description'   => $this->description,
            'hardware_name' => $this->hardware_name ?: null,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->storePublicly('ticket-attachments', 'public');

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $user->id,
                'filename'  => $file->getClientOriginalName(),
                'path'      => $path,
                'mime_type' => $file->getMimeType(),
                'size'      => $file->getSize(),
            ]);
        }

        session()->flash('success', "Ticket #{$ticket->id} submitted! We'll get back to you soon.");

        $this->redirect(route('tickets.show', $ticket), navigate: true);
    }

    public function with(): array
    {
        return [
            'priorities'  => TicketPriority::cases(),
            'categories'  => TicketCategory::cases(),
            'departments' => Department::orderBy('name')->get(),
        ];
    }
}; ?>

<div>
    <!-- Page header -->
    <div class="mb-8">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('dashboard') }}" wire:navigate class="mb-4">
            Back to My Tickets
        </flux:button>
        <flux:heading size="xl" level="1">Submit a Support Ticket</flux:heading>
        <flux:subheading>Describe your IT issue and our team will respond as soon as possible.</flux:subheading>
    </div>

    <div class="mx-auto max-w-2xl">
        <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">
            <!-- Priority + Category -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Priority</flux:label>
                    <flux:select wire:model="priority" id="priority">
                        @foreach ($priorities as $p)
                            <flux:select.option value="{{ $p->value }}">{{ $p->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="priority" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        Problem Category
                        <flux:badge variant="outline" size="sm" class="ml-1">required</flux:badge>
                    </flux:label>
                    <flux:select wire:model="category" id="category" placeholder="Select a category…">
                        @foreach ($categories as $c)
                            <flux:select.option value="{{ $c->value }}">{{ $c->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>
            </div>

            <!-- Description -->
            <flux:field>
                <flux:label>
                    Description
                    <flux:badge variant="outline" size="sm" class="ml-1">required</flux:badge>
                </flux:label>
                <flux:textarea
                    wire:model="description"
                    id="description"
                    rows="6"
                    placeholder="What happened? Since when? Which software or hardware is affected?"
                />
                <flux:description>Minimum 10 characters. Be as specific as possible.</flux:description>
                <flux:error name="description" />
            </flux:field>

            <!-- Department + Hardware Name -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Department</flux:label>
                    <flux:select wire:model="department_id" id="department_id" placeholder="Select your department…">
                        @foreach ($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="department_id" />
                </flux:field>

                <flux:field>
                    <flux:label>
                        Hardware Name / ID
                        <flux:badge variant="outline" size="sm" class="ml-1">optional</flux:badge>
                    </flux:label>
                    <flux:input
                        wire:model="hardware_name"
                        id="hardware_name"
                        type="text"
                        placeholder="e.g. PC-042, Printer HP LaserJet"
                    />
                    <flux:description>If hardware-related, provide the asset name or ID.</flux:description>
                    <flux:error name="hardware_name" />
                </flux:field>
            </div>

            <!-- File Attachments -->
            <flux:field>
                <flux:label>
                    Attachments
                    <flux:badge variant="outline" size="sm" class="ml-1">optional</flux:badge>
                </flux:label>
                <input
                    type="file"
                    wire:model="attachments"
                    id="attachments"
                    multiple
                    accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                    class="block w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-700 file:mr-4 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                />
                <flux:description>Screenshots or photos. JPG, PNG, GIF, WebP, PDF — max 4 MB each.</flux:description>
                <flux:error name="attachments.*" />

                @if ($attachments)
                    <ul class="mt-2 space-y-1">
                        @foreach ($attachments as $attachment)
                            <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon.paper-clip class="size-4 shrink-0" />
                                {{ $attachment->getClientOriginalName() }}
                                <span class="text-zinc-400">({{ number_format($attachment->getSize() / 1024, 1) }} KB)</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div wire:loading wire:target="attachments" class="mt-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <flux:icon.arrow-path class="size-4 animate-spin" />
                    Uploading files…
                </div>
            </flux:field>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button variant="ghost" href="{{ route('dashboard') }}" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Submit Ticket</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <flux:icon.arrow-path class="size-4 animate-spin" />
                        Submitting…
                    </span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
