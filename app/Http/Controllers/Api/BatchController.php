<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Batch\CreateBatchAction;
use App\Actions\Batch\DeleteBatchAction;
use App\Actions\Batch\UpdateBatchAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateBatchRequest;
use App\Http\Requests\Api\UpdateBatchRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class BatchController extends Controller
{
    public function __construct(
        private readonly CreateBatchAction $createAction,
        private readonly UpdateBatchAction $updateAction,
        private readonly DeleteBatchAction $deleteAction,
    ) {}

    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', Batch::class);

        $batches = Batch::query()
            ->with(['product', 'warehouseLocation'])
            ->ordered()
            ->cursorPaginate(request()->integer('per_page', 25));

        return BatchResource::collection($batches);
    }

    public function store(CreateBatchRequest $request): BatchResource
    {
        $this->authorize('create', Batch::class);

        $batch = $this->createAction->execute($request->validated());

        return new BatchResource($batch);
    }

    public function show(Batch $batch): BatchResource
    {
        $this->authorize('view', $batch);

        return new BatchResource($batch);
    }

    public function update(UpdateBatchRequest $request, Batch $batch): BatchResource
    {
        $this->authorize('update', $batch);

        $batch = $this->updateAction->execute($batch, $request->validated());

        return new BatchResource($batch);
    }

    public function destroy(Batch $batch): JsonResponse
    {
        $this->authorize('delete', $batch);

        $this->deleteAction->execute($batch);

        return response()->json(['message' => 'Batch deleted successfully']);
    }
}
