<?php

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;

class UpdateWarehouseLocationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(WarehouseLocation $location, array $data): WarehouseLocation
    {
        $location->update($data);

        return $location->fresh();
    }
}
