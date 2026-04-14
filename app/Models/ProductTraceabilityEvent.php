<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $batch_id
 * @property int|null $from_branch_id
 * @property int $to_branch_id
 * @property float $quantity
 * @property Carbon $transferred_at
 * @property int|null $responsible_user_id
 * @property int|null $created_by_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Product $product
 * @property-read Batch|null $batch
 * @property-read Branch|null $fromBranch
 * @property-read Branch $toBranch
 * @property-read User|null $responsibleUser
 * @property-read User|null $createdBy
 */
#[Fillable([
    'product_id',
    'batch_id',
    'from_branch_id',
    'to_branch_id',
    'quantity',
    'transferred_at',
    'responsible_user_id',
    'created_by_user_id',
])]
final class ProductTraceabilityEvent extends Model
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
     * @return BelongsTo<Branch, $this>
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'transferred_at' => 'datetime',
        ];
    }
}
