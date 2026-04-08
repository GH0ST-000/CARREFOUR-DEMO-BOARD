<?php

declare(strict_types=1);

use App\Events\ChatMessagesRead;
use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;

describe('NewChatMessage Event', function (): void {
    test('broadcasts on the correct private channel', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();
        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

        $message = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
        ]);

        $event = new NewChatMessage($message);
        $channels = $event->broadcastOn();

        expect($channels)->toHaveCount(1)
            ->and($channels[0])->toBeInstanceOf(PrivateChannel::class)
            ->and($channels[0]->name)->toBe("private-conversation.{$conversation->id}");
    });

    test('uses correct broadcast name', function (): void {
        $message = ChatMessage::factory()->create();

        $event = new NewChatMessage($message);

        expect($event->broadcastAs())->toBe('message.sent');
    });

    test('broadcasts message data with sender', function (): void {
        $message = ChatMessage::factory()->create();

        $event = new NewChatMessage($message);
        $data = $event->broadcastWith();

        expect($data)->toHaveKey('message')
            ->and($data['message'])->toHaveKeys(['id', 'conversation_id', 'sender_id', 'body', 'status']);
    });
});

describe('ChatMessagesRead Event', function (): void {
    test('broadcasts on the correct private channel', function (): void {
        $event = new ChatMessagesRead(
            conversationId: 42,
            readByUserId: 1,
            updatedCount: 5,
            readAt: '2026-04-08T10:00:00.000000Z',
        );

        $channels = $event->broadcastOn();

        expect($channels)->toHaveCount(1)
            ->and($channels[0])->toBeInstanceOf(PrivateChannel::class)
            ->and($channels[0]->name)->toBe('private-conversation.42');
    });

    test('uses correct broadcast name', function (): void {
        $event = new ChatMessagesRead(
            conversationId: 1,
            readByUserId: 1,
            updatedCount: 3,
            readAt: now()->toISOString(),
        );

        expect($event->broadcastAs())->toBe('messages.read');
    });

    test('broadcasts correct data payload', function (): void {
        $readAt = now()->toISOString();
        $event = new ChatMessagesRead(
            conversationId: 10,
            readByUserId: 5,
            updatedCount: 7,
            readAt: $readAt,
        );

        $data = $event->broadcastWith();

        expect($data)->toBe([
            'conversation_id' => 10,
            'read_by_user_id' => 5,
            'updated_count' => 7,
            'read_at' => $readAt,
        ]);
    });
});
