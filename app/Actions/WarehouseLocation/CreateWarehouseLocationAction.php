<?php

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;

class CreateWarehouseLocationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): WarehouseLocation
    {
        return WarehouseLocation::create($data);
    }
}
