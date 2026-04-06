# User Management Guide for Recall Admins

Complete guide for creating users and managing roles in NIKORA Supply Chain Management System.

---

## Table of Contents

1. [Overview](#overview)
2. [User Roles](#user-roles)
3. [Creating Users (Admin Panel)](#creating-users-admin-panel)
4. [Assigning Roles (Admin Panel)](#assigning-roles-admin-panel)
5. [Managing Roles via API](#managing-roles-via-api)
6. [User Permissions Reference](#user-permissions-reference)
7. [Common Scenarios](#common-scenarios)

---

## Overview

As a **Recall Admin**, you have full system access and can:
- Create, view, edit, and delete users
- Assign and remove roles
- Manage all system resources
- View all branches and their users

### Available Management Methods

| Method | Use Case | Access |
|--------|----------|--------|
| **Filament Admin Panel** | Creating new users, bulk role management | Web interface at `/admin` |
| **API** | Programmatic role assignment/removal | REST API endpoints |

---

## User Roles

The system has 5 predefined roles with different permission levels:

### 1. Recall Admin
**Full system access** - All permissions for all resources

**Use Case:** System administrators, IT managers
- Create/manage users and assign roles
- Full CRUD on all resources
- Access to all branches
- Approve recalls and audits

### 2. Quality Manager
**Quality control and product oversight**

**Permissions:**
- View users and branches
- Full CRUD on products, inventory, recalls, audits
- Approve recalls and audits
- View all data across branches

**Use Case:** Quality control managers, product safety officers

### 3. Branch Manager
**Branch-level management**

**Permissions:**
- View/manage own branch users
- View/manage own branch products and inventory
- Create recalls for own branch
- View own branch audits

**Use Case:** Store managers, branch supervisors

### 4. Warehouse Operator
**Inventory operations**

**Permissions:**
- View/create/update inventory
- View products
- Create and view recalls

**Use Case:** Warehouse staff, inventory clerks

### 5. Auditor
**Read-only access for compliance**

**Permissions:**
- View audits (all branches)
- View recalls (all branches)
- View inventory and products (all branches)
- Create/update audits

**Use Case:** Compliance officers, external auditors

---

## Creating Users (Admin Panel)

### Step 1: Access User Management

1. Login to the admin panel at `http://your-domain/admin`
2. Navigate to **User Management → Users** in the sidebar
3. Click the **Create** button

### Step 2: Fill User Information

Complete the following required fields:

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| **Name** | ✅ Yes | User's full name | `John Smith` |
| **Email** | ✅ Yes | Unique email address (used for login) | `john.smith@nikora.ge` |
| **Branch** | ❌ No | Associate user with a branch | `Tbilisi Central` |
| **Roles** | ✅ Yes | One or more roles | `Quality Manager` |
| **Password** | ✅ Yes | Secure password (required on creation) | `SecurePass123!` |
| **Email Verified At** | ❌ No | Timestamp of email verification | Auto-set on verification |

### Step 3: Assign Role(s)

Users can have multiple roles:

1. In the **Roles** field, click to open the dropdown
2. Select one or more roles from the list
3. Selected roles appear as tags

**Example Combinations:**
- Quality Manager + Auditor (for senior quality staff)
- Branch Manager + Warehouse Operator (for small branches)
- Recall Admin only (for system administrators)

### Step 4: Save User

1. Click the **Create** button at the bottom
2. User account is created immediately
3. User can login with their email and password

---

## Assigning Roles (Admin Panel)

### Method 1: During User Creation
Assign roles when creating the user (see Step 3 above)

### Method 2: Editing Existing User

1. Navigate to **User Management → Users**
2. Find the user in the table
3. Click the user's name or the edit icon
4. Modify the **Roles** field:
   - Click the field to add roles
   - Click the 'X' on a tag to remove a role
5. Click **Save** to apply changes

### Method 3: Bulk Role Assignment

For assigning the same role to multiple users:

1. Navigate to **User Management → Users**
2. Select multiple users using checkboxes
3. Click **Bulk Actions** in the table toolbar
4. Select **Assign Role**
5. Choose the role to assign
6. Confirm the action

---

## Managing Roles via API

For programmatic or automated role management, use the REST API.

### Authentication

All API requests require JWT authentication:

```bash
# Login to get token
TOKEN=$(curl -s -X POST http://your-domain/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nikora.ge","password":"your-password"}' \
  | jq -r '.access_token')
```

### 1. List Available Roles

Get all roles with their permissions:

```bash
curl -X GET http://your-domain/api/roles \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Recall Admin",
      "guard_name": "web",
      "permissions": [
        {
          "id": 1,
          "name": "view_any_user",
          "guard_name": "web"
        },
        ...
      ]
    },
    ...
  ]
}
```

### 2. View User's Current Roles

Get user information with their roles:

```bash
curl -X GET http://your-domain/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "data": {
    "id": 5,
    "name": "John Smith",
    "email": "john.smith@nikora.ge",
    "branch_id": 2,
    "roles": [
      "Quality Manager",
      "Auditor"
    ]
  }
}
```

### 3. Assign Role to User

Add a role to a user (requires user update permission):

```bash
curl -X POST http://your-domain/api/users/{user_id}/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "role": "Quality Manager"
  }'
```

**Parameters:**
- `{user_id}`: The ID of the user to assign the role to
- `role`: The exact name of the role (case-sensitive)

**Response:**
```json
{
  "data": {
    "id": 5,
    "name": "John Smith",
    "email": "john.smith@nikora.ge",
    "branch_id": 2,
    "roles": [
      "Warehouse Operator",
      "Quality Manager"
    ]
  }
}
```

**Valid Role Names:**
- `Recall Admin`
- `Quality Manager`
- `Branch Manager`
- `Warehouse Operator`
- `Auditor`

### 4. Remove Role from User

Remove a specific role from a user:

```bash
curl -X DELETE http://your-domain/api/users/{user_id}/roles/{role_name} \
  -H "Authorization: Bearer $TOKEN"
```

**Parameters:**
- `{user_id}`: The ID of the user
- `{role_name}`: The URL-encoded role name (e.g., `Quality%20Manager`)

**Response:**
```json
{
  "message": "Role removed successfully"
}
```

### 5. View Specific Role Details

Get detailed information about a role:

```bash
curl -X GET http://your-domain/api/roles/{role_id} \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "data": {
    "id": 2,
    "name": "Quality Manager",
    "guard_name": "web",
    "permissions": [
      {
        "id": 1,
        "name": "view_any_user"
      },
      {
        "id": 2,
        "name": "view_user"
      },
      ...
    ]
  }
}
```

---

## User Permissions Reference

### Complete Permission Matrix

| Permission | Recall Admin | Quality Manager | Branch Manager | Warehouse Operator | Auditor |
|------------|--------------|-----------------|----------------|-------------------|---------|
| **Users** |
| View any user | ✅ | ✅ | ❌ | ❌ | ❌ |
| View own branch user | ✅ | ❌ | ✅ | ❌ | ❌ |
| Create user | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update user | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete user | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Branches** |
| View any branch | ✅ | ✅ | ❌ | ❌ | ❌ |
| View own branch | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Products** |
| Full CRUD | ✅ | ✅ | ❌ | ❌ | ❌ |
| View only | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Inventory/Batches** |
| Full CRUD | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create/Update only | ❌ | ❌ | ✅ | ✅ | ❌ |
| View only | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Recalls** |
| Full CRUD + Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create/View only | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Audits** |
| Full CRUD + Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create/Update/View | ❌ | ❌ | ❌ | ❌ | ✅ |
| View only | ❌ | ❌ | ✅ | ❌ | ❌ |

### Permission Naming Convention

Permissions follow the pattern: `{action}_{resource}`

**Actions:**
- `view_any` - View all records
- `view` - View specific record
- `view_own_branch` - View records from own branch only
- `create` - Create new records
- `update` - Modify existing records
- `delete` - Delete records
- `restore` - Restore soft-deleted records
- `force_delete` - Permanently delete records
- `approve` - Approve special actions (recalls, audits)

**Resources:**
- `user`, `role`, `branch`, `product`, `batch`
- `warehouse_location`, `receiving`, `inventory`
- `recall`, `audit`, `manufacturer`

---

## Common Scenarios

### Scenario 1: Onboarding a New Quality Manager

**Goal:** Create a quality manager for the Tbilisi branch who can manage products and recalls.

**Admin Panel Method:**

1. Go to **User Management → Users → Create**
2. Fill in details:
   - Name: `Ana Beridze`
   - Email: `ana.beridze@nikora.ge`
   - Branch: `Tbilisi Central`
   - Roles: Select `Quality Manager`
   - Password: (secure password)
3. Click **Create**
4. Ana can now login and manage products/recalls for all branches

**API Method:**

```bash
# First, create the user in admin panel (or via separate user creation API if available)
# Then assign role via API:

curl -X POST http://your-domain/api/users/12/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role": "Quality Manager"}'
```

### Scenario 2: Converting Warehouse Operator to Branch Manager

**Goal:** Promote a warehouse operator to branch manager for their location.

**Admin Panel Method:**

1. Navigate to **User Management → Users**
2. Find the user and click to edit
3. In the **Roles** field:
   - Remove `Warehouse Operator`
   - Add `Branch Manager`
4. Click **Save**

**API Method:**

```bash
# Remove old role
curl -X DELETE http://your-domain/api/users/8/roles/Warehouse%20Operator \
  -H "Authorization: Bearer $TOKEN"

# Add new role
curl -X POST http://your-domain/api/users/8/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role": "Branch Manager"}'
```

### Scenario 3: Creating Multi-Role User

**Goal:** Create a user who is both a Quality Manager and an Auditor (for senior quality staff).

**Admin Panel Method:**

1. Go to **User Management → Users → Create**
2. Fill in user details
3. In **Roles** field, select both:
   - `Quality Manager`
   - `Auditor`
4. Click **Create**

**API Method:**

```bash
# Create user first (via admin panel), then assign roles

# Assign first role
curl -X POST http://your-domain/api/users/15/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role": "Quality Manager"}'

# Assign second role
curl -X POST http://your-domain/api/users/15/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role": "Auditor"}'
```

### Scenario 4: Bulk Onboarding Branch Staff

**Goal:** Create multiple warehouse operators for a new branch opening.

**Best Approach:** Use Admin Panel for bulk creation

1. Create users one by one through **User Management → Users → Create**
2. For each user:
   - Name: Staff member's name
   - Email: Their unique email
   - Branch: Select the new branch
   - Roles: `Warehouse Operator`
   - Password: Initial secure password (users should change on first login)

**Alternative:** For large-scale onboarding (20+ users), consider:
1. Preparing a CSV/Excel with user data
2. Using database import tools
3. Then bulk-assign roles via API script

### Scenario 5: Temporary Auditor Access

**Goal:** Grant temporary auditor access to an external compliance consultant.

**Admin Panel Method:**

1. Create user with **Auditor** role
2. Set a secure temporary password
3. Share credentials with consultant
4. After audit completion:
   - Edit user and remove all roles, OR
   - Delete the user account

**Security Best Practice:** Always remove/deactivate temporary accounts after the access period ends.

---

## Error Handling

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| `Unauthorized` (401) | Invalid or expired token | Re-authenticate to get a new token |
| `Forbidden` (403) | Insufficient permissions | Ensure you have `update_user` permission |
| `Validation failed` (422) | Invalid role name | Check exact role name spelling (case-sensitive) |
| `User not found` (404) | Invalid user ID | Verify user ID exists |
| `Role not found` | Invalid role name in API call | Use exact role names from the list |

### Validation Rules

**Creating Users:**
- Name: Required, max 255 characters
- Email: Required, unique, valid email format, max 255 characters
- Password: Required on creation, max 255 characters
- Roles: At least one role must be assigned

**Assigning Roles:**
- Role name must match exactly (case-sensitive)
- User must exist
- Requester must have permission to update users

---

## Security Best Practices

### For Recall Admins

1. **Strong Passwords**
   - Enforce minimum 12 characters
   - Mix of uppercase, lowercase, numbers, symbols
   - No common words or patterns

2. **Role Assignment**
   - Follow principle of least privilege
   - Only assign roles necessary for the user's job
   - Review permissions before assigning Recall Admin role

3. **Access Review**
   - Regularly audit user accounts (monthly)
   - Remove inactive accounts
   - Revoke access for terminated employees immediately

4. **Branch Isolation**
   - Assign branch-specific roles when possible
   - Use Branch Manager instead of Quality Manager for branch-only access

5. **Multi-Role Caution**
   - Limit multiple role assignments
   - Document reason when assigning multiple roles
   - More roles = more access = more risk

6. **API Security**
   - Keep JWT tokens secure
   - Use HTTPS in production
   - Implement token rotation
   - Log all role changes

---

## Troubleshooting

### User Cannot Login

1. Verify email address is correct
2. Check password was set during creation
3. Ensure user has at least one role assigned
4. Check if email verification is required

### User Has Wrong Permissions

1. Go to **User Management → Users**
2. Click on the user
3. Review assigned roles
4. Check role permissions via API: `GET /api/roles/{role_id}`
5. Adjust roles as needed

### Role Assignment Fails via API

1. Verify exact role name (case-sensitive):
   - ✅ `Quality Manager`
   - ❌ `quality manager`
   - ❌ `QualityManager`
2. Check JWT token is valid
3. Ensure you have `update_user` permission
4. Verify user ID exists

### Cannot See User Management Menu

**Cause:** Not logged in as Recall Admin or role doesn't have `view_any_user` permission.

**Solution:** Login with a Recall Admin account or contact an administrator.

---

## API Reference Summary

### Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/roles` | List all roles with permissions | ✅ |
| GET | `/api/roles/{id}` | View specific role details | ✅ |
| GET | `/api/permissions` | List all permissions | ✅ |
| POST | `/api/users/{user}/roles` | Assign role to user | ✅ + Update permission |
| DELETE | `/api/users/{user}/roles/{role}` | Remove role from user | ✅ + Update permission |
| GET | `/api/auth/me` | Get current user with roles | ✅ |

### Request Headers

All authenticated requests require:
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

### Rate Limiting

API endpoints are rate-limited:
- 60 requests per minute per user
- 429 status code when limit exceeded
- Wait for `Retry-After` header duration

---

## Additional Resources

- **Main Documentation:** [README.md](README.md)
- **API Quick Reference:** [API_QUICK_REFERENCE.md](API_QUICK_REFERENCE.md)
- **Authentication Guide:** [API_AUTHENTICATION.md](API_AUTHENTICATION.md)
- **Dashboard API:** [DASHBOARD_API.md](DASHBOARD_API.md)

---

## Support

For technical support or questions:
- **Email:** support@nikora.ge
- **Phone:** +995 XXX XXX XXX
- **Documentation:** See links above

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-04-06 | Initial user management documentation |
