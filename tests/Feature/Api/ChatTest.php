<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

function createDirectConversation(User $userA, User $userB): Conversation
{
    $conversation = Conversation::factory()->create(['type' => 'direct']);

    ConversationParticipant::create([
        'conversation_id' => $conversation->id,
        'user_id' => $userA->id,
    ]);

    ConversationParticipant::create([
        'conversation_id' => $conversation->id,
        'user_id' => $userB->id,
    ]);

    return $conversation;
}

describe('POST /api/chat/conversations/direct', function (): void {
    test('creates a new direct conversation', function (): void {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/chat/conversations/direct', [
                'user_id' => $this->otherUser->id,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'participants',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.type', 'direct');

        $this->assertDatabaseHas('conversations', ['type' => 'direct']);
        $this->assertDatabaseHas('conversation_participants', [
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('conversation_participants', [
            'user_id' => $this->otherUser->id,
        ]);
    });

    test('returns existing conversation if already exists', function (): void {
        $existing = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/chat/conversations/direct', [
                'user_id' => $this->otherUser->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $existing->id);

        expect(Conversation::count())->toBe(1);
    });

    test('cannot create conversation with self', function (): void {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/chat/conversations/direct', [
                'user_id' => $this->user->id,
            ]);

        $response->assertStatus(422);
    });

    test('validates user_id is required', function (): void {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/chat/conversations/direct', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    });

    test('validates user_id exists', function (): void {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/chat/conversations/direct', [
                'user_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    });

    test('requires authentication', function (): void {
        $response = $this->postJson('/api/chat/conversations/direct', [
            'user_id' => $this->otherUser->id,
        ]);

        $response->assertUnauthorized();
    });
});

describe('GET /api/chat/conversations', function (): void {
    test('returns user conversations', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'body' => 'Hello',
        ]);

        $conversation->update(['last_message_at' => now()]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/conversations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'participants',
                        'last_message',
                        'unread_count',
                    ],
                ],
            ]);
    });

    test('does not return conversations where user is not participant', function (): void {
        $thirdUser = User::factory()->create();
        createDirectConversation($this->otherUser, $thirdUser);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/conversations');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('includes unread count', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

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

        $conversation->update(['last_message_at' => now()]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/conversations');

        $response->assertOk();

        $data = $response->json('data');
        expect($data[0]['unread_count'])->toBe(3);
    });

    test('orders by last message descending', function (): void {
        $conversation1 = createDirectConversation($this->user, $this->otherUser);
        $conversation1->update(['last_message_at' => now()->subHour()]);

        $thirdUser = User::factory()->create();
        $conversation2 = createDirectConversation($this->user, $thirdUser);
        $conversation2->update(['last_message_at' => now()]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/conversations');

        $response->assertOk();

        $data = $response->json('data');
        expect($data[0]['id'])->toBe($conversation2->id);
        expect($data[1]['id'])->toBe($conversation1->id);
    });

    test('supports per_page parameter', function (): void {
        for ($i = 0; $i < 5; $i++) {
            $otherUser = User::factory()->create();
            $conv = createDirectConversation($this->user, $otherUser);
            $conv->update(['last_message_at' => now()->subMinutes($i)]);
        }

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/conversations?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('requires authentication', function (): void {
        $response = $this->getJson('/api/chat/conversations');

        $response->assertUnauthorized();
    });
});

describe('GET /api/chat/conversations/{conversation}', function (): void {
    test('returns conversation details for participant', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $conversation->id)
            ->assertJsonPath('data.type', 'direct')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'participants',
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    test('returns 403 for non-participant', function (): void {
        $thirdUser = User::factory()->create();
        $conversation = createDirectConversation($this->otherUser, $thirdUser);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}");

        $response->assertForbidden();
    });
});

describe('GET /api/chat/conversations/{conversation}/messages', function (): void {
    test('returns paginated messages for participant', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'conversation_id',
                        'sender_id',
                        'body',
                        'status',
                        'read_at',
                        'created_at',
                    ],
                ],
            ]);
    });

    test('messages are returned in descending order', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $old = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'created_at' => now()->subMinutes(5),
        ]);

        $new = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}/messages");

        $response->assertOk();

        $data = $response->json('data');
        expect($data[0]['id'])->toBe($new->id);
        expect($data[1]['id'])->toBe($old->id);
    });

    test('supports per_page parameter', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}/messages?per_page=3");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('returns 403 for non-participant', function (): void {
        $thirdUser = User::factory()->create();
        $conversation = createDirectConversation($this->otherUser, $thirdUser);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}/messages");

        $response->assertForbidden();
    });

    test('excludes soft-deleted messages', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
        ]);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'deleted_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/chat/conversations/{$conversation->id}/messages");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('POST /api/chat/conversations/{conversation}/messages', function (): void {
    test('sends a message to conversation', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", [
                'body' => 'Hello there!',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Hello there!')
            ->assertJsonPath('data.sender_id', $this->user->id)
            ->assertJsonPath('data.conversation_id', $conversation->id)
            ->assertJsonPath('data.status', 'sent');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'body' => 'Hello there!',
        ]);
    });

    test('updates conversation last_message_at', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        expect($conversation->last_message_at)->toBeNull();

        $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", [
                'body' => 'Hello',
            ]);

        $conversation->refresh();
        expect($conversation->last_message_at)->not->toBeNull();
    });

    test('validates body is required', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    });

    test('validates body max length', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", [
                'body' => str_repeat('a', 5001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    });

    test('trims whitespace from body', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", [
                'body' => '  Hello  ',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Hello');
    });

    test('returns 403 for non-participant', function (): void {
        $thirdUser = User::factory()->create();
        $conversation = createDirectConversation($this->otherUser, $thirdUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/messages", [
                'body' => 'Hello',
            ]);

        $response->assertForbidden();
    });
});

describe('POST /api/chat/conversations/{conversation}/read', function (): void {
    test('marks incoming unread messages as read', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

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

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/read");

        $response->assertOk()
            ->assertJsonPath('conversation_id', $conversation->id)
            ->assertJsonPath('updated_count', 3);

        expect(ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', $this->otherUser->id)
            ->where('status', 'read')
            ->count()
        )->toBe(3);

        expect(ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', $this->user->id)
            ->where('status', 'sent')
            ->count()
        )->toBe(1);
    });

    test('does not mark own messages as read', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/read");

        $response->assertOk()
            ->assertJsonPath('updated_count', 0);
    });

    test('returns 403 for non-participant', function (): void {
        $thirdUser = User::factory()->create();
        $conversation = createDirectConversation($this->otherUser, $thirdUser);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/read");

        $response->assertForbidden();
    });
});

describe('GET /api/chat/unread-count', function (): void {
    test('returns total unread count', function (): void {
        $conversation1 = createDirectConversation($this->user, $this->otherUser);
        $thirdUser = User::factory()->create();
        $conversation2 = createDirectConversation($this->user, $thirdUser);

        ChatMessage::factory()->count(3)->create([
            'conversation_id' => $conversation1->id,
            'sender_id' => $this->otherUser->id,
            'status' => 'sent',
        ]);

        ChatMessage::factory()->count(2)->create([
            'conversation_id' => $conversation2->id,
            'sender_id' => $thirdUser->id,
            'status' => 'sent',
        ]);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation1->id,
            'sender_id' => $this->user->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/unread-count');

        $response->assertOk()
            ->assertJsonPath('unread_count', 5);
    });

    test('excludes already read messages', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        ChatMessage::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'status' => 'sent',
        ]);

        ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
            'status' => 'read',
            'read_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/unread-count');

        $response->assertOk()
            ->assertJsonPath('unread_count', 2);
    });

    test('returns zero when no unread messages', function (): void {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/chat/unread-count');

        $response->assertOk()
            ->assertJsonPath('unread_count', 0);
    });

    test('requires authentication', function (): void {
        $response = $this->getJson('/api/chat/unread-count');

        $response->assertUnauthorized();
    });
});

describe('DELETE /api/chat/messages/{chatMessage}', function (): void {
    test('sender can soft delete own message', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $message = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/chat/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Message deleted successfully');

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    });

    test('cannot delete other users message', function (): void {
        $conversation = createDirectConversation($this->user, $this->otherUser);

        $message = ChatMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/chat/messages/{$message->id}");

        $response->assertForbidden();
    });

    test('requires authentication', function (): void {
        $message = ChatMessage::factory()->create();

        $response = $this->deleteJson("/api/chat/messages/{$message->id}");

        $response->assertUnauthorized();
    });
});

describe('Broadcasting Events', function (): void {
    test('does not dispatch ChatMessagesRead when no messages to mark', function (): void {
        Event::fake([ChatMessagesRead::class]);

        $conversation = createDirectConversation($this->user, $this->otherUser);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/chat/conversations/{$conversation->id}/read");

        Event::assertNotDispatched(ChatMessagesRead::class);
    });
});
