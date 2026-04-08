<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\ChatMessagesRead;
use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

final class ChatService
{
    public function findOrCreateDirectConversation(User $authUser, User $targetUser): Conversation
    {
        $existing = $this->findDirectConversation($authUser->id, $targetUser->id);

        if ($existing) {
            return $existing->load('participants.user');
        }

        return DB::transaction(function () use ($authUser, $targetUser): Conversation {
            $conversation = Conversation::create([
                'type' => 'direct',
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $authUser->id,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $targetUser->id,
            ]);

            return $conversation->load('participants.user');
        });
    }

    /**
     * @return CursorPaginator<Conversation>
     */
    public function getUserConversations(User $user, int $perPage = 20): CursorPaginator
    {
        return Conversation::query()
            ->forUser($user->id)
            ->with(['participants.user', 'latestMessage'])
            ->withCount(['messages as unread_count' => function (Builder $query) use ($user): void {
                $query->where('sender_id', '!=', $user->id)
                    ->where('status', 'sent');
            }])
            ->orderedByLatestMessage()
            ->cursorPaginate($perPage);
    }

    public function sendMessage(Conversation $conversation, User $sender, string $body): ChatMessage
    {
        /** @var ChatMessage $message */
        $message = DB::transaction(function () use ($conversation, $sender, $body): ChatMessage {
            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $body,
                'status' => 'sent',
            ]);

            $conversation->update(['last_message_at' => $message->created_at]);

            return $message->load('sender');
        });

        $event = new NewChatMessage($message);
        $event->dontBroadcastToCurrentUser();
        event($event);

        return $message;
    }

    /**
     * @return CursorPaginator<ChatMessage>
     */
    public function getConversationMessages(Conversation $conversation, int $perPage = 50): CursorPaginator
    {
        return $conversation->messages()
            ->with('sender')
            ->ordered()
            ->cursorPaginate($perPage);
    }

    public function markConversationAsRead(Conversation $conversation, User $user): int
    {
        /** @var int $updatedCount */
        $updatedCount = $conversation->messages()
            ->incomingFor($user->id)
            ->unread()
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        if ($updatedCount > 0) {
            $event = new ChatMessagesRead(
                conversationId: $conversation->id,
                readByUserId: $user->id,
                updatedCount: $updatedCount,
                readAt: (string) now()->toISOString(),
            );
            $event->dontBroadcastToCurrentUser();
            event($event);
        }

        return $updatedCount;
    }

    public function getTotalUnreadCount(User $user): int
    {
        return ChatMessage::query()
            ->whereIn('conversation_id', function (QueryBuilder $query) use ($user): void {
                $query->select('conversation_id')
                    ->from('conversation_participants')
                    ->where('user_id', $user->id);
            })
            ->incomingFor($user->id)
            ->unread()
            ->count();
    }

    public function deleteMessage(ChatMessage $message): void
    {
        $message->delete();
    }

    private function findDirectConversation(int $userIdA, int $userIdB): ?Conversation
    {
        return Conversation::query()
            ->direct()
            ->whereHas('participants', fn (Builder $q): Builder => $q->where('user_id', $userIdA))
            ->whereHas('participants', fn (Builder $q): Builder => $q->where('user_id', $userIdB))
            ->first();
    }
}
