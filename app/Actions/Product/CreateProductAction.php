<?php

namespace App\Actions\Product;

use App\Models\Product;

class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Product
    {
        return Product::create($data);
    }
}
