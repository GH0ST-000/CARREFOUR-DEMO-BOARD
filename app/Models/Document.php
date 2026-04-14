<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $batch_id
 * @property string $type
 * @property string|null $description
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $source
 * @property string|null $external_reference
 * @property int|null $uploaded_by_user_id
 * @property Carbon $uploaded_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Product|null $product
 * @property-read Batch|null $batch
 * @property-read User|null $uploadedBy
 */
#[Fillable([
    'product_id',
    'batch_id',
    'type',
    'description',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size_bytes',
    'source',
    'external_reference',
    'uploaded_by_user_id',
    'uploaded_at',
])]
final class Document extends Model
{
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }
}
