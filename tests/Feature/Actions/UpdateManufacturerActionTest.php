<?php

use App\Actions\Manufacturer\UpdateManufacturerAction;
use App\Models\Manufacturer;

describe('UpdateManufacturerAction', function (): void {
    beforeEach(function (): void {
        $this->action = new UpdateManufacturerAction;
    });

    test('updates manufacturer fields', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'full_name' => 'Original Name',
            'email' => 'original@test.ge',
        ]);

        $data = [
            'full_name' => 'Updated Name',
            'email' => 'updated@test.ge',
        ];

        $updated = $this->action->execute($manufacturer, $data);

        expect($updated->full_name)->toBe('Updated Name')
            ->and($updated->email)->toBe('updated@test.ge');
    });

    test('updates only specified fields', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'full_name' => 'Original Name',
            'phone' => 'Original Phone',
        ]);

        $data = [
            'phone' => 'Updated Phone',
        ];

        $updated = $this->action->execute($manufacturer, $data);

        expect($updated->phone)->toBe('Updated Phone')
            ->and($updated->full_name)->toBe('Original Name');
    });

    test('can update is_active status', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'is_active' => true,
        ]);

        $updated = $this->action->execute($manufacturer, ['is_active' => false]);

        expect($updated->is_active)->toBeFalse();
    });

    test('returns fresh instance after update', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        $updated = $this->action->execute($manufacturer, ['full_name' => 'New Name']);

        expect($updated->id)->toBe($manufacturer->id)
            ->and($updated->full_name)->toBe('New Name')
            ->and($updated->wasRecentlyCreated)->toBeFalse();
    });
});
