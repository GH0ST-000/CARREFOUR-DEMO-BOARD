<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Services\ActionLogService;

final readonly class UpdateProductAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Product $product, array $data): Product
    {
        $product->update($data);

        $this->actionLogService->logModelUpdated($product, $product->getChanges());

        return $product->fresh();
    }
}
