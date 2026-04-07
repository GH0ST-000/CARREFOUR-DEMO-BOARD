<?php

declare(strict_types=1);

use App\Actions\Batch\UpdateBatchAction;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('updates batch successfully', function (): void {
    $batch = Batch::factory()->create([
        'status' => 'pending',
        'notes' => null,
    ]);

    $data = [
        'status' => 'received',
        'notes' => 'Received in good condition',
    ];

    $action = app(UpdateBatchAction::class);
    $updated = $action->execute($batch, $data);

    expect($updated->status)->toBe('received');
    expect($updated->notes)->toBe('Received in good condition');
});

test('persists changes to database', function (): void {
    $batch = Batch::factory()->create([
        'status' => 'pending',
    ]);

    $data = [
        'status' => 'blocked',
    ];

    $action = app(UpdateBatchAction::class);
    $action->execute($batch, $data);

    $this->assertDatabaseHas('batches', [
        'id' => $batch->id,
        'status' => 'blocked',
    ]);
});

test('returns fresh instance', function (): void {
    $batch = Batch::factory()->create([
        'remaining_quantity' => 100,
    ]);

    $data = [
        'remaining_quantity' => 75,
    ];

    $action = app(UpdateBatchAction::class);
    $updated = $action->execute($batch, $data);

    expect($updated->remaining_quantity)->toBe(75.0);
    expect($updated->wasRecentlyCreated)->toBeFalse();
});
