<?php

use App\Models\Batch;
use App\Models\Receiving;
use App\Models\User;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('has correct fillable attributes', function (): void {
    $fillable = (new Receiving)->getFillable();

    expect($fillable)->toContain(
        'receipt_number',
        'receipt_datetime',
        'supplier_invoice_number',
        'batch_id',
        'warehouse_location_id',
        'received_quantity',
        'unit',
        'recorded_temperature',
        'temperature_compliant',
        'temperature_notes',
        'packaging_condition',
        'quality_notes',
        'documents_verified',
        'missing_documents',
        'status',
        'rejection_reason',
        'photos',
        'received_by_user_id',
        'verified_by_user_id',
        'notes'
    );
});

test('belongs to batch', function (): void {
    $batch = Batch::factory()->create();
    $receiving = Receiving::factory()->create(['batch_id' => $batch->id]);

    expect($receiving->batch)->toBeInstanceOf(Batch::class)
        ->and($receiving->batch->id)->toBe($batch->id);
});

test('belongs to warehouse location', function (): void {
    $location = WarehouseLocation::factory()->create();
    $receiving = Receiving::factory()->create(['warehouse_location_id' => $location->id]);

    expect($receiving->warehouseLocation)->toBeInstanceOf(WarehouseLocation::class)
        ->and($receiving->warehouseLocation->id)->toBe($location->id);
});

test('belongs to received by user', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create(['received_by_user_id' => $user->id]);

    expect($receiving->receivedBy)->toBeInstanceOf(User::class)
        ->and($receiving->receivedBy->id)->toBe($user->id);
});

test('belongs to verified by user optionally', function (): void {
    $user = User::factory()->create();
    $receiving = Receiving::factory()->create(['verified_by_user_id' => $user->id]);

    expect($receiving->verifiedBy)->toBeInstanceOf(User::class)
        ->and($receiving->verifiedBy->id)->toBe($user->id);
});

test('verified by is null when not set', function (): void {
    $receiving = Receiving::factory()->create(['verified_by_user_id' => null]);

    expect($receiving->verifiedBy)->toBeNull();
});

test('scope ordered returns receivings ordered by receipt datetime desc', function (): void {
    $first = Receiving::factory()->create(['receipt_datetime' => now()->subDays(3)]);
    $second = Receiving::factory()->create(['receipt_datetime' => now()->subDays(2)]);
    $third = Receiving::factory()->create(['receipt_datetime' => now()->subDays(1)]);

    $ordered = Receiving::ordered()->get();

    expect($ordered->first()->id)->toBe($third->id)
        ->and($ordered->last()->id)->toBe($first->id);
});

test('scope pending returns only pending receivings', function (): void {
    Receiving::factory()->pending()->create();
    Receiving::factory()->accepted()->create();
    Receiving::factory()->rejected()->create();

    $pending = Receiving::pending()->get();

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->status)->toBe('pending');
});

test('scope accepted returns only accepted receivings', function (): void {
    Receiving::factory()->pending()->create();
    Receiving::factory()->accepted()->create();
    Receiving::factory()->accepted()->create();

    $accepted = Receiving::accepted()->get();

    expect($accepted)->toHaveCount(2)
        ->each(fn ($item) => $item->status->toBe('accepted'));
});

test('scope rejected returns only rejected receivings', function (): void {
    Receiving::factory()->pending()->create();
    Receiving::factory()->rejected()->create();
    Receiving::factory()->rejected()->create();

    $rejected = Receiving::rejected()->get();

    expect($rejected)->toHaveCount(2)
        ->each(fn ($item) => $item->status->toBe('rejected'));
});

test('scope quarantined returns only quarantined receivings', function (): void {
    Receiving::factory()->pending()->create();
    Receiving::factory()->quarantined()->create();

    $quarantined = Receiving::quarantined()->get();

    expect($quarantined)->toHaveCount(1)
        ->and($quarantined->first()->status)->toBe('quarantined');
});

test('scope temperature non compliant returns receivings with temperature issues', function (): void {
    Receiving::factory()->create(['temperature_compliant' => true]);
    Receiving::factory()->temperatureNonCompliant()->create();
    Receiving::factory()->temperatureNonCompliant()->create();

    $nonCompliant = Receiving::temperatureNonCompliant()->get();

    expect($nonCompliant)->toHaveCount(2)
        ->each(fn ($item) => $item->temperature_compliant->toBeFalse());
});

test('is accepted returns true for accepted status', function (): void {
    $receiving = Receiving::factory()->accepted()->create();

    expect($receiving->isAccepted())->toBeTrue();
});

test('is accepted returns false for non-accepted status', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    expect($receiving->isAccepted())->toBeFalse();
});

test('is rejected returns true for rejected status', function (): void {
    $receiving = Receiving::factory()->rejected()->create();

    expect($receiving->isRejected())->toBeTrue();
});

test('is rejected returns false for non-rejected status', function (): void {
    $receiving = Receiving::factory()->accepted()->create();

    expect($receiving->isRejected())->toBeFalse();
});

test('is quarantined returns true for quarantined status', function (): void {
    $receiving = Receiving::factory()->quarantined()->create();

    expect($receiving->isQuarantined())->toBeTrue();
});

test('is quarantined returns false for non-quarantined status', function (): void {
    $receiving = Receiving::factory()->accepted()->create();

    expect($receiving->isQuarantined())->toBeFalse();
});

test('is pending returns true for pending status', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    expect($receiving->isPending())->toBeTrue();
});

test('is pending returns false for non-pending status', function (): void {
    $receiving = Receiving::factory()->accepted()->create();

    expect($receiving->isPending())->toBeFalse();
});

test('is documents verified returns true when verified', function (): void {
    $receiving = Receiving::factory()->create(['documents_verified' => true]);

    expect($receiving->isDocumentsVerified())->toBeTrue();
});

test('is documents verified returns false when not verified', function (): void {
    $receiving = Receiving::factory()->create(['documents_verified' => false]);

    expect($receiving->isDocumentsVerified())->toBeFalse();
});

test('has photos returns true when photos array exists', function (): void {
    $receiving = Receiving::factory()->withPhotos()->create();

    expect($receiving->hasPhotos())->toBeTrue();
});

test('has photos returns false when photos is null', function (): void {
    $receiving = Receiving::factory()->create(['photos' => null]);

    expect($receiving->hasPhotos())->toBeFalse();
});

test('is temperature compliant returns correct value', function (): void {
    $compliant = Receiving::factory()->create(['temperature_compliant' => true]);
    $nonCompliant = Receiving::factory()->temperatureNonCompliant()->create();

    expect($compliant->isTemperatureCompliant())->toBeTrue()
        ->and($nonCompliant->isTemperatureCompliant())->toBeFalse();
});

test('is packaging acceptable checks packaging condition', function (): void {
    $excellent = Receiving::factory()->create(['packaging_condition' => 'excellent']);
    $good = Receiving::factory()->create(['packaging_condition' => 'good']);
    $acceptable = Receiving::factory()->create(['packaging_condition' => 'acceptable']);
    $damaged = Receiving::factory()->create(['packaging_condition' => 'damaged']);
    $rejected = Receiving::factory()->create(['packaging_condition' => 'rejected']);

    expect($excellent->isPackagingAcceptable())->toBeTrue()
        ->and($good->isPackagingAcceptable())->toBeTrue()
        ->and($acceptable->isPackagingAcceptable())->toBeTrue()
        ->and($damaged->isPackagingAcceptable())->toBeFalse()
        ->and($rejected->isPackagingAcceptable())->toBeFalse();
});

test('are documents complete checks verification status', function (): void {
    $complete = Receiving::factory()->create(['documents_verified' => true, 'missing_documents' => null]);
    $incomplete = Receiving::factory()->withMissingDocuments()->create();

    expect($complete->areDocumentsComplete())->toBeTrue()
        ->and($incomplete->areDocumentsComplete())->toBeFalse();
});

test('casts receipt datetime to carbon', function (): void {
    $receiving = Receiving::factory()->create();

    expect($receiving->receipt_datetime)->toBeInstanceOf(Carbon::class);
});

test('casts json fields correctly', function (): void {
    $receiving = Receiving::factory()
        ->withPhotos()
        ->withMissingDocuments()
        ->create();

    expect($receiving->photos)->toBeArray()
        ->and($receiving->missing_documents)->toBeArray();
});

test('casts boolean fields correctly', function (): void {
    $receiving = Receiving::factory()->create([
        'temperature_compliant' => true,
        'documents_verified' => false,
    ]);

    expect($receiving->temperature_compliant)->toBeBool()->toBeTrue()
        ->and($receiving->documents_verified)->toBeBool()->toBeFalse();
});
