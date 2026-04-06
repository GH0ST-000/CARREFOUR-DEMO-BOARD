<?php

namespace App\Actions\Receiving;

use App\Models\Receiving;

class DeleteReceivingAction
{
    public function execute(Receiving $receiving): bool
    {
        return (bool) $receiving->delete();
    }
}
