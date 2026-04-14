<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $product_id
 * @property int|null $batch_id
 * @property string $batch_number
 * @property float $quantity
 * @property Carbon|null $production_date
 * @property Carbon|null $expiration_date
 * @property Carbon|null $transferred_at
 * @property int|null $responsible_user_id
 */
#[Fillable([
    'branch_id',
    'product_id',
    'batch_id',
    'batch_number',
    'quantity',
    'production_date',
    'expiration_date',
    'transferred_at',
    'responsible_user_id',
])]
final class BranchProductBatch extends Model
{
    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'production_date' => 'date',
            'expiration_date' => 'date',
            'transferred_at' => 'datetime',
        ];
    }
}
