<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ManufacturerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $full_name
 * @property string|null $short_name
 * @property string $legal_form
 * @property string $identification_number
 * @property string $legal_address
 * @property string $phone
 * @property string $email
 * @property string $country
 * @property string $region
 * @property string|null $city
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'full_name',
    'short_name',
    'legal_form',
    'identification_number',
    'legal_address',
    'phone',
    'email',
    'country',
    'region',
    'city',
    'is_active',
])]
final class Manufacturer extends Model
{
    /** @use HasFactory<ManufacturerFactory> */
    use HasFactory;

    /**
     * @param  Builder<Manufacturer>  $query
     * @return Builder<Manufacturer>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('full_name')
            ->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
