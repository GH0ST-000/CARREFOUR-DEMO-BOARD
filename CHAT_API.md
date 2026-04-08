# Chat Module API Documentation

Base URL: `/api/chat`

All endpoints require JWT authentication via the `Authorization: Bearer <token>` header.

---

## Endpoints

### 1. List Users for Chat

Returns a paginated, searchable list of users available to start a conversation with. The authenticated user is excluded from the results.

```
GET /api/chat/users
```

**Query Parameters:**

| Param      | Type    | Default | Description                          |
|------------|---------|---------|--------------------------------------|
| `search`   | string  | —       | Filter by name or email (partial)    |
| `per_page` | integer | 20      | Items per page (1–100)               |
| `page`     | integer | 1       | Page number                          |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 25,
      "name": "Nika",
      "email": "nika@example.com",
      "branch_id": null,
      "roles": ["manager"],
      "permissions": ["view_tickets"]
    }
  ],
  "links": {
    "first": "/api/chat/users?page=1",
    "last": "/api/chat/users?page=3",
    "prev": null,
    "next": "/api/chat/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 50
  }
}
```

**Frontend Usage Notes:**

- Use this endpoint to populate a user picker when starting a new conversation.
- The `search` parameter filters by both name and email (case-insensitive partial match).
- Results are ordered alphabetically by name.
- The authenticated user is always excluded from the list.

---

### 2. Create or Get Direct Conversation

Creates a direct conversation between the authenticated user and another user. Returns the existing conversation if one already exists.

```
POST /api/chat/conversations/direct
```

**Request Body:**

```json
{
  "user_id": 25
}
```

**Validation Rules:**

| Field     | Rules                            |
|-----------|----------------------------------|
| `user_id` | required, integer, must exist    |

- Cannot create a conversation with yourself (returns `422`).

**Response (201 Created — new conversation):**

```json
{
  "data": {
    "id": 10,
    "type": "direct",
    "participants": [
      { "id": 5, "name": "Luka" },
      { "id": 25, "name": "Nika" }
    ],
    "last_message": null,
    "unread_count": 0,
    "last_message_at": null,
    "created_at": "2026-04-07T10:00:00.000000Z",
    "updated_at": "2026-04-07T10:00:00.000000Z"
  }
}
```

**Response (200 OK — existing conversation):**

Same structure as above but with status `200`.

---

### 3. List My Conversations

Returns paginated conversations for the authenticated user, sorted by latest message.

```
GET /api/chat/conversations
```

**Query Parameters:**

| Param      | Type    | Default | Description              |
|------------|---------|---------|--------------------------|
| `per_page` | integer | 20      | Items per page (1–100)   |
| `cursor`   | string  | —       | Cursor for next page     |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 10,
      "type": "direct",
      "participants": [
        { "id": 5, "name": "Luka" },
        { "id": 25, "name": "Nika" }
      ],
      "last_message": {
        "id": 99,
        "conversation_id": 10,
        "sender_id": 25,
        "sender": null,
        "body": "Hello",
        "status": "sent",
        "read_at": null,
        "created_at": "2026-04-07T10:10:00.000000Z",
        "updated_at": "2026-04-07T10:10:00.000000Z"
      },
      "unread_count": 3,
      "last_message_at": "2026-04-07T10:10:00.000000Z",
      "created_at": "2026-04-07T10:00:00.000000Z",
      "updated_at": "2026-04-07T10:10:00.000000Z"
    }
  ],
  "meta": {
    "path": "/api/chat/conversations",
    "per_page": 20,
    "next_cursor": "eyJpZCI6MTB9",
    "prev_cursor": null
  }
}
```

**Frontend Usage Notes:**

- Uses cursor pagination. Store `meta.next_cursor` and pass as `?cursor=...` for next page.
- `unread_count` represents unread incoming messages in each conversation.
- `last_message` is the most recent message in the conversation (useful for preview).

---

### 4. Get Conversation Details

Returns metadata for a single conversation.

```
GET /api/chat/conversations/{conversationId}
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 10,
    "type": "direct",
    "participants": [
      { "id": 5, "name": "Luka" },
      { "id": 25, "name": "Nika" }
    ],
    "last_message": null,
    "unread_count": 0,
    "last_message_at": "2026-04-07T10:10:00.000000Z",
    "created_at": "2026-04-07T10:00:00.000000Z",
    "updated_at": "2026-04-07T10:10:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Condition                          |
|--------|------------------------------------|
| 403    | User is not a participant          |
| 404    | Conversation not found             |

---

### 5. Get Conversation Messages

Returns paginated message history for a conversation.

```
GET /api/chat/conversations/{conversationId}/messages
```

**Query Parameters:**

| Param      | Type    | Default | Description              |
|------------|---------|---------|--------------------------|
| `per_page` | integer | 50      | Items per page (1–100)   |
| `cursor`   | string  | —       | Cursor for next page     |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 101,
      "conversation_id": 10,
      "sender_id": 25,
      "sender": {
        "id": 25,
        "name": "Nika",
        "email": "nika@example.com",
        "branch_id": null,
        "roles": [],
        "permissions": []
      },
      "body": "Hello",
      "status": "sent",
      "read_at": null,
      "created_at": "2026-04-07T10:10:00.000000Z",
      "updated_at": "2026-04-07T10:10:00.000000Z"
    },
    {
      "id": 100,
      "conversation_id": 10,
      "sender_id": 5,
      "sender": {
        "id": 5,
        "name": "Luka",
        "email": "luka@example.com",
        "branch_id": null,
        "roles": [],
        "permissions": []
      },
      "body": "Hi there!",
      "status": "read",
      "read_at": "2026-04-07T10:11:00.000000Z",
      "created_at": "2026-04-07T10:09:00.000000Z",
      "updated_at": "2026-04-07T10:11:00.000000Z"
    }
  ],
  "meta": {
    "path": "/api/chat/conversations/10/messages",
    "per_page": 50,
    "next_cursor": "eyJpZCI6MTAwfQ",
    "prev_cursor": null
  }
}
```

**Frontend Usage Notes:**

- Messages are returned in **descending** order (newest first).
- Uses cursor pagination for efficient loading of large histories.
- `status` is either `"sent"` or `"read"`.
- Soft-deleted messages are automatically excluded.
- `sender` contains the user who sent the message.

---

### 6. Send Message

Creates a new message in a conversation.

```
POST /api/chat/conversations/{conversationId}/messages
```

**Request Body:**

```json
{
  "body": "Hello there!"
}
```

**Validation Rules:**

| Field  | Rules                           |
|--------|---------------------------------|
| `body` | required, string, max 5000 chars |

- Body is automatically trimmed of leading/trailing whitespace.

**Response (201 Created):**

```json
{
  "data": {
    "id": 102,
    "conversation_id": 10,
    "sender_id": 5,
    "sender": {
      "id": 5,
      "name": "Luka",
      "email": "luka@example.com",
      "branch_id": null,
      "roles": [],
      "permissions": []
    },
    "body": "Hello there!",
    "status": "sent",
    "read_at": null,
    "created_at": "2026-04-07T10:15:00.000000Z",
    "updated_at": "2026-04-07T10:15:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Condition                          |
|--------|------------------------------------|
| 403    | User is not a participant          |
| 422    | Validation error (empty body, etc) |

---

### 7. Mark Messages as Read

Marks all unread incoming messages in a conversation as read for the authenticated user.

```
POST /api/chat/conversations/{conversationId}/read
```

**Request Body:** None required.

**Response (200 OK):**

```json
{
  "conversation_id": 10,
  "updated_count": 5,
  "read_at": "2026-04-07T10:20:00.000000Z"
}
```

**Frontend Usage Notes:**

- Call this endpoint when the user opens/views a conversation.
- Only marks messages sent by **other users** as read (not the user's own messages).
- `updated_count` shows how many messages were marked as read.

---

### 8. Get Total Unread Count

Returns the total number of unread incoming messages across all conversations.

```
GET /api/chat/unread-count
```

**Response (200 OK):**

```json
{
  "unread_count": 8
}
```

**Frontend Usage Notes:**

- Use this to display a badge/counter on the chat icon in your navigation.
- Only counts messages sent by other users that haven't been read yet.
- Poll this endpoint periodically (e.g., every 30 seconds) or after receiving a WebSocket event.

---

### 9. Delete Message (Optional)

Soft deletes a message. Only the sender can delete their own messages.

```
DELETE /api/chat/messages/{messageId}
```

**Response (200 OK):**

```json
{
  "message": "Message deleted successfully"
}
```

**Error Responses:**

| Status | Condition                              |
|--------|----------------------------------------|
| 403    | User is not the sender of the message  |
| 404    | Message not found                      |

---

## Authentication

All endpoints use JWT authentication. Include the token in requests:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

Get a token via `POST /api/auth/login` with `{ "email": "...", "password": "..." }`.

---

## Real-Time Broadcasting (Pusher)

The chat module broadcasts events in real-time using Pusher. When a message is sent or messages are marked as read, all participants in the conversation receive instant updates via WebSocket.

### Configuration

The backend uses **Pusher** as the broadcast driver:

| Setting              | Value                            |
|----------------------|----------------------------------|
| Broadcast Driver     | `pusher`                         |
| Pusher Cluster       | `eu`                             |
| Auth Endpoint        | `POST /api/broadcasting/auth`    |
| Auth Guard           | `auth:api` (JWT)                 |

### Channel Authorization

All chat channels are **private channels**. The frontend must authenticate before subscribing.

The broadcasting auth endpoint is `POST /api/broadcasting/auth` and requires the same JWT `Authorization: Bearer <token>` header used by all other API endpoints.

### Channels

| Channel                            | Type    | Description                                     |
|------------------------------------|---------|-------------------------------------------------|
| `private-conversation.{id}`        | Private | Events for a specific conversation              |

Only participants of a conversation can subscribe to its channel. The backend enforces this automatically during channel authentication.

### Events

#### `message.sent`

Fired when a new message is sent in a conversation.

**Channel:** `private-conversation.{conversationId}`

**Payload:**

```json
{
  "message": {
    "id": 102,
    "conversation_id": 10,
    "sender_id": 5,
    "sender": {
      "id": 5,
      "name": "Luka",
      "email": "luka@example.com",
      "branch_id": null,
      "roles": [],
      "permissions": []
    },
    "body": "Hello there!",
    "status": "sent",
    "read_at": null,
    "created_at": "2026-04-07T10:15:00.000000Z",
    "updated_at": "2026-04-07T10:15:00.000000Z"
  }
}
```

#### `messages.read`

Fired when a participant marks messages as read.

**Channel:** `private-conversation.{conversationId}`

**Payload:**

```json
{
  "conversation_id": 10,
  "read_by_user_id": 25,
  "updated_count": 5,
  "read_at": "2026-04-07T10:20:00.000000Z"
}
```

### Frontend Setup (React + Pusher)

#### 1. Install Dependencies

```bash
npm install pusher-js laravel-echo
```

#### 2. Configure Laravel Echo

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: '6f06146371c2c866d0da',
  cluster: 'eu',
  forceTLS: true,
  authEndpoint: '/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  },
});
```

> Update `token` dynamically when the JWT token refreshes.

#### 3. Subscribe to Conversation Events

```typescript
// Subscribe to a conversation channel
const subscribeToConversation = (conversationId: number) => {
  echo
    .private(`conversation.${conversationId}`)
    .listen('.message.sent', (data: { message: ChatMessage }) => {
      // New message received — append to message list
      console.log('New message:', data.message);
    })
    .listen('.messages.read', (data: MessagesReadEvent) => {
      // Messages were read by the other participant
      // Update message statuses and unread count in UI
      console.log('Messages read:', data);
    });
};

// Unsubscribe when leaving conversation view
const unsubscribeFromConversation = (conversationId: number) => {
  echo.leave(`conversation.${conversationId}`);
};
```

> **Important:** Use `.listen('.message.sent', ...)` with a leading dot (`.`) before the event name. This tells Laravel Echo to use the custom broadcast name instead of the fully-qualified class name.

#### 4. Complete Chat Hook Example

```typescript
import { useEffect, useCallback, useState, useRef } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

interface UseChatOptions {
  conversationId: number;
  token: string;
  onNewMessage: (message: ChatMessage) => void;
  onMessagesRead: (data: MessagesReadEvent) => void;
}

interface MessagesReadEvent {
  conversation_id: number;
  read_by_user_id: number;
  updated_count: number;
  read_at: string;
}

export function useChat({ conversationId, token, onNewMessage, onMessagesRead }: UseChatOptions) {
  const echoRef = useRef<Echo | null>(null);

  useEffect(() => {
    window.Pusher = Pusher;

    const echoInstance = new Echo({
      broadcaster: 'pusher',
      key: '6f06146371c2c866d0da',
      cluster: 'eu',
      forceTLS: true,
      authEndpoint: '/api/broadcasting/auth',
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    });

    echoRef.current = echoInstance;

    echoInstance
      .private(`conversation.${conversationId}`)
      .listen('.message.sent', (data: { message: ChatMessage }) => {
        onNewMessage(data.message);
      })
      .listen('.messages.read', (data: MessagesReadEvent) => {
        onMessagesRead(data);
      });

    return () => {
      echoInstance.leave(`conversation.${conversationId}`);
      echoInstance.disconnect();
    };
  }, [conversationId, token]);
}
```

#### 5. Global Unread Count Listener

To update a global unread badge, subscribe to all active conversations:

```typescript
export function useChatNotifications(
  conversationIds: number[],
  token: string,
  onAnyNewMessage: (message: ChatMessage) => void,
) {
  useEffect(() => {
    window.Pusher = Pusher;

    const echoInstance = new Echo({
      broadcaster: 'pusher',
      key: '6f06146371c2c866d0da',
      cluster: 'eu',
      forceTLS: true,
      authEndpoint: '/api/broadcasting/auth',
      auth: {
        headers: { Authorization: `Bearer ${token}` },
      },
    });

    conversationIds.forEach((id) => {
      echoInstance
        .private(`conversation.${id}`)
        .listen('.message.sent', (data: { message: ChatMessage }) => {
          onAnyNewMessage(data.message);
        });
    });

    return () => {
      conversationIds.forEach((id) => echoInstance.leave(`conversation.${id}`));
      echoInstance.disconnect();
    };
  }, [conversationIds, token]);
}
```

### Event Flow Summary

```
User A sends message → Backend saves to DB
                      → Backend broadcasts "message.sent" to conversation channel
                      → User B receives event via WebSocket (instant)
                      → User B's UI appends the new message

User B opens conversation → Frontend calls POST /api/chat/conversations/{id}/read
                           → Backend marks messages as read
                           → Backend broadcasts "messages.read" to conversation channel
                           → User A receives event via WebSocket
                           → User A's UI updates message statuses to "read"
```

---

## React Integration Example

```typescript
const API_BASE = '/api';

const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${token}`,
};

// List users to chat with
const getUsers = async (search?: string, page = 1) => {
  const params = new URLSearchParams({ per_page: '20', page: String(page) });
  if (search) params.set('search', search);
  const res = await fetch(`${API_BASE}/chat/users?${params}`, { headers });
  return res.json();
};

// Create or get direct conversation
const startChat = async (userId: number) => {
  const res = await fetch(`${API_BASE}/chat/conversations/direct`, {
    method: 'POST',
    headers,
    body: JSON.stringify({ user_id: userId }),
  });
  return res.json();
};

// List conversations
const getConversations = async (cursor?: string) => {
  const params = new URLSearchParams({ per_page: '20' });
  if (cursor) params.set('cursor', cursor);
  const res = await fetch(`${API_BASE}/chat/conversations?${params}`, { headers });
  return res.json();
};

// Get messages
const getMessages = async (conversationId: number, cursor?: string) => {
  const params = new URLSearchParams({ per_page: '50' });
  if (cursor) params.set('cursor', cursor);
  const res = await fetch(
    `${API_BASE}/chat/conversations/${conversationId}/messages?${params}`,
    { headers }
  );
  return res.json();
};

// Send message
const sendMessage = async (conversationId: number, body: string) => {
  const res = await fetch(
    `${API_BASE}/chat/conversations/${conversationId}/messages`,
    {
      method: 'POST',
      headers,
      body: JSON.stringify({ body }),
    }
  );
  return res.json();
};

// Mark conversation as read
const markAsRead = async (conversationId: number) => {
  const res = await fetch(
    `${API_BASE}/chat/conversations/${conversationId}/read`,
    { method: 'POST', headers }
  );
  return res.json();
};

// Get unread count
const getUnreadCount = async () => {
  const res = await fetch(`${API_BASE}/chat/unread-count`, { headers });
  return res.json();
};

// Delete message
const deleteMessage = async (messageId: number) => {
  const res = await fetch(`${API_BASE}/chat/messages/${messageId}`, {
    method: 'DELETE',
    headers,
  });
  return res.json();
};
```

---

## TypeScript Interfaces

```typescript
interface Participant {
  id: number;
  name: string;
}

interface Sender {
  id: number;
  name: string;
  email: string;
  branch_id: number | null;
  roles: string[];
  permissions: string[];
}

interface ChatMessage {
  id: number;
  conversation_id: number;
  sender_id: number;
  sender: Sender | null;
  body: string;
  status: 'sent' | 'read';
  read_at: string | null;
  created_at: string;
  updated_at: string;
}

interface Conversation {
  id: number;
  type: 'direct';
  participants: Participant[];
  last_message: ChatMessage | null;
  unread_count: number;
  last_message_at: string | null;
  created_at: string;
  updated_at: string;
}

interface CursorPaginationMeta {
  path: string;
  per_page: number;
  next_cursor: string | null;
  prev_cursor: string | null;
}

interface PaginatedResponse<T> {
  data: T[];
  meta: CursorPaginationMeta;
}

interface UnreadCountResponse {
  unread_count: number;
}

interface MarkAsReadResponse {
  conversation_id: number;
  updated_count: number;
  read_at: string;
}

// Real-time event payloads
interface MessageSentEvent {
  message: ChatMessage;
}

interface MessagesReadEvent {
  conversation_id: number;
  read_by_user_id: number;
  updated_count: number;
  read_at: string;
}
```

---

## Response Codes Summary

| Code | Meaning           | When                                         |
|------|-------------------|----------------------------------------------|
| 200  | OK                | Successful GET/POST (existing resource)       |
| 201  | Created           | New conversation or message created           |
| 400  | Bad Request       | Malformed request                             |
| 401  | Unauthorized      | Missing or invalid JWT token                  |
| 403  | Forbidden         | User is not a participant of the conversation |
| 404  | Not Found         | Resource does not exist                       |
| 422  | Validation Error  | Invalid input data                            |

---

## Endpoint Summary

| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | `/api/chat/users`                                | List users for chat (searchable)   |
| POST   | `/api/chat/conversations/direct`                 | Create or get direct conversation  |
| GET    | `/api/chat/conversations`                        | List my conversations              |
| GET    | `/api/chat/conversations/{id}`                   | Get conversation details           |
| GET    | `/api/chat/conversations/{id}/messages`          | Get conversation messages          |
| POST   | `/api/chat/conversations/{id}/messages`          | Send message                       |
| POST   | `/api/chat/conversations/{id}/read`              | Mark messages as read              |
| GET    | `/api/chat/unread-count`                         | Get total unread count             |
| DELETE | `/api/chat/messages/{id}`                        | Delete message (soft delete)       |
