<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActionLogResource;
use App\Models\ActionLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ActionLogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ActionLog::class);

        $perPage = (int) request()->input('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $query = ActionLog::query()->with('user');

        if (request()->filled('user_id')) {
            $query->forUser((int) request()->input('user_id'));
        }

        if (request()->filled('action_type')) {
            $query->actionType((string) request()->input('action_type'));
        }

        if (request()->filled('entity_type')) {
            $query->entityType((string) request()->input('entity_type'));
        }

        if (request()->filled('module')) {
            $query->module((string) request()->input('module'));
        }

        if (request()->filled('date_from')) {
            $query->dateFrom((string) request()->input('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->dateTo((string) request()->input('date_to'));
        }

        $logs = $query
            ->ordered()
            ->cursorPaginate($perPage);

        return ActionLogResource::collection($logs);
    }

    public function show(ActionLog $actionLog): ActionLogResource
    {
        $this->authorize('view', $actionLog);

        $actionLog->load('user');

        return new ActionLogResource($actionLog);
    }

    public function search(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ActionLog::class);

        $perPage = (int) request()->input('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $searchQuery = (string) request()->input('q', '');

        $query = ActionLog::search($searchQuery);

        if (request()->filled('user_id')) {
            $query->where('user_id', (int) request()->input('user_id'));
        }

        if (request()->filled('action_type')) {
            $query->where('action_type', (string) request()->input('action_type'));
        }

        if (request()->filled('module')) {
            $query->where('module', (string) request()->input('module'));
        }

        $logs = $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $logs->load('user');

        return ActionLogResource::collection($logs);
    }
}
