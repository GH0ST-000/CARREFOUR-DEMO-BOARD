<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Carbon;

describe('Conversation Model', function (): void {
    test('has participants relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        expect($conversation->participants)->toHaveCount(1)
            ->and($conversation->participants->first()->user_id)->toBe($user->id);
    });

    test('has messages relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
        ]);

        expect($conversation->messages)->toHaveCount(1);
    });

    test('has latestMessage relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now()->subMinute(),
        ]);

        $latest = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'created_at' => now(),
        ]);

        expect($conversation->latestMessage->id)->toBe($latest->id);
    });

    test('hasParticipant returns true when participants loaded', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $conversation->load('participants');

        expect($conversation->hasParticipant($user->id))->toBeTrue()
            ->and($conversation->hasParticipant($user->id + 999))->toBeFalse();
    });

    test('hasParticipant queries database when participants not loaded', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $fresh = Conversation::find($conversation->id);

        expect($fresh->hasParticipant($user->id))->toBeTrue()
            ->and($fresh->hasParticipant($user->id + 999))->toBeFalse();
    });

    test('isDirect returns correct value', function (): void {
        $direct = Conversation::factory()->create(['type' => 'direct']);
        $other = Conversation::factory()->create(['type' => 'group']);

        expect($direct->isDirect())->toBeTrue()
            ->and($other->isDirect())->toBeFalse();
    });

    test('casts last_message_at to datetime', function (): void {
        $conversation = Conversation::factory()->create([
            'last_message_at' => '2026-04-07 10:00:00',
        ]);

        expect($conversation->last_message_at)->toBeInstanceOf(Carbon::class);
    });

    test('scopeDirect filters direct conversations', function (): void {
        Conversation::factory()->create(['type' => 'direct']);
        Conversation::factory()->create(['type' => 'group']);

        expect(Conversation::direct()->count())->toBe(1);
    });

    test('scopeForUser filters by participant', function (): void {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        Conversation::factory()->create();

        expect(Conversation::forUser($user->id)->count())->toBe(1);
    });

    test('scopeOrderedByLatestMessage sorts correctly', function (): void {
        $old = Conversation::factory()->create(['last_message_at' => now()->subHour()]);
        $new = Conversation::factory()->create(['last_message_at' => now()]);

        $result = Conversation::orderedByLatestMessage()->get();

        expect($result->first()->id)->toBe($new->id)
            ->and($result->last()->id)->toBe($old->id);
    });
});

describe('ConversationParticipant Model', function (): void {
    test('has conversation relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        $participant = ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        expect($participant->conversation)->toBeInstanceOf(Conversation::class)
            ->and($participant->conversation->id)->toBe($conversation->id);
    });

    test('has user relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        $participant = ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        expect($participant->user)->toBeInstanceOf(User::class)
            ->and($participant->user->id)->toBe($user->id);
    });
});

describe('ChatMessage Model', function (): void {
    test('has conversation relationship', function (): void {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();

        $message = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
        ]);

        expect($message->conversation)->toBeInstanceOf(Conversation::class)
            ->and($message->conversation->id)->toBe($conversation->id);
    });

    test('has sender relationship', function (): void {
        $user = User::factory()->create();

        $message = ChatMessage::factory()->create([
            'sender_id' => $user->id,
        ]);

        expect($message->sender)->toBeInstanceOf(User::class)
            ->and($message->sender->id)->toBe($user->id);
    });

    test('isSentBy returns correct value', function (): void {
        $user = User::factory()->create();

        $message = ChatMessage::factory()->create([
            'sender_id' => $user->id,
        ]);

        expect($message->isSentBy($user->id))->toBeTrue()
            ->and($message->isSentBy($user->id + 999))->toBeFalse();
    });

    test('isRead returns correct value', function (): void {
        $sent = ChatMessage::factory()->sent()->create();
        $read = ChatMessage::factory()->read()->create();

        expect($sent->isRead())->toBeFalse()
            ->and($read->isRead())->toBeTrue();
    });

    test('markAsRead updates status and read_at', function (): void {
        $message = ChatMessage::factory()->sent()->create();

        expect($message->isRead())->toBeFalse()
            ->and($message->read_at)->toBeNull();

        $message->markAsRead();
        $message->refresh();

        expect($message->isRead())->toBeTrue()
            ->and($message->read_at)->toBeInstanceOf(Carbon::class);
    });

    test('casts read_at to datetime', function (): void {
        $message = ChatMessage::factory()->read()->create([
            'read_at' => '2026-04-07 10:00:00',
        ]);

        expect($message->read_at)->toBeInstanceOf(Carbon::class);
    });

    test('scopeUnread filters unread messages', function (): void {
        ChatMessage::factory()->sent()->create();
        ChatMessage::factory()->read()->create();

        expect(ChatMessage::unread()->count())->toBe(1);
    });

    test('scopeIncomingFor filters messages from others', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ChatMessage::factory()->create(['sender_id' => $user->id]);
        ChatMessage::factory()->create(['sender_id' => $otherUser->id]);

        expect(ChatMessage::incomingFor($user->id)->count())->toBe(1);
    });

    test('scopeOrdered sorts by created_at desc', function (): void {
        $old = ChatMessage::factory()->create(['created_at' => now()->subMinute()]);
        $new = ChatMessage::factory()->create(['created_at' => now()]);

        $result = ChatMessage::ordered()->get();

        expect($result->first()->id)->toBe($new->id)
            ->and($result->last()->id)->toBe($old->id);
    });

    test('supports soft deletes', function (): void {
        $message = ChatMessage::factory()->create();

        $message->delete();

        expect(ChatMessage::withTrashed()->find($message->id))->not->toBeNull()
            ->and(ChatMessage::find($message->id))->toBeNull();
    });
});
