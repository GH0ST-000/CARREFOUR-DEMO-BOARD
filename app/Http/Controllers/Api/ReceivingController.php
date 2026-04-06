<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Receiving\CreateReceivingAction;
use App\Actions\Receiving\DeleteReceivingAction;
use App\Actions\Receiving\UpdateReceivingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateReceivingRequest;
use App\Http\Requests\Api\UpdateReceivingRequest;
use App\Http\Resources\ReceivingResource;
use App\Models\Receiving;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReceivingController extends Controller
{
    public function __construct(
        private readonly CreateReceivingAction $createAction,
        private readonly UpdateReceivingAction $updateAction,
        private readonly DeleteReceivingAction $deleteAction,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Receiving::class);

        /** @var int $perPage */
        $perPage = (int) request()->query('per_page', '25');

        $receivings = Receiving::query()
            ->ordered()
            ->cursorPaginate(perPage: $perPage);

        return ReceivingResource::collection($receivings);
    }

    public function store(CreateReceivingRequest $request): ReceivingResource
    {
        $this->authorize('create', Receiving::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $receiving = $this->createAction->execute($validated);

        return new ReceivingResource($receiving);
    }

    public function show(Receiving $receiving): ReceivingResource
    {
        $this->authorize('view', $receiving);

        return new ReceivingResource($receiving);
    }

    public function update(UpdateReceivingRequest $request, Receiving $receiving): ReceivingResource
    {
        $this->authorize('update', $receiving);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $receiving = $this->updateAction->execute($receiving, $validated);

        return new ReceivingResource($receiving);
    }

    public function destroy(Receiving $receiving): JsonResponse
    {
        $this->authorize('delete', $receiving);

        $this->deleteAction->execute($receiving);

        return response()->json(['message' => 'Receiving deleted successfully']);
    }
}
