<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;
}
