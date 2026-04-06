<?php

namespace App\Http\Controllers\Api;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        $perPage = (int) request()->input('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $products = Product::query()
            ->with('manufacturer')
            ->ordered()
            ->cursorPaginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(CreateProductRequest $request, CreateProductAction $action): ProductResource
    {
        $this->authorize('create', Product::class);

        $product = $action->execute($request->validated());

        return new ProductResource($product);
    }

    public function show(Product $product): ProductResource
    {
        $this->authorize('view', $product);

        $product->load('manufacturer');

        return new ProductResource($product);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $action
    ): ProductResource {
        $this->authorize('update', $product);

        $product = $action->execute($product, $request->validated());
        $product->load('manufacturer');

        return new ProductResource($product);
    }

    public function destroy(Product $product, DeleteProductAction $action): JsonResponse
    {
        $this->authorize('delete', $product);

        $action->execute($product);

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
