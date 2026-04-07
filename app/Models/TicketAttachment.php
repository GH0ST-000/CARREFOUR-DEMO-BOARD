<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property string $mime_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Ticket $ticket
 */
#[Fillable([
    'ticket_id',
    'file_path',
    'file_name',
    'file_size',
    'mime_type',
])]
final class TicketAttachment extends Model
{
    /** @use HasFactory<TicketAttachmentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Ticket, self>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }
}
