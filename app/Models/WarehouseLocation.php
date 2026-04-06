<?php

namespace App\Models;

use Database\Factories\WarehouseLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property int|null $parent_id
 * @property float|null $temp_min
 * @property float|null $temp_max
 * @property int|null $responsible_user_id
 * @property int|null $inspection_frequency_hours
 * @property string|null $description
 * @property string|null $address
 * @property bool $has_sensor
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read WarehouseLocation|null $parent
 * @property-read WarehouseLocation[] $children
 * @property-read User|null $responsibleUser
 */
#[Fillable([
    'name',
    'code',
    'type',
    'parent_id',
    'temp_min',
    'temp_max',
    'responsible_user_id',
    'inspection_frequency_hours',
    'description',
    'address',
    'has_sensor',
    'is_active',
])]
class WarehouseLocation extends Model
{
    /** @use HasFactory<WarehouseLocationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<WarehouseLocation, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'parent_id');
    }

    /**
     * @return HasMany<WarehouseLocation, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'parent_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
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
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function hasTemperatureControl(): bool
    {
        return $this->temp_min !== null || $this->temp_max !== null;
    }

    public function isTemperatureInRange(float $temperature): bool
    {
        if (! $this->hasTemperatureControl()) {
            return true;
        }

        if ($this->temp_min !== null && $temperature < $this->temp_min) {
            return false;
        }

        if ($this->temp_max !== null && $temperature > $this->temp_max) {
            return false;
        }

        return true;
    }

    protected function casts(): array
    {
        return [
            'temp_min' => 'float',
            'temp_max' => 'float',
            'inspection_frequency_hours' => 'integer',
            'has_sensor' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
