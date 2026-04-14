<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property string|null $barcode
 * @property string|null $qr_code
 * @property string|null $brand
 * @property string $category
 * @property string $unit
 * @property string $origin_type
 * @property string $country_of_origin
 * @property float|null $storage_temp_min
 * @property float|null $storage_temp_max
 * @property Carbon|null $production_date
 * @property int $shelf_life_days
 * @property Carbon|null $expiration_date
 * @property string $inventory_policy
 * @property array<int, string>|null $allergens
 * @property array<int, string>|null $risk_indicators
 * @property array<int, string>|null $required_documents
 * @property int $manufacturer_id
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Manufacturer $manufacturer
 * @property-read Collection<int, Batch> $batches
 * @property-read Collection<int, Document> $documents
 */
#[Fillable([
    'name',
    'sku',
    'barcode',
    'qr_code',
    'brand',
    'category',
    'unit',
    'origin_type',
    'country_of_origin',
    'storage_temp_min',
    'storage_temp_max',
    'production_date',
    'shelf_life_days',
    'expiration_date',
    'inventory_policy',
    'allergens',
    'risk_indicators',
    'required_documents',
    'manufacturer_id',
    'is_active',
])]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Manufacturer, self>
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return HasMany<Batch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
            ->orderBy('name')
            ->orderBy('id');
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeLocal(Builder $query): Builder
    {
        return $query->where('origin_type', 'local');
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeImported(Builder $query): Builder
    {
        return $query->where('origin_type', 'imported');
    }

    public function isLocal(): bool
    {
        return $this->origin_type === 'local';
    }

    public function isImported(): bool
    {
        return $this->origin_type === 'imported';
    }

    public function hasTemperatureRequirement(): bool
    {
        return $this->storage_temp_min !== null || $this->storage_temp_max !== null;
    }

    public function isTemperatureInRange(float $temperature): bool
    {
        if (! $this->hasTemperatureRequirement()) {
            return true;
        }

        if ($this->storage_temp_min !== null && $temperature < $this->storage_temp_min) {
            return false;
        }

        if ($this->storage_temp_max !== null && $temperature > $this->storage_temp_max) {
            return false;
        }

        return true;
    }

    protected static function booted(): void
    {
        self::saving(function (self $product): void {
            if ($product->production_date === null || $product->shelf_life_days <= 0) {
                $product->expiration_date = null;

                return;
            }

            $product->expiration_date = $product->production_date->copy()->addDays($product->shelf_life_days);
        });
    }

    protected function casts(): array
    {
        return [
            'storage_temp_min' => 'float',
            'storage_temp_max' => 'float',
            'production_date' => 'date',
            'shelf_life_days' => 'integer',
            'expiration_date' => 'date',
            'allergens' => 'array',
            'risk_indicators' => 'array',
            'required_documents' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
