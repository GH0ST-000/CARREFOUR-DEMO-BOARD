<?php

use App\Actions\Receiving\UpdateReceivingAction;
use App\Models\Receiving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('updates receiving status', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'status' => 'accepted',
    ]);

    expect($updated->status)->toBe('accepted');
});

test('updates receiving with all fields', function (): void {
    $receiving = Receiving::factory()->pending()->create();
    $verifiedBy = User::factory()->create();

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'status' => 'accepted',
        'verified_by_user_id' => $verifiedBy->id,
        'recorded_temperature' => 3.0,
        'temperature_compliant' => true,
        'temperature_notes' => 'Temperature verified',
        'packaging_condition' => 'excellent',
        'quality_notes' => 'Quality approved',
        'documents_verified' => true,
        'notes' => 'All checks passed',
    ]);

    expect($updated->status)->toBe('accepted')
        ->and($updated->verified_by_user_id)->toBe($verifiedBy->id)
        ->and($updated->recorded_temperature)->toBe(3.0)
        ->and($updated->temperature_compliant)->toBeTrue()
        ->and($updated->packaging_condition)->toBe('excellent')
        ->and($updated->documents_verified)->toBeTrue();
});

test('updates receiving to rejected with reason', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'status' => 'rejected',
        'rejection_reason' => 'Damaged packaging',
        'packaging_condition' => 'damaged',
    ]);

    expect($updated->status)->toBe('rejected')
        ->and($updated->rejection_reason)->toBe('Damaged packaging')
        ->and($updated->packaging_condition)->toBe('damaged');
});

test('updates receiving to quarantined', function (): void {
    $receiving = Receiving::factory()->pending()->create();

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'status' => 'quarantined',
        'notes' => 'Placed in quarantine for further inspection',
    ]);

    expect($updated->status)->toBe('quarantined')
        ->and($updated->notes)->toBe('Placed in quarantine for further inspection');
});

test('adds photos to receiving', function (): void {
    $receiving = Receiving::factory()->create(['photos' => null]);

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'photos' => ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'],
    ]);

    expect($updated->photos)->toBeArray()->toHaveCount(3);
});

test('updates temperature compliance', function (): void {
    $receiving = Receiving::factory()->create([
        'temperature_compliant' => true,
        'recorded_temperature' => 2.0,
    ]);

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'temperature_compliant' => false,
        'recorded_temperature' => 15.0,
        'temperature_notes' => 'Temperature exceeded acceptable range',
    ]);

    expect($updated->temperature_compliant)->toBeFalse()
        ->and($updated->recorded_temperature)->toBe(15.0)
        ->and($updated->temperature_notes)->toBe('Temperature exceeded acceptable range');
});

test('updates document verification status', function (): void {
    $receiving = Receiving::factory()->create([
        'documents_verified' => false,
    ]);

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'documents_verified' => true,
        'missing_documents' => null,
    ]);

    expect($updated->documents_verified)->toBeTrue()
        ->and($updated->missing_documents)->toBeNull();
});

test('updates with missing documents list', function (): void {
    $receiving = Receiving::factory()->create();

    $action = new UpdateReceivingAction;
    $updated = $action->execute($receiving, [
        'documents_verified' => false,
        'missing_documents' => ['certificate_of_origin', 'quality_certificate'],
    ]);

    expect($updated->documents_verified)->toBeFalse()
        ->and($updated->missing_documents)->toBeArray()->toHaveCount(2);
});
