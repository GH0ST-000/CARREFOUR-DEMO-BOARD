# Action Log API

Full audit trail system that tracks all significant user and system activities across the CRM.

## Authentication

All endpoints require a valid JWT token via the `Authorization: Bearer <token>` header.

## Authorization

Only users with the following permissions can access action logs:

- `view_any_action_log` - required for listing and searching
- `view_action_log` - required for viewing a single entry

By default, only **Super Admin** and **Recall Admin** roles have these permissions.

---

## Endpoints

### 1. List Action Logs

```
GET /api/action-logs
```

Returns a paginated list of action logs sorted by newest first. Supports cursor pagination.

#### Query Parameters

| Parameter     | Type     | Required | Description                                      |
|---------------|----------|----------|--------------------------------------------------|
| `per_page`    | integer  | No       | Items per page (1-100, default: 25)              |
| `user_id`     | integer  | No       | Filter by user who performed the action          |
| `action_type` | string   | No       | Filter by action type (see values below)         |
| `entity_type` | string   | No       | Filter by entity type (see values below)         |
| `module`      | string   | No       | Filter by module (see values below)              |
| `date_from`   | string   | No       | Filter logs from this date (`YYYY-MM-DD`)        |
| `date_to`     | string   | No       | Filter logs until this date (`YYYY-MM-DD`)       |

All filters can be combined freely.

#### Example Request

```
GET /api/action-logs?module=products&action_type=create&per_page=10
```

#### Example Response

```json
{
  "data": [
    {
      "id": 42,
      "user_id": 1,
      "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "branch_id": null,
        "roles": ["Super Admin"],
        "permissions": ["..."]
      },
      "action_type": "create",
      "entity_type": "product",
      "entity_id": 15,
      "module": "products",
      "description": "Product #15 created",
      "metadata": null,
      "created_at": "2026-04-07T10:30:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/action-logs?per_page=10",
    "last": null,
    "prev": null,
    "next": "http://localhost/api/action-logs?cursor=eyJ..."
  },
  "meta": {
    "path": "http://localhost/api/action-logs",
    "per_page": 10,
    "next_cursor": "eyJ...",
    "prev_cursor": null
  }
}
```

---

### 2. View Single Action Log

```
GET /api/action-logs/{id}
```

Returns a single action log entry with its user relationship.

#### Path Parameters

| Parameter | Type    | Description           |
|-----------|---------|-----------------------|
| `id`      | integer | The action log ID     |

#### Example Request

```
GET /api/action-logs/42
```

#### Example Response

```json
{
  "data": {
    "id": 42,
    "user_id": 1,
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "branch_id": null,
      "roles": ["Super Admin"],
      "permissions": ["..."]
    },
    "action_type": "status_change",
    "entity_type": "ticket",
    "entity_id": 7,
    "module": "tickets",
    "description": "Ticket #7 status changed from open to closed",
    "metadata": {
      "old_status": "open",
      "new_status": "closed"
    },
    "created_at": "2026-04-07T11:00:00.000000Z"
  }
}
```

---

### 3. Search Action Logs

```
GET /api/action-logs/search
```

Full-text search across action log descriptions and fields using Laravel Scout. Returns standard page-based pagination (not cursor).

#### Query Parameters

| Parameter     | Type    | Required | Description                              |
|---------------|---------|----------|------------------------------------------|
| `q`           | string  | No       | Search query (searches description, action_type, entity_type, module) |
| `per_page`    | integer | No       | Items per page (1-100, default: 25)      |
| `user_id`     | integer | No       | Filter by user ID                        |
| `action_type` | string  | No       | Filter by action type                    |
| `module`      | string  | No       | Filter by module                         |
| `page`        | integer | No       | Page number (default: 1)                 |

#### Example Request

```
GET /api/action-logs/search?q=Product+created&module=products&per_page=10
```

#### Example Response

```json
{
  "data": [
    {
      "id": 42,
      "user_id": 1,
      "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "action_type": "create",
      "entity_type": "product",
      "entity_id": 15,
      "module": "products",
      "description": "Product #15 created",
      "metadata": null,
      "created_at": "2026-04-07T10:30:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/action-logs/search?page=1",
    "last": "http://localhost/api/action-logs/search?page=3",
    "prev": null,
    "next": "http://localhost/api/action-logs/search?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 10,
    "to": 10,
    "total": 25
  }
}
```

---

## Response Object Schema

Each action log entry has the following structure:

| Field         | Type            | Description                                          |
|---------------|-----------------|------------------------------------------------------|
| `id`          | integer         | Unique log entry ID                                  |
| `user_id`     | integer \| null | ID of the user who performed the action (`null` for system actions) |
| `user`        | object \| null  | Nested user object (when loaded)                     |
| `action_type` | string          | Type of action performed                             |
| `entity_type` | string          | Type of entity affected                              |
| `entity_id`   | integer \| null | ID of the affected entity                            |
| `module`      | string          | Functional module name                               |
| `description` | string          | Human-readable explanation of the action             |
| `metadata`    | object \| null  | Additional context (changes, old/new values, etc.)   |
| `created_at`  | string          | ISO 8601 timestamp                                   |

---

## Enum Values

### `action_type`

| Value           | Description                                    |
|-----------------|------------------------------------------------|
| `create`        | A new entity was created                       |
| `update`        | An existing entity was modified                |
| `delete`        | An entity was deleted                          |
| `login`         | A user logged in                               |
| `logout`        | A user logged out                              |
| `status_change` | An entity's status was changed (e.g. ticket close/reopen) |

### `entity_type`

| Value                | Description          |
|----------------------|----------------------|
| `manufacturer`       | Manufacturer         |
| `product`            | Product              |
| `batch`              | Batch                |
| `warehouse_location` | Warehouse Location   |
| `receiving`          | Receiving            |
| `ticket`             | Ticket               |
| `ticket_message`     | Ticket Message       |
| `user`               | User                 |

### `module`

| Value                 | Description          |
|-----------------------|----------------------|
| `manufacturers`       | Manufacturers        |
| `products`            | Products             |
| `batches`             | Batches              |
| `warehouse-locations` | Warehouse Locations  |
| `receivings`          | Receivings           |
| `tickets`             | Tickets              |
| `users`               | Users                |
| `dashboard`           | Dashboard            |

---

## Metadata Examples

The `metadata` field varies by action type. Here are common shapes:

### Update action (field changes)

```json
{
  "changes": {
    "status": "in_progress",
    "priority": "high"
  }
}
```

### Status change action

```json
{
  "old_status": "open",
  "new_status": "closed"
}
```

### Role assignment

```json
{
  "role": "Quality Manager"
}
```

### No metadata

```json
null
```

---

## Error Responses

### 401 Unauthorized

No valid JWT token provided.

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

User does not have `view_any_action_log` or `view_action_log` permission.

```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found

Action log entry with the given ID does not exist.

```json
{
  "message": "No query results for model [App\\Models\\ActionLog] 999"
}
```

---

## What Gets Logged Automatically

The system automatically creates log entries for:

| Action                     | action_type     | module              |
|----------------------------|-----------------|---------------------|
| Create manufacturer        | `create`        | `manufacturers`     |
| Update manufacturer        | `update`        | `manufacturers`     |
| Delete manufacturer        | `delete`        | `manufacturers`     |
| Create product             | `create`        | `products`          |
| Update product             | `update`        | `products`          |
| Delete product             | `delete`        | `products`          |
| Create batch               | `create`        | `batches`           |
| Update batch               | `update`        | `batches`           |
| Delete batch               | `delete`        | `batches`           |
| Create warehouse location  | `create`        | `warehouse-locations` |
| Update warehouse location  | `update`        | `warehouse-locations` |
| Delete warehouse location  | `delete`        | `warehouse-locations` |
| Create receiving           | `create`        | `receivings`        |
| Update receiving           | `update`        | `receivings`        |
| Delete receiving           | `delete`        | `receivings`        |
| Create ticket              | `create`        | `tickets`           |
| Update ticket              | `update`        | `tickets`           |
| Delete ticket              | `delete`        | `tickets`           |
| Close ticket               | `status_change` | `tickets`           |
| Reopen ticket              | `status_change` | `tickets`           |
| Create ticket message      | `create`        | `tickets`           |
| Assign role to user        | `update`        | `users`             |
| Remove role from user      | `update`        | `users`             |
| User login                 | `login`         | `users`             |
| User logout                | `logout`        | `users`             |

---

## Frontend Integration Tips

### Fetching logs for a specific entity

To get all logs for a specific product (e.g. product #15):

```
GET /api/action-logs?entity_type=product&module=products
```

Then filter client-side by `entity_id === 15`, or use search:

```
GET /api/action-logs/search?q=Product+%2315
```

### Building a filter UI

Recommended filter controls:

1. **Module dropdown** - use the `module` values from the enum table above
2. **Action type dropdown** - use the `action_type` values
3. **Date range picker** - sends `date_from` and `date_to`
4. **User selector** - sends `user_id`
5. **Search input** - uses the `/search` endpoint with `q` parameter

### Pagination

- The **list** endpoint (`/api/action-logs`) uses **cursor pagination** - use `next_cursor` / `prev_cursor` from `meta` for navigation
- The **search** endpoint (`/api/action-logs/search`) uses **page-based pagination** - use `page` parameter and `meta.last_page` for navigation

### Displaying metadata

The `metadata` field is a free-form JSON object. Render it as a key-value table or collapsible JSON view. It will be `null` when there is no extra context to show.
