<?php

namespace App\Actions\Receiving;

use App\Models\Receiving;

class CreateReceivingAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Receiving
    {
        return Receiving::create($data);
    }
}
