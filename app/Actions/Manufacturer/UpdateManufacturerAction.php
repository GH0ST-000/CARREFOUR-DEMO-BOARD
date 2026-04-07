<?php

declare(strict_types=1);

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;
use App\Services\ActionLogService;

final readonly class UpdateManufacturerAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array{full_name?: string, short_name?: string|null, legal_form?: string, identification_number?: string, legal_address?: string, phone?: string, email?: string, country?: string, region?: string, city?: string|null, is_active?: bool}  $data
     */
    public function execute(Manufacturer $manufacturer, array $data): Manufacturer
    {
        $manufacturer->update($data);

        $this->actionLogService->logModelUpdated($manufacturer, $manufacturer->getChanges());

        return $manufacturer->fresh() ?? $manufacturer;
    }
}
