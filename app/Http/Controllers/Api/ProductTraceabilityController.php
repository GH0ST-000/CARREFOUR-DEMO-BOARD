<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignProductToBranchRequest;
use App\Http\Resources\ProductTraceabilityEventResource;
use App\Models\Batch;
use App\Models\BranchProductBatch;
use App\Models\Product;
use App\Models\ProductTraceabilityEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

final class ProductTraceabilityController extends Controller
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $this->authorize('view', $product);

        $events = ProductTraceabilityEvent::query()
            ->where('product_id', $product->id)
            ->orderByDesc('transferred_at')
            ->cursorPaginate(request()->integer('per_page', 50));

        return ProductTraceabilityEventResource::collection($events);
    }

    public function store(AssignProductToBranchRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        $batchId = $data['batch_id'] ?? null;
        $batch = $batchId ? Batch::query()->findOrFail($batchId) : null;
        if ($batch !== null && $batch->product_id !== $product->id) {
            abort(422, 'Batch does not belong to product.');
        }

        $batchNumber = (string) ($data['batch_number'] ?? ($batch?->batch_number ?? ''));
        if ($batchNumber === '') {
            abort(422, 'batch_number is required when batch_id is not provided.');
        }

        $transferredAt = isset($data['transferred_at']) ? Carbon::parse((string) $data['transferred_at']) : now();

        $event = ProductTraceabilityEvent::query()->create([
            'product_id' => $product->id,
            'batch_id' => $batch?->id,
            'from_branch_id' => $data['from_branch_id'] ?? null,
            'to_branch_id' => $data['to_branch_id'],
            'quantity' => $data['quantity'],
            'transferred_at' => $transferredAt,
            'responsible_user_id' => $data['responsible_user_id'] ?? null,
            'created_by_user_id' => auth('api')->id(),
        ]);

        BranchProductBatch::query()->updateOrCreate(
            [
                'branch_id' => $data['to_branch_id'],
                'product_id' => $product->id,
                'batch_number' => $batchNumber,
            ],
            [
                'batch_id' => $batch?->id,
                'quantity' => $data['quantity'],
                'production_date' => $batch?->production_date,
                'expiration_date' => $batch?->expiry_date,
                'transferred_at' => $transferredAt,
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
            ],
        );

        return (new ProductTraceabilityEventResource($event))->response()->setStatusCode(201);
    }
}
