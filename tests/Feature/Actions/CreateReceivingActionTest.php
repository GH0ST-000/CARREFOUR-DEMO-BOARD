<?php

declare(strict_types=1);

use App\Actions\Receiving\CreateReceivingAction;
use App\Models\Batch;
use App\Models\Receiving;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates receiving with valid data', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $data = [
        'batch_id' => $batch->id,
        'warehouse_location_id' => $location->id,
        'received_by_user_id' => $receivedBy->id,
        'receipt_datetime' => now()->toDateTimeString(),
        'received_quantity' => 100.50,
        'unit' => 'kg',
        'receipt_number' => 'RCP-001',
        'status' => 'pending',
    ];

    $action = app(CreateReceivingAction::class);
    $receiving = $action->execute($data);

    expect($receiving)->toBeInstanceOf(Receiving::class)
        ->and($receiving->receipt_number)->toBe('RCP-001')
        ->and($receiving->received_quantity)->toBe(100.50)
        ->and($receiving->batch_id)->toBe($batch->id);
});

test('creates receiving with all optional fields', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();
    $verifiedBy = User::factory()->create();

    $data = [
        'batch_id' => $batch->id,
        'warehouse_location_id' => $location->id,
        'received_by_user_id' => $receivedBy->id,
        'verified_by_user_id' => $verifiedBy->id,
        'receipt_datetime' => now()->toDateTimeString(),
        'received_quantity' => 100.50,
        'unit' => 'kg',
        'receipt_number' => 'RCP-002',
        'supplier_invoice_number' => 'INV-12345',
        'status' => 'accepted',
        'recorded_temperature' => 2.5,
        'temperature_compliant' => true,
        'temperature_notes' => 'Temperature within range',
        'packaging_condition' => 'excellent',
        'quality_notes' => 'All quality checks passed',
        'documents_verified' => true,
        'missing_documents' => ['certificate_of_origin'],
        'photos' => ['photo1.jpg', 'photo2.jpg'],
        'notes' => 'Received in good condition',
    ];

    $action = app(CreateReceivingAction::class);
    $receiving = $action->execute($data);

    expect($receiving)->toBeInstanceOf(Receiving::class)
        ->and($receiving->receipt_number)->toBe('RCP-002')
        ->and($receiving->supplier_invoice_number)->toBe('INV-12345')
        ->and($receiving->status)->toBe('accepted')
        ->and($receiving->recorded_temperature)->toBe(2.5)
        ->and($receiving->temperature_compliant)->toBeTrue()
        ->and($receiving->packaging_condition)->toBe('excellent')
        ->and($receiving->documents_verified)->toBeTrue()
        ->and($receiving->missing_documents)->toBeArray()->toHaveCount(1)
        ->and($receiving->photos)->toBeArray()->toHaveCount(2)
        ->and($receiving->verified_by_user_id)->toBe($verifiedBy->id);
});

test('creates receiving with explicit status', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $data = [
        'batch_id' => $batch->id,
        'warehouse_location_id' => $location->id,
        'received_by_user_id' => $receivedBy->id,
        'receipt_datetime' => now()->toDateTimeString(),
        'received_quantity' => 100,
        'unit' => 'kg',
        'status' => 'pending',
    ];

    $action = app(CreateReceivingAction::class);
    $receiving = $action->execute($data);

    expect($receiving->status)->toBe('pending');
});

test('creates rejected receiving with reason', function (): void {
    $batch = Batch::factory()->create();
    $location = WarehouseLocation::factory()->create();
    $receivedBy = User::factory()->create();

    $data = [
        'batch_id' => $batch->id,
        'warehouse_location_id' => $location->id,
        'received_by_user_id' => $receivedBy->id,
        'receipt_datetime' => now()->toDateTimeString(),
        'received_quantity' => 100,
        'unit' => 'kg',
        'status' => 'rejected',
        'rejection_reason' => 'Temperature non-compliant',
        'recorded_temperature' => 15.0,
        'temperature_compliant' => false,
    ];

    $action = app(CreateReceivingAction::class);
    $receiving = $action->execute($data);

    expect($receiving->status)->toBe('rejected')
        ->and($receiving->rejection_reason)->toBe('Temperature non-compliant')
        ->and($receiving->temperature_compliant)->toBeFalse();
});
