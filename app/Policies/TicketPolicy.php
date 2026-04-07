<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

final class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket') || $user->can('create_ticket');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($ticket->isOwnedBy($user)) {
            return true;
        }

        return $user->can('view_ticket');
    }

    public function create(User $user): bool
    {
        return $user->can('create_ticket');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->isOwnedBy($user)) {
            return true;
        }

        return $user->can('update_ticket');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete_ticket');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        if ($ticket->isOwnedBy($user)) {
            return true;
        }

        return $user->can('update_ticket');
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        if ($ticket->isOwnedBy($user)) {
            return true;
        }

        return $user->can('update_ticket');
    }

    public function addMessage(User $user, Ticket $ticket): bool
    {
        if ($ticket->isOwnedBy($user)) {
            return true;
        }

        return $user->can('update_ticket');
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->can('restore_ticket');
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->can('force_delete_ticket');
    }
}
