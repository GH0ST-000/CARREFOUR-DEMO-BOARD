<?php

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;

class CreateManufacturerAction
{
    /**
     * @param  array{full_name: string, short_name: string|null, legal_form: string, identification_number: string, legal_address: string, phone: string, email: string, country: string, region: string, city: string|null, is_active: bool}  $data
     */
    public function execute(array $data): Manufacturer
    {
        return Manufacturer::create($data);
    }
}
