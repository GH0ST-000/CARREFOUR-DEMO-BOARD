<?php

declare(strict_types=1);

use App\Models\Manufacturer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

describe('Manufacturer Model', function (): void {
    test('can create manufacturer', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'full_name' => 'Test Manufacturer',
        ]);

        expect($manufacturer)->toBeInstanceOf(Manufacturer::class)
            ->and($manufacturer->full_name)->toBe('Test Manufacturer')
            ->and($manufacturer->exists)->toBeTrue();
    });

    test('casts is_active to boolean', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'is_active' => true,
        ]);

        expect($manufacturer->is_active)->toBeTrue()
            ->and($manufacturer->is_active)->toBeBool();
    });

    test('has timestamps', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        expect($manufacturer->created_at)->not->toBeNull()
            ->and($manufacturer->updated_at)->not->toBeNull()
            ->and($manufacturer->created_at)->toBeInstanceOf(Carbon::class);
    });

    test('allows nullable fields', function (): void {
        $manufacturer = Manufacturer::factory()->create([
            'short_name' => null,
            'city' => null,
        ]);

        expect($manufacturer->short_name)->toBeNull()
            ->and($manufacturer->city)->toBeNull();
    });

    test('identification number must be unique', function (): void {
        Manufacturer::factory()->create([
            'identification_number' => 'UNIQUE123',
        ]);

        expect(function (): void {
            Manufacturer::factory()->create([
                'identification_number' => 'UNIQUE123',
            ]);
        })->toThrow(UniqueConstraintViolationException::class);
    });
});
