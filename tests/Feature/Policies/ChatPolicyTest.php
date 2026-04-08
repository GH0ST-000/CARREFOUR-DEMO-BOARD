<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->conversation = Conversation::factory()->create(['type' => 'direct']);

    ConversationParticipant::create([
        'conversation_id' => $this->conversation->id,
        'user_id' => $this->user->id,
    ]);

    ConversationParticipant::create([
        'conversation_id' => $this->conversation->id,
        'user_id' => $this->otherUser->id,
    ]);
});

describe('ConversationPolicy', function (): void {
    test('any authenticated user can view conversations list', function (): void {
        expect($this->user->can('viewAny', Conversation::class))->toBeTrue();
    });

    test('participant can view conversation', function (): void {
        expect($this->user->can('view', $this->conversation))->toBeTrue();
    });

    test('non-participant cannot view conversation', function (): void {
        $outsider = User::factory()->create();

        expect($outsider->can('view', $this->conversation))->toBeFalse();
    });

    test('participant can send message', function (): void {
        expect($this->user->can('sendMessage', $this->conversation))->toBeTrue();
    });

    test('non-participant cannot send message', function (): void {
        $outsider = User::factory()->create();

        expect($outsider->can('sendMessage', $this->conversation))->toBeFalse();
    });

    test('participant can mark as read', function (): void {
        expect($this->user->can('markAsRead', $this->conversation))->toBeTrue();
    });

    test('non-participant cannot mark as read', function (): void {
        $outsider = User::factory()->create();

        expect($outsider->can('markAsRead', $this->conversation))->toBeFalse();
    });
});

describe('ChatMessagePolicy', function (): void {
    test('sender can delete own message', function (): void {
        $message = ChatMessage::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
        ]);

        expect($this->user->can('delete', $message))->toBeTrue();
    });

    test('user cannot delete message sent by another user', function (): void {
        $message = ChatMessage::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->otherUser->id,
        ]);

        expect($this->user->can('delete', $message))->toBeFalse();
    });
});
