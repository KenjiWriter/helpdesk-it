<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TicketObserver::class)]
#[Fillable([
    'user_id',
    'department_id',
    'assignee_id',
    'priority',
    'category',
    'status',
    'description',
    'hardware_name',
    'resolved_at',
    'rating_time',
    'rating_quality',
    'has_unread_reply',
])]
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority'         => TicketPriority::class,
            'category'         => TicketCategory::class,
            'status'           => TicketStatus::class,
            'resolved_at'      => 'datetime',
            'has_unread_reply' => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /** The user who created the ticket. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The IT staff member assigned to this ticket. */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /** Filter to non-terminal tickets (not Resolved or Closed). */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ]);
    }

    /** Filter to tickets assigned to a specific user. */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assignee_id', $userId);
    }
}
