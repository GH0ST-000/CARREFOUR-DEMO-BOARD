<?php

use App\Actions\Receiving\DeleteReceivingAction;
use App\Models\Receiving;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes receiving', function (): void {
    $receiving = Receiving::factory()->create();

    $action = new DeleteReceivingAction;
    $action->execute($receiving);

    expect(Receiving::find($receiving->id))->toBeNull();
});

test('deletes pending receiving', function (): void {
    $receiving = Receiving::factory()->pending()->create();
    $id = $receiving->id;

    $action = new DeleteReceivingAction;
    $action->execute($receiving);

    $this->assertDatabaseMissing('receivings', ['id' => $id]);
});

test('deletes accepted receiving', function (): void {
    $receiving = Receiving::factory()->accepted()->create();
    $id = $receiving->id;

    $action = new DeleteReceivingAction;
    $action->execute($receiving);

    $this->assertDatabaseMissing('receivings', ['id' => $id]);
});

test('deletes rejected receiving', function (): void {
    $receiving = Receiving::factory()->rejected()->create();
    $id = $receiving->id;

    $action = new DeleteReceivingAction;
    $action->execute($receiving);

    $this->assertDatabaseMissing('receivings', ['id' => $id]);
});
