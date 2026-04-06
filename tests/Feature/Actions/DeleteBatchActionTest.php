<?php

use App\Actions\Batch\DeleteBatchAction;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes batch successfully', function (): void {
    $batch = Batch::factory()->create();

    $action = new DeleteBatchAction;
    $result = $action->execute($batch);

    expect($result)->toBeTrue();
});

test('removes batch from database', function (): void {
    $batch = Batch::factory()->create();
    $batchId = $batch->id;

    $action = new DeleteBatchAction;
    $action->execute($batch);

    $this->assertDatabaseMissing('batches', [
        'id' => $batchId,
    ]);
});
