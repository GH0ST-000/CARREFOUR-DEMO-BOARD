<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Services\ActionLogService;

final readonly class DeleteProductAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    public function execute(Product $product): bool
    {
        $this->actionLogService->logModelDeleted($product);

        return (bool) $product->delete();
    }
}
