<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Ticket\CloseTicketAction;
use App\Actions\Ticket\CreateTicketAction;
use App\Actions\Ticket\DeleteTicketAction;
use App\Actions\Ticket\ReopenTicketAction;
use App\Actions\Ticket\UpdateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTicketRequest;
use App\Http\Requests\Api\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TicketController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $perPage = (int) request()->input('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        /** @var User $user */
        $user = auth('api')->user();

        $query = Ticket::query()
            ->with(['user', 'assignee'])
            ->withCount('messages');

        if (! $user->can('view_any_ticket')) {
            $query->forUser($user->id);
        }

        if (request()->filled('status')) {
            $query->status((string) request()->input('status'));
        }

        if (request()->filled('priority')) {
            $query->priority((string) request()->input('priority'));
        }

        if (request()->filled('search')) {
            $query->search((string) request()->input('search'));
        }

        $tickets = $query
            ->ordered()
            ->cursorPaginate($perPage);

        return TicketResource::collection($tickets);
    }

    public function store(CreateTicketRequest $request, CreateTicketAction $action): TicketResource
    {
        $this->authorize('create', Ticket::class);

        /** @var User $user */
        $user = auth('api')->user();

        $ticket = $action->execute(array_merge($request->validated(), [
            'user_id' => $user->id,
        ]));

        $ticket->load(['user', 'assignee']);

        return new TicketResource($ticket);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket->load(['user', 'assignee', 'messages.user', 'attachments']);

        return new TicketResource($ticket);
    }

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket,
        UpdateTicketAction $action
    ): TicketResource {
        $this->authorize('update', $ticket);

        /** @var User $user */
        $user = auth('api')->user();

        $data = $request->validated();

        if ($ticket->isOwnedBy($user) && ! $user->can('update_ticket')) {
            $data = array_intersect_key($data, array_flip(['title', 'description']));
        }

        if (isset($data['status']) && $data['status'] === 'closed') {
            $data['closed_at'] = now();
        }

        $ticket = $action->execute($ticket, $data);
        $ticket->load(['user', 'assignee']);

        return new TicketResource($ticket);
    }

    public function destroy(Ticket $ticket, DeleteTicketAction $action): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $action->execute($ticket);

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ]);
    }

    public function close(Ticket $ticket, CloseTicketAction $action): TicketResource
    {
        $this->authorize('close', $ticket);

        if ($ticket->isClosed()) {
            abort(422, 'Ticket is already closed');
        }

        $ticket = $action->execute($ticket);
        $ticket->load(['user', 'assignee']);

        return new TicketResource($ticket);
    }

    public function reopen(Ticket $ticket, ReopenTicketAction $action): TicketResource
    {
        $this->authorize('reopen', $ticket);

        if (! $ticket->isClosed()) {
            abort(422, 'Ticket is not closed');
        }

        $ticket = $action->execute($ticket);
        $ticket->load(['user', 'assignee']);

        return new TicketResource($ticket);
    }
}
