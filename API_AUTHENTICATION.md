# API Authentication Documentation

## Overview
This API uses JWT (JSON Web Token) authentication for secure access. All protected endpoints require a valid JWT token in the Authorization header.

## Base URL
```
/api
```

## Endpoints

### 1. Login
Authenticate a user and receive a JWT token.

**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
  "email": "admin@nikora.ge",
  "password": "password"
}
```

**Success Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Error Response (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 2. Get User Profile
Get the authenticated user's profile with roles and permissions.

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Recall Admin",
    "email": "admin@nikora.ge",
    "branch_id": 1,
    "roles": ["Recall Admin"],
    "permissions": [
      "view_any_user",
      "view_user",
      "create_user",
      "update_user",
      "delete_user",
      ...
    ]
  }
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

### 3. Get User Permissions
Get all permissions for the authenticated user (detailed format).

**Endpoint:** `GET /api/permissions`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": [
    {
      "name": "view_any_user",
      "guard_name": "web"
    },
    {
      "name": "view_user",
      "guard_name": "web"
    },
    {
      "name": "create_user",
      "guard_name": "web"
    }
  ]
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

### 4. Refresh Token
Refresh the JWT token to extend the session.

**Endpoint:** `POST /api/auth/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

### 5. Logout
Invalidate the current JWT token.

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Successfully logged out"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

## Available Roles

1. **Recall Admin** - Full system access
2. **Quality Manager** - Manage products, inventory, recalls, and audits
3. **Branch Manager** - Branch-scoped access to operations
4. **Warehouse Operator** - Inventory and recall operations
5. **Auditor** - Read-only access to audits and recalls

## Test Accounts

For development/testing purposes, the following accounts are available:

| Email | Password | Role |
|-------|----------|------|
| admin@nikora.ge | password | Recall Admin |
| quality@nikora.ge | password | Quality Manager |
| branch@nikora.ge | password | Branch Manager |
| warehouse@nikora.ge | password | Warehouse Operator |
| auditor@nikora.ge | password | Auditor |

## Usage Example

```bash
# 1. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nikora.ge","password":"password"}'

# 2. Use the token from response
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 3. Get user profile
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"

# 4. Get user permissions (detailed)
curl -X GET http://localhost:8000/api/permissions \
  -H "Authorization: Bearer $TOKEN"

# 5. Refresh token
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer $TOKEN"

# 6. Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer $TOKEN"
```

## Token Configuration

- **Token TTL:** 60 minutes (configurable in `config/jwt.php`)
- **Refresh TTL:** 20160 minutes (2 weeks)
- **Algorithm:** HS256

## Security Notes

1. Always use HTTPS in production
2. Store tokens securely (never in localStorage for sensitive apps)
3. Implement token refresh before expiry
4. Handle 401 responses by redirecting to login
5. Never expose JWT_SECRET in version control
