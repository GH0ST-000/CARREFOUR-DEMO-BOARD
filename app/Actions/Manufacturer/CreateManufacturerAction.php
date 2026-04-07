<?php

declare(strict_types=1);

namespace App\Actions\Manufacturer;

use App\Models\Manufacturer;
use App\Services\ActionLogService;

final readonly class CreateManufacturerAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array{full_name: string, short_name: string|null, legal_form: string, identification_number: string, legal_address: string, phone: string, email: string, country: string, region: string, city: string|null, is_active: bool}  $data
     */
    public function execute(array $data): Manufacturer
    {
        $manufacturer = Manufacturer::create($data);

        $this->actionLogService->logModelCreated($manufacturer);

        return $manufacturer;
    }
}
