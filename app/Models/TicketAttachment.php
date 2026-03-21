<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['ticket_id', 'user_id', 'filename', 'path', 'mime_type', 'size'])]
class TicketAttachment extends Model
{
    // ─── Relationships ────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /**
     * Get the public URL for this attachment.
     */
    public function url(): string
    {
        return Storage::url($this->path);
    }
}
