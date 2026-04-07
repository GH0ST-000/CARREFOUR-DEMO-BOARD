<?php

declare(strict_types=1);

namespace App\Actions\Recall;

use App\Models\Recall;
use App\Models\User;

final class ApproveRecallAction
{
    public function execute(Recall $recall, User $approver, string $status): void
    {
        $recall->update([
            'status' => $status,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}
