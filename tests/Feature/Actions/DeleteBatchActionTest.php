<?php

declare(strict_types=1);

use App\Actions\Batch\DeleteBatchAction;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes batch successfully', function (): void {
    $batch = Batch::factory()->create();

    $action = app(DeleteBatchAction::class);
    $result = $action->execute($batch);

    expect($result)->toBeTrue();
});

test('removes batch from database', function (): void {
    $batch = Batch::factory()->create();
    $batchId = $batch->id;

    $action = app(DeleteBatchAction::class);
    $action->execute($batch);

    $this->assertDatabaseMissing('batches', [
        'id' => $batchId,
    ]);
});
