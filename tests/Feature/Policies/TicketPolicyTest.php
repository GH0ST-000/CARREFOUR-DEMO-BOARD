<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->policy = new TicketPolicy;
    $this->user = User::factory()->create();
    $this->ticket = Ticket::factory()->create();
});

test('viewAny returns true when user has view_any_ticket permission', function (): void {
    $this->user->givePermissionTo('view_any_ticket');

    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('viewAny returns true when user has create_ticket permission', function (): void {
    $this->user->givePermissionTo('create_ticket');

    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('viewAny returns false when user lacks permission', function (): void {
    expect($this->policy->viewAny($this->user))->toBeFalse();
});

test('view returns true when user owns the ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->view($this->user, $ticket))->toBeTrue();
});

test('view returns true when user has view_ticket permission', function (): void {
    $this->user->givePermissionTo('view_ticket');

    expect($this->policy->view($this->user, $this->ticket))->toBeTrue();
});

test('view returns false when user lacks permission and does not own ticket', function (): void {
    expect($this->policy->view($this->user, $this->ticket))->toBeFalse();
});

test('create returns true when user has permission', function (): void {
    $this->user->givePermissionTo('create_ticket');

    expect($this->policy->create($this->user))->toBeTrue();
});

test('create returns false when user lacks permission', function (): void {
    expect($this->policy->create($this->user))->toBeFalse();
});

test('update returns true when user owns the ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->update($this->user, $ticket))->toBeTrue();
});

test('update returns true when user has update_ticket permission', function (): void {
    $this->user->givePermissionTo('update_ticket');

    expect($this->policy->update($this->user, $this->ticket))->toBeTrue();
});

test('update returns false when user lacks permission and does not own ticket', function (): void {
    expect($this->policy->update($this->user, $this->ticket))->toBeFalse();
});

test('delete returns true when user has permission', function (): void {
    $this->user->givePermissionTo('delete_ticket');

    expect($this->policy->delete($this->user, $this->ticket))->toBeTrue();
});

test('delete returns false when user lacks permission', function (): void {
    expect($this->policy->delete($this->user, $this->ticket))->toBeFalse();
});

test('close returns true when user owns the ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->close($this->user, $ticket))->toBeTrue();
});

test('close returns true when user has update_ticket permission', function (): void {
    $this->user->givePermissionTo('update_ticket');

    expect($this->policy->close($this->user, $this->ticket))->toBeTrue();
});

test('close returns false when user lacks permission and does not own ticket', function (): void {
    expect($this->policy->close($this->user, $this->ticket))->toBeFalse();
});

test('reopen returns true when user owns the ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->reopen($this->user, $ticket))->toBeTrue();
});

test('reopen returns true when user has update_ticket permission', function (): void {
    $this->user->givePermissionTo('update_ticket');

    expect($this->policy->reopen($this->user, $this->ticket))->toBeTrue();
});

test('addMessage returns true when user owns the ticket', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->addMessage($this->user, $ticket))->toBeTrue();
});

test('addMessage returns true when user has update_ticket permission', function (): void {
    $this->user->givePermissionTo('update_ticket');

    expect($this->policy->addMessage($this->user, $this->ticket))->toBeTrue();
});

test('addMessage returns false when user lacks permission and does not own ticket', function (): void {
    expect($this->policy->addMessage($this->user, $this->ticket))->toBeFalse();
});

test('restore returns true when user has permission', function (): void {
    $this->user->givePermissionTo('restore_ticket');

    expect($this->policy->restore($this->user, $this->ticket))->toBeTrue();
});

test('restore returns false when user lacks permission', function (): void {
    expect($this->policy->restore($this->user, $this->ticket))->toBeFalse();
});

test('forceDelete returns true when user has permission', function (): void {
    $this->user->givePermissionTo('force_delete_ticket');

    expect($this->policy->forceDelete($this->user, $this->ticket))->toBeTrue();
});

test('forceDelete returns false when user lacks permission', function (): void {
    expect($this->policy->forceDelete($this->user, $this->ticket))->toBeFalse();
});
