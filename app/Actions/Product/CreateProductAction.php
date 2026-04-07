<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Services\ActionLogService;

final readonly class CreateProductAction
{
    public function __construct(
        private ActionLogService $actionLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Product
    {
        $product = Product::create($data);

        $this->actionLogService->logModelCreated($product);

        return $product;
    }
}
