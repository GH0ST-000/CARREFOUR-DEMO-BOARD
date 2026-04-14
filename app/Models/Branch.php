<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $location
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, User> $responsibleUsers
 */
#[Fillable(['name', 'code', 'location', 'is_active'])]
final class Branch extends Model
{
    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function responsibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_responsible_users')
            ->withPivot(['assigned_at'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
