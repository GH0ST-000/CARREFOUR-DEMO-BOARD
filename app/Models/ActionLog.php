<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ActionLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action_type
 * @property string $entity_type
 * @property int|null $entity_id
 * @property string $module
 * @property string $description
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property-read User|null $user
 */
final class ActionLog extends Model
{
    /** @use HasFactory<ActionLogFactory> */
    use HasFactory, Searchable;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'module',
        'description',
        'metadata',
        'created_at',
    ];

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActionType(Builder $query, string $actionType): Builder
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeEntityType(Builder $query, string $entityType): Builder
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDateFrom(Builder $query, string $date): Builder
    {
        return $query->where('created_at', '>=', $date);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDateTo(Builder $query, string $date): Builder
    {
        return $query->where('created_at', '<=', $date);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'action_type' => $this->action_type,
            'entity_type' => $this->entity_type,
            'module' => $this->module,
        ];
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
