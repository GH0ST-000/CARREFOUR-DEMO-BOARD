<?php

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;

class DeleteManufacturerAction
{
    public function execute(Manufacturer $manufacturer): bool
    {
        return (bool) $manufacturer->delete();
    }
}
