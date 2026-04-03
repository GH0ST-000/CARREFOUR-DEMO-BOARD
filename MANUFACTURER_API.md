# Manufacturer API Documentation

## Overview

Complete RESTful API for managing manufacturers with role-based access control. Only **Recall Admin** users can access these endpoints.

---

## Architecture

### Design Patterns Used

1. **Action Pattern** - Business logic encapsulated in dedicated Action classes
2. **Repository Pattern** - Eloquent models as data access layer
3. **Form Request Validation** - Validation logic in dedicated Request classes
4. **API Resources** - Consistent response formatting
5. **Policy-based Authorization** - Permission checks via Laravel Policies

### File Structure

```
app/
├── Models/
│   └── Manufacturer.php
├── Actions/Manufacturer/
│   ├── CreateManufacturerAction.php
│   ├── UpdateManufacturerAction.php
│   └── DeleteManufacturerAction.php
├── Http/
│   ├── Controllers/Api/
│   │   └── ManufacturerController.php
│   ├── Requests/Api/
│   │   ├── CreateManufacturerRequest.php
│   │   └── UpdateManufacturerRequest.php
│   └── Resources/
│       └── ManufacturerResource.php
├── Policies/
│   └── ManufacturerPolicy.php
database/
├── migrations/
│   └── 2026_04_03_093400_create_manufacturers_table.php
└── factories/
    └── ManufacturerFactory.php
```

---

## Database Schema

```sql
CREATE TABLE manufacturers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    full_name VARCHAR(255) NOT NULL,
    short_name VARCHAR(255) NULL,
    legal_form VARCHAR(255) NOT NULL,
    identification_number VARCHAR(255) UNIQUE NOT NULL,
    
    -- Contact Information
    legal_address VARCHAR(500) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    
    -- Geography
    country VARCHAR(255) NOT NULL,
    region VARCHAR(255) NOT NULL,
    city VARCHAR(255) NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    INDEX idx_is_active (is_active),
    INDEX idx_country (country),
    INDEX idx_country_region (country, region)
);
```

---

## API Endpoints

### Authentication Required

All endpoints require JWT authentication:
```
Authorization: Bearer {token}
```

### List Manufacturers

**GET** `/api/manufacturers`

Returns paginated list of manufacturers using cursor pagination.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "full_name": "ABC Manufacturing LLC",
      "short_name": "ABC Mfg",
      "legal_form": "Limited Liability Company",
      "identification_number": "123456789",
      "legal_address": "123 Industrial St, City",
      "phone": "+995-555-123456",
      "email": "contact@abc.ge",
      "country": "Georgia",
      "region": "Tbilisi",
      "city": "Tbilisi",
      "is_active": true,
      "created_at": "2026-04-03T12:00:00.000000Z",
      "updated_at": "2026-04-03T12:00:00.000000Z"
    }
  ],
  "meta": {
    "next_cursor": "...",
    "prev_cursor": "..."
  },
  "links": {
    "next": "http://api/manufacturers?cursor=...",
    "prev": null
  }
}
```

**Features:**
- Cursor pagination (25 items per page)
- Ordered by `full_name` then `id`
- Optimized for large datasets

---

### Create Manufacturer

**POST** `/api/manufacturers`

**Request Body:**
```json
{
  "full_name": "Test Manufacturer LLC",
  "short_name": "TestMfg",
  "legal_form": "Limited Liability Company",
  "identification_number": "123456789",
  "legal_address": "123 Test Street, Test City",
  "phone": "+995-555-123456",
  "email": "contact@testmfg.ge",
  "country": "Georgia",
  "region": "Tbilisi",
  "city": "Tbilisi",
  "is_active": true
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `full_name` | string | Yes | max:255 |
| `short_name` | string | No | max:255, nullable |
| `legal_form` | string | Yes | max:255 |
| `identification_number` | string | Yes | max:255, unique |
| `legal_address` | string | Yes | max:500 |
| `phone` | string | Yes | max:50 |
| `email` | string | Yes | valid email, max:255 |
| `country` | string | Yes | max:255 |
| `region` | string | Yes | max:255 |
| `city` | string | No | max:255, nullable |
| `is_active` | boolean | No | default: true |

**Features:**
- Automatic string trimming
- Email validation
- Unique identification number check

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "full_name": "Test Manufacturer LLC",
    ...
  }
}
```

---

### View Manufacturer

**GET** `/api/manufacturers/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "full_name": "Test Manufacturer LLC",
    ...
  }
}
```

---

### Update Manufacturer

**PUT/PATCH** `/api/manufacturers/{id}`

Supports partial updates. Only send fields you want to change.

**Request Body Example:**
```json
{
  "full_name": "Updated Name",
  "is_active": false
}
```

**Validation Rules:**
- Same as create, but all fields are optional (`sometimes`)
- `identification_number` uniqueness ignores current record

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "full_name": "Updated Name",
    "is_active": false,
    ...
  }
}
```

---

### Delete Manufacturer

**DELETE** `/api/manufacturers/{id}`

**Response:** `200 OK`
```json
{
  "message": "Manufacturer deleted successfully"
}
```

---

## Authorization

### Permissions

The following permissions are automatically generated:
- `view_any_manufacturer` - List manufacturers
- `view_manufacturer` - View specific manufacturer
- `create_manufacturer` - Create new manufacturer
- `update_manufacturer` - Update manufacturer
- `delete_manufacturer` - Delete manufacturer
- `restore_manufacturer` - Restore deleted manufacturer
- `force_delete_manufacturer` - Permanently delete

### Role Access

**Recall Admin** - Full access to all manufacturer operations

Other roles (Quality Manager, Branch Manager, Warehouse Operator, Auditor) - **No access**

---

## Example Usage

### Complete Workflow

```bash
# 1. Login as Recall Admin
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nikora.ge",
    "password": "password123"
  }'

# Response: { "access_token": "...", ... }

# 2. Create Manufacturer
curl -X POST http://localhost/api/manufacturers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Georgian Foods LLC",
    "short_name": "GF",
    "legal_form": "Limited Liability Company",
    "identification_number": "404123456",
    "legal_address": "15 Rustaveli Ave, Tbilisi 0108",
    "phone": "+995-32-2-123456",
    "email": "info@georgianfoods.ge",
    "country": "Georgia",
    "region": "Tbilisi",
    "city": "Tbilisi"
  }'

# 3. List Manufacturers
curl -X GET http://localhost/api/manufacturers \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Update Manufacturer
curl -X PUT http://localhost/api/manufacturers/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+995-32-2-654321",
    "email": "contact@georgianfoods.ge"
  }'

# 5. Delete Manufacturer
curl -X DELETE http://localhost/api/manufacturers/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\Manufacturer] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The full name field is required. (and 3 more errors)",
  "errors": {
    "full_name": ["Full name is required."],
    "email": ["Email address must be valid."],
    "identification_number": ["This identification number is already registered."]
  }
}
```

---

## Code Examples

### Using Action Classes

```php
use App\Actions\Manufacturer\CreateManufacturerAction;
use App\Actions\Manufacturer\UpdateManufacturerAction;
use App\Actions\Manufacturer\DeleteManufacturerAction;
use App\Models\Manufacturer;

// Create
$data = [
    'full_name' => 'New Manufacturer',
    'legal_form' => 'LLC',
    'identification_number' => '123456',
    // ... other required fields
];
$manufacturer = app(CreateManufacturerAction::class)->execute($data);

// Update
$updateData = ['full_name' => 'Updated Name'];
$updated = app(UpdateManufacturerAction::class)->execute($manufacturer, $updateData);

// Delete
$result = app(DeleteManufacturerAction::class)->execute($manufacturer);
```

### Querying Manufacturers

```php
use App\Models\Manufacturer;

// Active manufacturers only
$active = Manufacturer::where('is_active', true)->get();

// By country
$georgian = Manufacturer::where('country', 'Georgia')->get();

// Search by name
$results = Manufacturer::where('full_name', 'like', '%LLC%')->get();

// With cursor pagination
$manufacturers = Manufacturer::query()
    ->orderBy('full_name')
    ->orderBy('id')
    ->cursorPaginate(25);
```

---

## Performance Optimizations

1. **Indexes** - Added on `is_active`, `country`, and compound `(country, region)`
2. **Cursor Pagination** - Efficient for large datasets, no offset calculations
3. **Deterministic Sorting** - Always ordered by `full_name` then `id` for consistency
4. **Trimmed Input** - All string fields automatically trimmed in Form Requests

---

## Testing

Run tests with:
```bash
composer test
```

**Test Coverage:**
- ✅ 153 tests passed
- ✅ 505 assertions
- ✅ 100% code coverage
- ✅ 100% type coverage

**Test Files:**
- `tests/Feature/Api/ManufacturerTest.php` - API endpoint tests
- `tests/Feature/Actions/CreateManufacturerActionTest.php`
- `tests/Feature/Actions/UpdateManufacturerActionTest.php`
- `tests/Feature/Actions/DeleteManufacturerActionTest.php`
- `tests/Feature/Models/ManufacturerTest.php`
- `tests/Feature/Policies/ManufacturerPolicyTest.php`

---

## Production Checklist

✅ Database migration with proper indexes  
✅ Model with type-safe properties  
✅ Action classes following single responsibility principle  
✅ Form Requests with validation and auto-trimming  
✅ API Resources for consistent responses  
✅ Policy-based authorization  
✅ Comprehensive test coverage (100%)  
✅ Type coverage (100% - PHPStan Level Max)  
✅ Cursor pagination for scalability  
✅ Unique constraints on identification_number  
✅ Email validation  
✅ Error handling  

---

## Notes

- All business logic is in Action classes for reusability
- Controllers are thin (only HTTP concerns)
- Policies enforce authorization at every endpoint
- Automatic string trimming prevents whitespace issues
- Nullable fields: `short_name`, `city`
- Default value: `is_active = true`
