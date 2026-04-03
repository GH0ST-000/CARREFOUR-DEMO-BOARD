<?php

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;

class UpdateManufacturerAction
{
    /**
     * @param  array{full_name?: string, short_name?: string|null, legal_form?: string, identification_number?: string, legal_address?: string, phone?: string, email?: string, country?: string, region?: string, city?: string|null, is_active?: bool}  $data
     */
    public function execute(Manufacturer $manufacturer, array $data): Manufacturer
    {
        $manufacturer->update($data);

        return $manufacturer->fresh() ?? $manufacturer;
    }
}
