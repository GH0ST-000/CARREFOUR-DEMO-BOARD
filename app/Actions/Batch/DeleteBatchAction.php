<?php

namespace App\Actions\Batch;

use App\Models\Batch;

class DeleteBatchAction
{
    public function execute(Batch $batch): bool
    {
        return (bool) $batch->delete();
    }
}
