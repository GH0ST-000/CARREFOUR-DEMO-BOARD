<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateDirectConversationRequest;
use App\Http\Requests\Api\SendChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ConversationResource;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    public function createOrGetDirect(CreateDirectConversationRequest $request): ConversationResource
    {
        /** @var User $authUser */
        $authUser = auth('api')->user();
        $targetUserId = (int) $request->validated('user_id');

        if ($authUser->id === $targetUserId) {
            abort(422, 'Cannot create a conversation with yourself.');
        }

        $targetUser = User::findOrFail($targetUserId);

        $conversation = $this->chatService->findOrCreateDirectConversation($authUser, $targetUser);

        return new ConversationResource($conversation);
    }

    public function indexConversations(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Conversation::class);

        /** @var User $user */
        $user = auth('api')->user();

        $perPage = (int) request()->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $conversations = $this->chatService->getUserConversations($user, $perPage);

        return ConversationResource::collection($conversations);
    }

    public function showConversation(Conversation $conversation): ConversationResource
    {
        $this->authorize('view', $conversation);

        $conversation->load('participants.user');

        return new ConversationResource($conversation);
    }

    public function indexMessages(Conversation $conversation): AnonymousResourceCollection
    {
        $this->authorize('view', $conversation);

        $perPage = (int) request()->input('per_page', 50);
        $perPage = max(1, min($perPage, 100));

        $messages = $this->chatService->getConversationMessages($conversation, $perPage);

        return ChatMessageResource::collection($messages);
    }

    public function sendMessage(SendChatMessageRequest $request, Conversation $conversation): ChatMessageResource
    {
        $this->authorize('sendMessage', $conversation);

        /** @var User $user */
        $user = auth('api')->user();

        $message = $this->chatService->sendMessage(
            $conversation,
            $user,
            $request->validated('body'),
        );

        return new ChatMessageResource($message);
    }

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $this->authorize('markAsRead', $conversation);

        /** @var User $user */
        $user = auth('api')->user();

        $updatedCount = $this->chatService->markConversationAsRead($conversation, $user);

        return response()->json([
            'conversation_id' => $conversation->id,
            'updated_count' => $updatedCount,
            'read_at' => now()->toISOString(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();

        return response()->json([
            'unread_count' => $this->chatService->getTotalUnreadCount($user),
        ]);
    }

    public function destroyMessage(ChatMessage $chatMessage): JsonResponse
    {
        $this->authorize('delete', $chatMessage);

        $this->chatService->deleteMessage($chatMessage);

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }
}
