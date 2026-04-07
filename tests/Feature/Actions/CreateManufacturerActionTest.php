<?php

declare(strict_types=1);

use App\Actions\Manufacturer\CreateManufacturerAction;
use App\Models\Manufacturer;

describe('CreateManufacturerAction', function (): void {
    beforeEach(function (): void {
        $this->action = app(CreateManufacturerAction::class);
    });

    test('creates manufacturer with all fields', function (): void {
        $data = [
            'full_name' => 'Test Manufacturer LLC',
            'short_name' => 'TestMfg',
            'legal_form' => 'Limited Liability Company',
            'identification_number' => '123456789',
            'legal_address' => '123 Test Street',
            'phone' => '+995-555-123456',
            'email' => 'contact@test.ge',
            'country' => 'Georgia',
            'region' => 'Tbilisi',
            'city' => 'Tbilisi',
            'is_active' => true,
        ];

        $manufacturer = $this->action->execute($data);

        expect($manufacturer)->toBeInstanceOf(Manufacturer::class)
            ->and($manufacturer->full_name)->toBe('Test Manufacturer LLC')
            ->and($manufacturer->identification_number)->toBe('123456789')
            ->and($manufacturer->is_active)->toBeTrue();
    });

    test('creates manufacturer with nullable fields', function (): void {
        $data = [
            'full_name' => 'Test Manufacturer',
            'short_name' => null,
            'legal_form' => 'LLC',
            'identification_number' => '987654321',
            'legal_address' => 'Address',
            'phone' => '+995-555-000000',
            'email' => 'test@test.ge',
            'country' => 'Georgia',
            'region' => 'Tbilisi',
            'city' => null,
            'is_active' => true,
        ];

        $manufacturer = $this->action->execute($data);

        expect($manufacturer->short_name)->toBeNull()
            ->and($manufacturer->city)->toBeNull();
    });

    test('creates manufacturer with default is_active', function (): void {
        $data = [
            'full_name' => 'Test',
            'short_name' => null,
            'legal_form' => 'LLC',
            'identification_number' => '111111',
            'legal_address' => 'Address',
            'phone' => '123',
            'email' => 'test@test.ge',
            'country' => 'Georgia',
            'region' => 'Tbilisi',
            'city' => null,
            'is_active' => true,
        ];

        $manufacturer = $this->action->execute($data);

        expect($manufacturer->is_active)->toBeTrue();
    });
});
