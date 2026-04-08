<?php

declare(strict_types=1);

use App\Events\ChatMessagesRead;
use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->chatService = app(ChatService::class);
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

describe('findOrCreateDirectConversation', function (): void {
    test('creates a new conversation when none exists', function (): void {
        $conversation = $this->chatService->findOrCreateDirectConversation($this->user, $this->otherUser);

        expect($conversation)->toBeInstanceOf(Conversation::class)
            ->and($conversation->type)->toBe('direct')
            ->and($conversation->participants)->toHaveCount(2);

        $participantIds = $conversation->participants->pluck('user_id')->sort()->values()->all();
        $expectedIds = collect([$this->user->id, $this->otherUser->id])->sort()->values()->all();

        expect($participantIds)->toBe($expectedIds);
    });

    test('returns existing conversation when one exists', function (): void {
        $first = $this->chatService->findOrCreateDirectConversation($this->user, $this->otherUser);
        $second = $this->chatService->findOrCreateDirectConversation($this->user, $this->otherUser);

        expect($first->id)->toBe($second->id)
            ->and(Conversation::count())->toBe(1);
    });

    test('returns existing conversation when called from other user side', function (): void {
        $first = $this->chatService->findOrCreateDirectConversation($this->user, $this->otherUser);
        $second = $this->chatService->findOrCreateDirectConversation($this->otherUser, $this->user);

        expect($first->id)->toBe($second->id);
    });
});

describe('getUserConversations', function (): void {
    test('returns only conversations where user is participant', function (): void {
        $conversation = Conversation::factory()->create(['last_message_at' => now()]);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $this->user->id]);
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $this->otherUser->id]);

        $thirdUser = User::factory()->create();
        $otherConversation = Conversation::factory()->create(['last_message_at' => now()]);
        ConversationParticipant::create(['conversation_id' => $otherConversation->id, 'user_id' => $this->otherUser->id]);
        ConversationParticipant::create(['conversation_id' => $otherConversation->id, 'user_id' => $thirdUser->id]);

        $result = $this->chatService->getUserConversations($this->user);

        expect($result)->toHaveCount(1)
            ->and($result->first()->id)->toBe($conversation->id);
    });
});

describe('sendMessage', function (): void {
    test('creates message and updates conversation timestamp', function (): void {
        Event::fake([NewChatMessage::class]);

        $conversation = Conversation::factory()->create();
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $this->user->id]);

        $message = $this->chatService->sendMessage($conversation, $this->user, 'Hello!');

        expect($message)->toBeInstanceOf(ChatMessage::class)
            ->and($message->body)->toBe('Hello!')
            ->and($message->sender_id)->toBe($this->user->id)
            ->and($message->status)->toBe('sent');

        $conversation->refresh();
        expect($conversation->last_message_at)->not->toBeNull();

        Event::assertDispatched(NewChatMessage::class, function (NewChatMessage $event) use ($message): bool {
            return $event->chatMessage->id === $message->id;
        });
    });
});

describe('getConversationMessages', function (): void {
    test('returns paginated messages ordered by newest first', function (): void {
        $conversation = Conversation::factory()->create();

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'created_at' => now()->subMinute(),
        ]);

        $newest = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $result = $this->chatService->getConversationMessages($conversation);

        expect($result)->toHaveCount(2)
            ->and($result->first()->id)->toBe($newest->id);
    });
});

describe('markConversationAsRead', function (): void {
    test('marks only incoming unread messages as read', function (): void {
        Event::fake([ChatMessagesRead::class]);

        $conversation = Conversation::factory()->create();

        ChatMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'status' => 'sent',
        ]);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'status' => 'sent',
        ]);

        $updated = $this->chatService->markConversationAsRead($conversation, $this->user);

        expect($updated)->toBe(3);

        Event::assertDispatched(ChatMessagesRead::class, function (ChatMessagesRead $event) use ($conversation): bool {
            return $event->conversationId === $conversation->id
                && $event->updatedCount === 3;
        });
    });
});

describe('getTotalUnreadCount', function (): void {
    test('counts unread messages across all conversations', function (): void {
        $conversation1 = Conversation::factory()->create();
        ConversationParticipant::create(['conversation_id' => $conversation1->id, 'user_id' => $this->user->id]);

        $conversation2 = Conversation::factory()->create();
        ConversationParticipant::create(['conversation_id' => $conversation2->id, 'user_id' => $this->user->id]);

        ChatMessage::factory()->count(2)->create([
            'conversation_id' => $conversation1->id,
            'sender_id' => $this->otherUser->id,
            'status' => 'sent',
        ]);

        $thirdUser = User::factory()->create();
        ConversationParticipant::create(['conversation_id' => $conversation2->id, 'user_id' => $thirdUser->id]);
        ChatMessage::factory()->count(3)->create([
            'conversation_id' => $conversation2->id,
            'sender_id' => $thirdUser->id,
            'status' => 'sent',
        ]);

        expect($this->chatService->getTotalUnreadCount($this->user))->toBe(5);
    });
});

describe('deleteMessage', function (): void {
    test('soft deletes a message', function (): void {
        $message = ChatMessage::factory()->create();

        $this->chatService->deleteMessage($message);

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    });
});
