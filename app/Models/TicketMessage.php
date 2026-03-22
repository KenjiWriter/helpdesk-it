<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TicketMessageObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TicketMessageObserver::class)]
#[Fillable(['ticket_id', 'user_id', 'body'])]
class TicketMessage extends Model
{
    /** @use HasFactory<\Database\Factories\TicketMessageFactory> */
    use HasFactory;
    // ─── Relationships ────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
