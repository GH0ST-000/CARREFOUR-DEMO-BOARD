<?php

namespace App\Actions\WarehouseLocation;

use App\Models\WarehouseLocation;

class DeleteWarehouseLocationAction
{
    public function execute(WarehouseLocation $location): bool
    {
        return (bool) $location->delete();
    }
}
