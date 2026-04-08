<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property Carbon|null $last_message_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, ConversationParticipant> $participants
 * @property-read Collection<int, ChatMessage> $messages
 * @property-read ChatMessage|null $latestMessage
 */
#[Fillable(['type', 'last_message_at'])]
final class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @return HasMany<ConversationParticipant, self>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * @return HasMany<ChatMessage, self>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * @return HasOne<ChatMessage, self>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('participants', fn (Builder $q): Builder => $q->where('user_id', $userId));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('type', 'direct');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrderedByLatestMessage(Builder $query): Builder
    {
        return $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function hasParticipant(int $userId): bool
    {
        if ($this->relationLoaded('participants')) {
            return $this->participants->contains('user_id', $userId);
        }

        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }
}
