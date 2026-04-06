<?php

namespace App\Actions\Receiving;

use App\Models\Receiving;

class UpdateReceivingAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Receiving $receiving, array $data): Receiving
    {
        $receiving->update($data);

        $result = $receiving->fresh();
        assert($result instanceof Receiving);

        return $result;
    }
}
