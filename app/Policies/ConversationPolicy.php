<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

final class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user->id);
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user->id);
    }

    public function markAsRead(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user->id);
    }
}
