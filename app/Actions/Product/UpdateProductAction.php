<?php

namespace App\Actions\Product;

use App\Models\Product;

class UpdateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }
}
