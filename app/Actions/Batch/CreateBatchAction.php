<?php

namespace App\Actions\Batch;

use App\Models\Batch;

class CreateBatchAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Batch
    {
        $data['remaining_quantity'] = $data['quantity'];

        return Batch::create($data);
    }
}
