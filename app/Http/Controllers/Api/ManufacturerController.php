<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Manufacturer\CreateManufacturerAction;
use App\Actions\Manufacturer\DeleteManufacturerAction;
use App\Actions\Manufacturer\UpdateManufacturerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateManufacturerRequest;
use App\Http\Requests\Api\UpdateManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\Models\Manufacturer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ManufacturerController extends Controller
{
    public function __construct(
        private readonly CreateManufacturerAction $createAction,
        private readonly UpdateManufacturerAction $updateAction,
        private readonly DeleteManufacturerAction $deleteAction,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Manufacturer::class);

        $perPage = (int) request()->query('per_page', '25');
        $perPage = min(max($perPage, 1), 100);

        $manufacturers = Manufacturer::query()
            ->ordered()
            ->cursorPaginate($perPage);

        return ManufacturerResource::collection($manufacturers);
    }

    public function store(CreateManufacturerRequest $request): ManufacturerResource
    {
        $this->authorize('create', Manufacturer::class);

        /** @var array{full_name: string, short_name: string|null, legal_form: string, identification_number: string, legal_address: string, phone: string, email: string, country: string, region: string, city: string|null, is_active: bool} $validated */
        $validated = $request->validated();

        $manufacturer = $this->createAction->execute($validated);

        return new ManufacturerResource($manufacturer);
    }

    public function show(Manufacturer $manufacturer): ManufacturerResource
    {
        $this->authorize('view', $manufacturer);

        return new ManufacturerResource($manufacturer);
    }

    public function update(UpdateManufacturerRequest $request, Manufacturer $manufacturer): ManufacturerResource
    {
        $this->authorize('update', $manufacturer);

        /** @var array{full_name?: string, short_name?: string|null, legal_form?: string, identification_number?: string, legal_address?: string, phone?: string, email?: string, country?: string, region?: string, city?: string|null, is_active?: bool} $validated */
        $validated = $request->validated();

        $updatedManufacturer = $this->updateAction->execute($manufacturer, $validated);

        return new ManufacturerResource($updatedManufacturer);
    }

    public function destroy(Manufacturer $manufacturer): JsonResponse
    {
        $this->authorize('delete', $manufacturer);

        $this->deleteAction->execute($manufacturer);

        return response()->json([
            'message' => 'Manufacturer deleted successfully',
        ]);
    }
}
