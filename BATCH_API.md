# Batch/Lot Management API Documentation

## Overview

Complete RESTful API for managing product batches and lots with full traceability. Supports both local and imported batches with temperature monitoring, quantity tracking, and status management.

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
│   └── Batch.php
├── Actions/Batch/
│   ├── CreateBatchAction.php
│   ├── UpdateBatchAction.php
│   └── DeleteBatchAction.php
├── Http/
│   ├── Controllers/Api/
│   │   └── BatchController.php
│   ├── Requests/Api/
│   │   ├── CreateBatchRequest.php
│   │   └── UpdateBatchRequest.php
│   └── Resources/
│       └── BatchResource.php
├── Policies/
│   └── BatchPolicy.php
database/
├── migrations/
│   └── 2026_04_06_100001_create_batches_table.php
└── factories/
    └── BatchFactory.php
```

---

## Database Schema

```sql
CREATE TABLE batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Batch Identification
    batch_number VARCHAR(255) UNIQUE NOT NULL,
    import_declaration_number VARCHAR(255) NULL,
    local_production_number VARCHAR(255) NULL,
    
    -- Dates
    production_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    receiving_datetime DATETIME NULL,
    
    -- Quantity & Status
    quantity DECIMAL(10,2) NOT NULL,
    remaining_quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(255) NOT NULL,
    status ENUM(
        'pending',
        'received',
        'in_storage',
        'in_transit',
        'blocked',
        'recalled',
        'expired',
        'disposed'
    ) DEFAULT 'pending',
    
    -- Storage Information
    warehouse_location_id BIGINT UNSIGNED NULL,
    receiving_temperature DECIMAL(5,2) NULL,
    packaging_condition TEXT NULL,
    
    -- Relationships
    product_id BIGINT UNSIGNED NOT NULL,
    received_by_user_id BIGINT UNSIGNED NULL,
    
    -- Additional Information
    linked_documents JSON NULL,
    temperature_history JSON NULL,
    movement_history JSON NULL,
    notes TEXT NULL,
    
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_batch_number (batch_number),
    INDEX idx_status (status),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_product_id (product_id),
    INDEX idx_warehouse_location_id (warehouse_location_id),
    INDEX idx_product_status (product_id, status),
    INDEX idx_expiry_status (expiry_date, status)
);
```

---

## Pagination

The Batch API uses **cursor pagination** for efficient querying of large datasets.

### Query Parameters

| Parameter | Type | Default | Range | Description |
|-----------|------|---------|-------|-------------|
| `per_page` | integer | 25 | 1-100 | Number of items per page |
| `cursor` | string | null | - | Cursor token from previous response |

### Example Requests

```bash
# Get first page (default 25 items)
GET /api/batches
Authorization: Bearer {token}

# Custom page size (10 items)
GET /api/batches?per_page=10
Authorization: Bearer {token}

# Navigate to next page using cursor
GET /api/batches?per_page=10&cursor=eyJleHBpcnlfZGF0ZSI...
Authorization: Bearer {token}
```

### Response Structure

```json
{
  "data": [
    {
      "id": 1,
      "batch_number": "BATCH-001",
      "product": {
        "id": 1,
        "name": "Fresh Milk"
      }
    }
  ],
  "meta": {
    "path": "http://api/batches",
    "per_page": 25,
    "next_cursor": "eyJleHBpcnlfZGF0ZSI...",
    "prev_cursor": null
  },
  "links": {
    "first": "http://api/batches",
    "last": null,
    "prev": null,
    "next": "http://api/batches?cursor=eyJleHBpcnlfZGF0ZSI..."
  }
}
```

---

## API Endpoints

### Authentication Required

All endpoints require JWT authentication:
```
Authorization: Bearer {token}
```

### List Batches

**GET** `/api/batches`

Returns paginated list of batches using cursor pagination, ordered by expiry date (earliest first).

**Query Parameters:**
- `per_page` (optional): Number of items per page (default: 25, min: 1, max: 100)
- `cursor` (optional): Cursor for next/previous page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "batch_number": "BATCH-001",
      "import_declaration_number": null,
      "local_production_number": "LOC-123456",
      "production_date": "2026-01-01",
      "expiry_date": "2026-07-01",
      "receiving_datetime": "2026-01-05T10:30:00.000000Z",
      "quantity": 500.0,
      "remaining_quantity": 450.0,
      "unit": "kg",
      "status": "in_storage",
      "warehouse_location_id": 1,
      "warehouse_location": {
        "id": 1,
        "name": "Cold Storage A",
        "code": "CS-A-001",
        "type": "storage_unit"
      },
      "receiving_temperature": 2.5,
      "packaging_condition": "Good condition",
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "Fresh Milk",
        "sku": "SKU-MILK-001",
        "category": "Dairy"
      },
      "received_by_user_id": 1,
      "received_by": {
        "id": 1,
        "name": "John Doe",
        "email": "john@nikora.ge"
      },
      "linked_documents": ["invoice_001.pdf", "certificate_002.pdf"],
      "temperature_history": [],
      "movement_history": [],
      "notes": "Received in excellent condition",
      "created_at": "2026-01-05T10:30:00.000000Z",
      "updated_at": "2026-01-05T10:30:00.000000Z"
    }
  ]
}
```

---

### Create Batch

**POST** `/api/batches`

**Request Body:**
```json
{
  "batch_number": "BATCH-001",
  "local_production_number": "LOC-123456",
  "production_date": "2026-01-01",
  "expiry_date": "2026-07-01",
  "quantity": 500,
  "unit": "kg",
  "product_id": 1,
  "receiving_temperature": 2.5,
  "packaging_condition": "Good condition",
  "notes": "First batch of the month"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `batch_number` | string | Yes | max:255, unique |
| `import_declaration_number` | string | No | max:255, nullable |
| `local_production_number` | string | No | max:255, nullable |
| `production_date` | date | Yes | before_or_equal:today |
| `expiry_date` | date | Yes | after:production_date |
| `receiving_datetime` | datetime | No | nullable |
| `quantity` | numeric | Yes | min:0.01 |
| `unit` | string | Yes | max:50 |
| `status` | string | No | in:pending,received,in_storage,in_transit,blocked,recalled,expired,disposed |
| `warehouse_location_id` | integer | No | exists:warehouse_locations,id, nullable |
| `receiving_temperature` | numeric | No | min:-50, max:50, nullable |
| `packaging_condition` | string | No | max:1000, nullable |
| `product_id` | integer | Yes | exists:products,id |
| `received_by_user_id` | integer | No | exists:users,id, nullable |
| `linked_documents` | array | No | array of strings, max:255 each, nullable |
| `notes` | string | No | max:1000, nullable |

**Features:**
- Automatic `remaining_quantity` initialization (equals `quantity`)
- String trimming for text fields
- Batch number uniqueness check
- Production date must not be in the future
- Expiry date must be after production date

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "batch_number": "BATCH-001",
    "quantity": 500.0,
    "remaining_quantity": 500.0,
    ...
  }
}
```

---

### View Batch

**GET** `/api/batches/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "batch_number": "BATCH-001",
    "product": {...},
    "warehouse_location": {...},
    ...
  }
}
```

---

### Update Batch

**PUT/PATCH** `/api/batches/{id}`

Supports partial updates. Only send fields you want to change.

**Request Body Example:**
```json
{
  "status": "blocked",
  "remaining_quantity": 400,
  "notes": "Quality issue detected - blocked for inspection"
}
```

**Validation Rules:**
- Same as create, but all fields are optional (`sometimes`)
- `batch_number` uniqueness ignores current record
- `remaining_quantity` cannot exceed total `quantity`
- When updating both `quantity` and `remaining_quantity`, validation ensures `remaining_quantity` ≤ new `quantity`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "status": "blocked",
    "remaining_quantity": 400.0,
    ...
  }
}
```

---

### Delete Batch

**DELETE** `/api/batches/{id}`

**Response:** `200 OK`
```json
{
  "message": "Batch deleted successfully"
}
```

---

## Batch Types & Status

### Local vs Imported Batches

**Local Batch:**
```json
{
  "batch_number": "BATCH-LOCAL-001",
  "local_production_number": "LOC-123456",
  "import_declaration_number": null
}
```

**Imported Batch:**
```json
{
  "batch_number": "BATCH-IMP-001",
  "import_declaration_number": "IMP-987654",
  "local_production_number": null
}
```

### Batch Status

| Status | Description | Use Case |
|--------|-------------|----------|
| `pending` | Awaiting receipt | Order placed, not yet received |
| `received` | Just received | Initial receipt, pending storage assignment |
| `in_storage` | Stored in warehouse | Normal storage state |
| `in_transit` | Being moved | Between locations |
| `blocked` | Blocked for quality | Quality issues, pending investigation |
| `recalled` | Product recall | Recall initiated |
| `expired` | Past expiry date | Automated or manual marking |
| `disposed` | Disposed/destroyed | Final state |

---

## Quantity Tracking

### Initial Creation
```json
{
  "quantity": 500,
  // remaining_quantity automatically set to 500
}
```

### Consumption Tracking
```json
{
  "quantity": 500,
  "remaining_quantity": 350
  // 150 units consumed
}
```

### Validation
- `remaining_quantity` must be ≥ 0
- `remaining_quantity` must be ≤ `quantity`
- When updating only `remaining_quantity`, it validates against existing `quantity`
- When updating both, validates `remaining_quantity` ≤ new `quantity`

---

## Temperature Tracking

### Recording Temperature
```json
{
  "receiving_temperature": 2.5,
  "temperature_history": [
    {
      "timestamp": "2026-01-05T10:30:00Z",
      "temperature": 2.5,
      "location": "Receiving Dock",
      "recorded_by": 1
    }
  ]
}
```

### Temperature Ranges
- Minimum: -50°C
- Maximum: 50°C
- Precision: 2 decimal places

---

## Movement & Document Tracking

### Movement History
```json
{
  "movement_history": [
    {
      "timestamp": "2026-01-05T10:30:00Z",
      "from_location_id": null,
      "to_location_id": 1,
      "quantity": 500,
      "type": "receiving",
      "user_id": 1
    },
    {
      "timestamp": "2026-01-10T14:00:00Z",
      "from_location_id": 1,
      "to_location_id": 2,
      "quantity": 500,
      "type": "transfer",
      "user_id": 2
    }
  ]
}
```

### Linked Documents
```json
{
  "linked_documents": [
    "invoice_001.pdf",
    "quality_certificate_002.pdf",
    "lab_results_003.pdf",
    "import_declaration_004.pdf"
  ]
}
```

---

## Authorization

### Permissions

- `view_any_batch` - List batches
- `view_batch` - View specific batch
- `create_batch` - Create new batch
- `update_batch` - Update batch
- `delete_batch` - Delete batch
- `restore_batch` - Restore deleted batch
- `force_delete_batch` - Permanently delete

### Role Access

**Recall Admin** - Full access

**Quality Manager** - Full CRUD access:
- ✅ Create, Read, Update, Delete batches
- ✅ Manage batch status and quality data

**Warehouse Operator** - Limited access:
- ✅ View batches
- ✅ Update batch status and quantity
- ❌ Cannot delete batches

**Branch Manager** - View only for their branch

**Auditor** - View only:
- ✅ View all batches
- ❌ Cannot modify batch data

---

## Model Methods & Scopes

### Scopes

```php
// Get active batches (received or in_storage)
$batches = Batch::active()->get();

// Get expired batches
$expired = Batch::expired()->get();

// Get batches expiring within N days
$expiring = Batch::expiringWithinDays(7)->get();

// Get blocked batches
$blocked = Batch::blocked()->get();

// Get recalled batches
$recalled = Batch::recalled()->get();

// Get ordered batches (by expiry date, earliest first)
$ordered = Batch::ordered()->get();

// Combine scopes
$criticalBatches = Batch::active()
    ->expiringWithinDays(3)
    ->ordered()
    ->get();
```

### Helper Methods

```php
$batch = Batch::find(1);

// Check status
$isExpired = $batch->isExpired();        // boolean
$daysLeft = $batch->daysUntilExpiry();   // integer (0 if expired)

// Check quantity
$isConsumed = $batch->isFullyConsumed(); // boolean
$hasStock = $batch->hasQuantityAvailable(); // boolean

// Check origin
$isLocal = $batch->isLocal();            // boolean
$isImported = $batch->isImported();      // boolean
```

### Relationships

```php
// Get batch with relationships
$batch = Batch::with(['product', 'warehouseLocation', 'receivedBy'])->find(1);

// Access relationships
$productName = $batch->product->name;
$locationName = $batch->warehouseLocation->name;
$receiverName = $batch->receivedBy->name;
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
  "message": "No query results for model [App\\Models\\Batch] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The batch number has already been taken. (and 2 more errors)",
  "errors": {
    "batch_number": ["The batch number has already been taken."],
    "expiry_date": ["The expiry date field must be a date after production date."],
    "remaining_quantity": ["The remaining quantity field must be less than or equal to quantity."]
  }
}
```

---

## Example Usage

### Complete Workflow

```bash
# 1. Login
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "quality@nikora.ge", "password": "password123"}'

# 2. Create Local Batch
curl -X POST http://localhost/api/batches \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "batch_number": "BATCH-LOCAL-001",
    "local_production_number": "LOC-123456",
    "production_date": "2026-01-01",
    "expiry_date": "2026-07-01",
    "quantity": 500,
    "unit": "kg",
    "product_id": 1,
    "receiving_temperature": 2.5
  }'

# 3. Assign to Warehouse Location
curl -X PUT http://localhost/api/batches/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "warehouse_location_id": 1,
    "status": "in_storage"
  }'

# 4. Update Remaining Quantity (consumption)
curl -X PUT http://localhost/api/batches/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "remaining_quantity": 350
  }'

# 5. Block for Quality Issue
curl -X PUT http://localhost/api/batches/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "blocked",
    "notes": "Quality issue - temperature deviation detected"
  }'

# 6. List Expiring Batches (filter in application logic)
curl -X GET "http://localhost/api/batches?per_page=50" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Factory Usage

```php
use App\Models\Batch;

// Create basic batch
$batch = Batch::factory()->create();

// Create local batch
$local = Batch::factory()->local()->create();

// Create imported batch
$imported = Batch::factory()->imported()->create();

// Create received batch
$received = Batch::factory()->received()->create();

// Create batch in storage
$inStorage = Batch::factory()->inStorage()->create();

// Create blocked batch
$blocked = Batch::factory()->blocked()->create();

// Create recalled batch
$recalled = Batch::factory()->recalled()->create();

// Create expired batch
$expired = Batch::factory()->expired()->create();

// Create batch expiring in N days
$expiring = Batch::factory()->expiringIn(7)->create();

// Create fully consumed batch
$consumed = Batch::factory()->fullyConsumed()->create();

// Combine states
$batch = Batch::factory()
    ->local()
    ->inStorage()
    ->create();
```

---

## Performance Optimizations

1. **Indexes** - Added on:
   - `batch_number`
   - `status`
   - `expiry_date`
   - `product_id`
   - `warehouse_location_id`
   - Compound: `(product_id, status)`
   - Compound: `(expiry_date, status)`

2. **Cursor Pagination** - Efficient for large datasets

3. **Deterministic Sorting** - Ordered by `expiry_date` then `id`

4. **Eager Loading** - Product and warehouse location loaded by default

5. **JSON Columns** - Used for flexible arrays (documents, history)

---

## Testing

Run tests with:
```bash
composer test
```

**Test Files:**
- `tests/Feature/Api/BatchTest.php` - API endpoint tests
- `tests/Feature/Actions/CreateBatchActionTest.php` - Create action tests
- `tests/Feature/Actions/UpdateBatchActionTest.php` - Update action tests
- `tests/Feature/Actions/DeleteBatchActionTest.php` - Delete action tests
- `tests/Feature/Models/BatchTest.php` - Model tests
- `tests/Feature/Policies/BatchPolicyTest.php` - Policy tests

---

## Integration with Other Modules

The Batch module integrates with:

1. **Product Module** - Every batch belongs to a product
2. **Warehouse Location Module** - Batches stored in locations
3. **Receiving Module** (upcoming) - Batches created during receiving
4. **Temperature Monitoring** (upcoming) - Temperature history tracking
5. **Movement Traceability** (upcoming) - Movement history tracking
6. **Expiry Management** (upcoming) - Expiry alerts and FEFO logic
7. **Recall Module** - Batches can be recalled

---

## Notes

- `remaining_quantity` automatically initialized to equal `quantity` on creation
- Expiry date must be after production date
- Production date cannot be in the future
- Batch numbers must be unique across all batches
- Temperature values stored with 2 decimal precision
- JSON fields provide flexibility for tracking documents and history
- Cursor pagination ordered by expiry date (earliest first) for FEFO support
