<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\TicketMessage\CreateTicketMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTicketMessageRequest;
use App\Http\Resources\TicketMessageResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TicketMessageController extends Controller
{
    public function index(Ticket $ticket): AnonymousResourceCollection
    {
        $this->authorize('view', $ticket);

        $perPage = (int) request()->input('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        $messages = $ticket->messages()
            ->with('user')
            ->ordered()
            ->cursorPaginate($perPage);

        return TicketMessageResource::collection($messages);
    }

    public function store(
        CreateTicketMessageRequest $request,
        Ticket $ticket,
        CreateTicketMessageAction $action
    ): TicketMessageResource {
        $this->authorize('addMessage', $ticket);

        if ($ticket->isClosed()) {
            abort(422, 'Cannot add messages to a closed ticket');
        }

        /** @var User $user */
        $user = auth('api')->user();

        $message = $action->execute(array_merge($request->validated(), [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]));

        $message->load('user');

        return new TicketMessageResource($message);
    }
}
