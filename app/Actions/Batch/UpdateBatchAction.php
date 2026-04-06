<?php

namespace App\Actions\Batch;

use App\Models\Batch;

class UpdateBatchAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Batch $batch, array $data): Batch
    {
        $batch->update($data);

        return $batch->fresh();
    }
}
