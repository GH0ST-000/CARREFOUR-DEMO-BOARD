<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $branch_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Branch|null $branch
 * @property-read Collection<int, Branch> $responsibleBranches
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable implements FilamentUser, JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsToMany<Branch, $this>
     */
    public function responsibleBranches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_responsible_users')
            ->withPivot(['assigned_at'])
            ->withTimestamps();
    }

    /**
     * In-app notifications (API `/notifications`, table `app_notifications`).
     *
     * @return HasMany<AppNotification, $this>
     */
    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    /**
     * Whether the user may receive non-chat notification modules (admin roles).
     */
    public function receivesBroadSystemNotifications(): bool
    {
        /** @var list<string> $roles */
        $roles = config('notifications.admin_roles', []);

        return $this->hasAnyRole($roles, 'web');
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }
}
