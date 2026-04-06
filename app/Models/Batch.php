<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $batch_number
 * @property string|null $import_declaration_number
 * @property string|null $local_production_number
 * @property Carbon $production_date
 * @property Carbon $expiry_date
 * @property Carbon|null $receiving_datetime
 * @property float $quantity
 * @property float $remaining_quantity
 * @property string $unit
 * @property string $status
 * @property int|null $warehouse_location_id
 * @property float|null $receiving_temperature
 * @property string|null $packaging_condition
 * @property int $product_id
 * @property int|null $received_by_user_id
 * @property array<int, string>|null $linked_documents
 * @property array<int, array<string, mixed>>|null $temperature_history
 * @property array<int, array<string, mixed>>|null $movement_history
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Product $product
 * @property-read WarehouseLocation|null $warehouseLocation
 * @property-read User|null $receivedBy
 */
#[Fillable([
    'batch_number',
    'import_declaration_number',
    'local_production_number',
    'production_date',
    'expiry_date',
    'receiving_datetime',
    'quantity',
    'remaining_quantity',
    'unit',
    'status',
    'warehouse_location_id',
    'receiving_temperature',
    'packaging_condition',
    'product_id',
    'received_by_user_id',
    'linked_documents',
    'temperature_history',
    'movement_history',
    'notes',
])]
class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<WarehouseLocation, self>
     */
    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['received', 'in_storage']);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeExpiringWithinDays(Builder $query, int $days): Builder
    {
        return $query->whereBetween('expiry_date', [
            now(),
            now()->addDays($days),
        ]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', 'blocked');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeRecalled(Builder $query): Builder
    {
        return $query->where('status', 'recalled');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function daysUntilExpiry(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false));
    }

    public function isFullyConsumed(): bool
    {
        return $this->remaining_quantity <= 0;
    }

    public function hasQuantityAvailable(): bool
    {
        return $this->remaining_quantity > 0;
    }

    public function isLocal(): bool
    {
        return $this->local_production_number !== null;
    }

    public function isImported(): bool
    {
        return $this->import_declaration_number !== null;
    }

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expiry_date' => 'date',
            'receiving_datetime' => 'datetime',
            'quantity' => 'float',
            'remaining_quantity' => 'float',
            'receiving_temperature' => 'float',
            'linked_documents' => 'array',
            'temperature_history' => 'array',
            'movement_history' => 'array',
        ];
    }
}
