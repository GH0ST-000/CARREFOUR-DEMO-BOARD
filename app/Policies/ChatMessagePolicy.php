<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

final class ChatMessagePolicy
{
    public function delete(User $user, ChatMessage $message): bool
    {
        return $message->isSentBy($user->id);
    }
}
