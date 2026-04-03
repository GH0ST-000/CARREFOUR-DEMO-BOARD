<?php

use App\Actions\Manufacturer\DeleteManufacturerAction;
use App\Models\Manufacturer;

describe('DeleteManufacturerAction', function (): void {
    beforeEach(function (): void {
        $this->action = new DeleteManufacturerAction;
    });

    test('deletes manufacturer', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        $result = $this->action->execute($manufacturer);

        expect($result)->toBeTrue();
        expect(Manufacturer::find($manufacturer->id))->toBeNull();
    });

    test('returns true on successful deletion', function (): void {
        $manufacturer = Manufacturer::factory()->create();

        $result = $this->action->execute($manufacturer);

        expect($result)->toBeTrue();
    });
});
