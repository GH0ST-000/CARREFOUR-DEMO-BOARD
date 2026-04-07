<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class ActionLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_action_log');
    }

    public function view(User $user): bool
    {
        return $user->can('view_action_log');
    }
}
