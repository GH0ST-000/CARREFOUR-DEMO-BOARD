<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\WarehouseLocation\CreateWarehouseLocationAction;
use App\Actions\WarehouseLocation\DeleteWarehouseLocationAction;
use App\Actions\WarehouseLocation\UpdateWarehouseLocationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWarehouseLocationRequest;
use App\Http\Requests\Api\UpdateWarehouseLocationRequest;
use App\Http\Resources\WarehouseLocationResource;
use App\Models\WarehouseLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class WarehouseLocationController extends Controller
{
    public function __construct(
        private readonly CreateWarehouseLocationAction $createAction,
        private readonly UpdateWarehouseLocationAction $updateAction,
        private readonly DeleteWarehouseLocationAction $deleteAction,
    ) {}

    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', WarehouseLocation::class);

        $locations = WarehouseLocation::query()
            ->with(['parent', 'responsibleUser'])
            ->ordered()
            ->cursorPaginate(request()->integer('per_page', 25));

        return WarehouseLocationResource::collection($locations);
    }

    public function store(CreateWarehouseLocationRequest $request): WarehouseLocationResource
    {
        $this->authorize('create', WarehouseLocation::class);

        $location = $this->createAction->execute($request->validated());

        return new WarehouseLocationResource($location);
    }

    public function show(WarehouseLocation $warehouseLocation): WarehouseLocationResource
    {
        $this->authorize('view', $warehouseLocation);

        return new WarehouseLocationResource($warehouseLocation);
    }

    public function update(UpdateWarehouseLocationRequest $request, WarehouseLocation $warehouseLocation): WarehouseLocationResource
    {
        $this->authorize('update', $warehouseLocation);

        $location = $this->updateAction->execute($warehouseLocation, $request->validated());

        return new WarehouseLocationResource($location);
    }

    public function destroy(WarehouseLocation $warehouseLocation): JsonResponse
    {
        $this->authorize('delete', $warehouseLocation);

        $this->deleteAction->execute($warehouseLocation);

        return response()->json(['message' => 'Warehouse location deleted successfully']);
    }
}
