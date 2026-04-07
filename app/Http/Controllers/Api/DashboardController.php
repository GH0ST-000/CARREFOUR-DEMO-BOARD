<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Actions\Dashboard\GetExpiringBatchesAction;
use App\Actions\Dashboard\GetRecentAdditionsAction;
use App\Actions\Dashboard\GetVisualizationDataAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly GetDashboardStatsAction $statsAction,
        private readonly GetExpiringBatchesAction $expiringBatchesAction,
        private readonly GetRecentAdditionsAction $recentAdditionsAction,
        private readonly GetVisualizationDataAction $visualizationAction,
    ) {}

    public function stats(): JsonResponse
    {
        $this->authorize('viewDashboard', User::class);

        $stats = $this->statsAction->execute();

        return response()->json(['data' => $stats]);
    }

    public function expiringBatches(): AnonymousResourceCollection
    {
        $this->authorize('viewDashboard', User::class);

        /** @var int $days */
        $days = (int) request()->query('days', '30');
        /** @var int $perPage */
        $perPage = (int) request()->query('per_page', '25');

        return $this->expiringBatchesAction->execute($days, $perPage);
    }

    public function recentAdditions(): JsonResponse
    {
        $this->authorize('viewDashboard', User::class);

        /** @var int $days */
        $days = (int) request()->query('days', '7');
        /** @var int $limit */
        $limit = (int) request()->query('limit', '10');

        $additions = $this->recentAdditionsAction->execute($days, $limit);

        return response()->json(['data' => $additions]);
    }

    public function visualization(): JsonResponse
    {
        $this->authorize('viewDashboard', User::class);

        $typeParam = request()->query('type', 'overview');
        $type = is_string($typeParam) ? $typeParam : 'overview';

        $data = $this->visualizationAction->execute($type);

        return response()->json(['data' => $data]);
    }
}
