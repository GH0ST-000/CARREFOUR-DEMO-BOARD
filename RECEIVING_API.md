# Receiving Process API Documentation

## Overview

Complete RESTful API for managing product receiving and quality inspections with photo evidence, temperature monitoring, and document verification. Supports multi-status workflows for acceptance, rejection, and quarantine.

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
│   └── Receiving.php
├── Actions/Receiving/
│   ├── CreateReceivingAction.php
│   ├── UpdateReceivingAction.php
│   └── DeleteReceivingAction.php
├── Http/
│   ├── Controllers/Api/
│   │   └── ReceivingController.php
│   ├── Requests/Api/
│   │   ├── CreateReceivingRequest.php
│   │   └── UpdateReceivingRequest.php
│   └── Resources/
│       └── ReceivingResource.php
├── Policies/
│   └── ReceivingPolicy.php
database/
├── migrations/
│   └── 2026_04_06_100002_create_receivings_table.php
└── factories/
    └── ReceivingFactory.php
```

---

## Database Schema

```sql
CREATE TABLE receivings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Receipt Information
    receipt_number VARCHAR(255) UNIQUE NULL,
    receipt_datetime DATETIME NOT NULL,
    supplier_invoice_number VARCHAR(255) NULL,
    
    -- Batch Reference
    batch_id BIGINT UNSIGNED NOT NULL,
    warehouse_location_id BIGINT UNSIGNED NULL,
    
    -- Quantity & Quality
    received_quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(255) NOT NULL,
    recorded_temperature DECIMAL(5,2) NULL,
    temperature_compliant BOOLEAN DEFAULT TRUE,
    temperature_notes TEXT NULL,
    
    -- Quality Inspection
    packaging_condition ENUM(
        'excellent',
        'good',
        'acceptable',
        'damaged',
        'rejected'
    ) DEFAULT 'good',
    quality_notes TEXT NULL,
    documents_verified BOOLEAN DEFAULT FALSE,
    missing_documents JSON NULL,
    
    -- Status
    status ENUM(
        'pending',
        'accepted',
        'rejected',
        'quarantined'
    ) DEFAULT 'pending',
    rejection_reason TEXT NULL,
    
    -- Photo Evidence
    photos JSON NULL,
    
    -- Users
    received_by_user_id BIGINT UNSIGNED NULL,
    verified_by_user_id BIGINT UNSIGNED NULL,
    
    -- Additional Information
    notes TEXT NULL,
    
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_receipt_number (receipt_number),
    INDEX idx_receipt_datetime (receipt_datetime),
    INDEX idx_batch_id (batch_id),
    INDEX idx_warehouse_location_id (warehouse_location_id),
    INDEX idx_status (status),
    INDEX idx_received_by_user_id (received_by_user_id),
    INDEX idx_status_datetime (status, receipt_datetime)
);
```

---

## Pagination

The Receiving API uses **cursor pagination** for efficient querying.

### Query Parameters

| Parameter | Type | Default | Range | Description |
|-----------|------|---------|-------|-------------|
| `per_page` | integer | 25 | 1-100 | Number of items per page |
| `cursor` | string | null | - | Cursor token from previous response |

---

## API Endpoints

### Authentication Required

All endpoints require JWT authentication:
```
Authorization: Bearer {token}
```

### List Receivings

**GET** `/api/receivings`

Returns paginated list of receivings, ordered by receipt datetime (most recent first).

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "receipt_number": "RCP-001",
      "receipt_datetime": "2026-01-05T10:30:00.000000Z",
      "supplier_invoice_number": "INV-12345",
      "batch_id": 1,
      "batch": {
        "id": 1,
        "batch_number": "BATCH-001",
        "product": {
          "id": 1,
          "name": "Fresh Milk",
          "sku": "SKU-MILK-001"
        }
      },
      "warehouse_location_id": 1,
      "warehouse_location": {
        "id": 1,
        "name": "Cold Storage A",
        "code": "CS-A-001",
        "type": "storage_unit"
      },
      "received_by_user_id": 1,
      "received_by": {
        "id": 1,
        "name": "John Doe",
        "email": "john@nikora.ge"
      },
      "verified_by_user_id": 2,
      "verified_by": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@nikora.ge"
      },
      "received_quantity": 500.0,
      "unit": "kg",
      "recorded_temperature": 2.5,
      "temperature_compliant": true,
      "temperature_notes": null,
      "packaging_condition": "excellent",
      "quality_notes": "All checks passed",
      "documents_verified": true,
      "missing_documents": null,
      "status": "accepted",
      "rejection_reason": null,
      "photos": ["photo1.jpg", "photo2.jpg", "photo3.jpg"],
      "notes": "Received in excellent condition",
      "created_at": "2026-01-05T10:30:00.000000Z",
      "updated_at": "2026-01-05T10:30:00.000000Z"
    }
  ]
}
```

---

### Create Receiving

**POST** `/api/receivings`

**Request Body:**
```json
{
  "batch_id": 1,
  "warehouse_location_id": 1,
  "received_by_user_id": 1,
  "receipt_datetime": "2026-01-05T10:30:00",
  "received_quantity": 500,
  "unit": "kg",
  "receipt_number": "RCP-001",
  "supplier_invoice_number": "INV-12345",
  "recorded_temperature": 2.5,
  "temperature_compliant": true,
  "packaging_condition": "excellent",
  "documents_verified": true,
  "status": "accepted",
  "photos": ["photo1.jpg", "photo2.jpg"],
  "notes": "Received in good condition"
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `batch_id` | integer | Yes | exists:batches,id |
| `warehouse_location_id` | integer | Yes | exists:warehouse_locations,id |
| `received_by_user_id` | integer | Yes | exists:users,id |
| `verified_by_user_id` | integer | No | exists:users,id, nullable |
| `receipt_datetime` | datetime | Yes | - |
| `received_quantity` | numeric | Yes | min:0.01 |
| `unit` | string | Yes | max:50 |
| `receipt_number` | string | No | max:255, nullable |
| `supplier_invoice_number` | string | No | max:255, nullable |
| `status` | string | No | in:pending,accepted,rejected,quarantined |
| `recorded_temperature` | numeric | No | min:-50, max:50, nullable |
| `temperature_compliant` | boolean | No | nullable |
| `temperature_notes` | string | No | max:1000, nullable |
| `packaging_condition` | string | No | in:excellent,good,acceptable,damaged,rejected |
| `quality_notes` | string | No | max:1000, nullable |
| `documents_verified` | boolean | No | nullable |
| `missing_documents` | array | No | array of strings, max:255 each, nullable |
| `photos` | array | No | array of strings, max:255 each, nullable |
| `rejection_reason` | string | No | max:1000, nullable |
| `notes` | string | No | max:1000, nullable |

**Features:**
- String trimming for text fields
- Empty strings converted to null

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "receipt_number": "RCP-001",
    "status": "accepted",
    ...
  }
}
```

---

### View Receiving

**GET** `/api/receivings/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "batch": {...},
    "warehouse_location": {...},
    ...
  }
}
```

---

### Update Receiving

**PUT/PATCH** `/api/receivings/{id}`

Supports partial updates. Commonly used to change status, add verification, or update quality data.

**Request Body Example:**
```json
{
  "status": "accepted",
  "verified_by_user_id": 2,
  "documents_verified": true,
  "quality_notes": "All quality checks passed"
}
```

**Response:** `200 OK`

---

### Delete Receiving

**DELETE** `/api/receivings/{id}`

**Response:** `200 OK`
```json
{
  "message": "Receiving deleted successfully"
}
```

---

## Receiving Status Workflow

### Status Transitions

```
pending → accepted    (normal receipt)
pending → rejected    (quality failure)
pending → quarantined (requires investigation)
```

### Status Descriptions

| Status | Description | When to Use |
|--------|-------------|-------------|
| `pending` | Initial state | Just received, awaiting inspection |
| `accepted` | Quality approved | All checks passed, ready for storage |
| `rejected` | Quality failed | Cannot be stored, will be returned |
| `quarantined` | Under investigation | Quality uncertain, needs further review |

---

## Quality Inspection Checklist

### Temperature Compliance

```json
{
  "recorded_temperature": 2.5,
  "temperature_compliant": true,
  "temperature_notes": "Within acceptable range (0-4°C)"
}
```

**Temperature Non-Compliant Example:**
```json
{
  "recorded_temperature": 15.0,
  "temperature_compliant": false,
  "temperature_notes": "Temperature exceeded maximum (4°C)",
  "status": "rejected",
  "rejection_reason": "Temperature non-compliant"
}
```

### Packaging Condition

| Condition | Description | Action |
|-----------|-------------|--------|
| `excellent` | Perfect condition | Accept |
| `good` | Normal condition | Accept |
| `acceptable` | Minor issues | Accept with notes |
| `damaged` | Significant damage | Reject or quarantine |
| `rejected` | Unacceptable | Reject |

### Document Verification

```json
{
  "documents_verified": true,
  "missing_documents": null
}
```

**Missing Documents Example:**
```json
{
  "documents_verified": false,
  "missing_documents": [
    "certificate_of_origin",
    "quality_certificate",
    "lab_results"
  ]
}
```

### Photo Evidence

```json
{
  "photos": [
    "receiving_001_overall.jpg",
    "receiving_001_packaging.jpg",
    "receiving_001_label.jpg",
    "receiving_001_temperature.jpg"
  ]
}
```

---

## Common Workflows

### Accept Receiving (Normal Flow)

```bash
# 1. Create receiving record
curl -X POST http://localhost/api/receivings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "batch_id": 1,
    "warehouse_location_id": 1,
    "received_by_user_id": 1,
    "receipt_datetime": "2026-01-05T10:30:00",
    "received_quantity": 500,
    "unit": "kg",
    "recorded_temperature": 2.5,
    "temperature_compliant": true,
    "packaging_condition": "excellent",
    "photos": ["photo1.jpg", "photo2.jpg"],
    "status": "pending"
  }'

# 2. Verify and accept
curl -X PUT http://localhost/api/receivings/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "accepted",
    "verified_by_user_id": 2,
    "documents_verified": true,
    "quality_notes": "All quality checks passed"
  }'
```

### Reject Receiving (Quality Issue)

```bash
curl -X PUT http://localhost/api/receivings/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "rejected",
    "rejection_reason": "Temperature non-compliant - recorded 15°C",
    "temperature_compliant": false,
    "recorded_temperature": 15.0,
    "packaging_condition": "acceptable",
    "photos": ["evidence_temp_violation.jpg"]
  }'
```

### Quarantine Receiving (Investigation Needed)

```bash
curl -X PUT http://localhost/api/receivings/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "quarantined",
    "notes": "Suspicious packaging integrity - placed in quarantine for further inspection",
    "packaging_condition": "acceptable",
    "photos": ["packaging_detail_1.jpg", "packaging_detail_2.jpg"]
  }'
```

---

## Authorization

### Permissions

- `view_any_receiving` - List receivings
- `view_receiving` - View specific receiving
- `create_receiving` - Create new receiving record
- `update_receiving` - Update receiving
- `delete_receiving` - Delete receiving
- `restore_receiving` - Restore deleted receiving
- `force_delete_receiving` - Permanently delete

### Role Access

**Recall Admin** - Full access

**Quality Manager** - Full CRUD access:
- ✅ Create, Read, Update, Delete receivings
- ✅ Accept/reject/quarantine decisions
- ✅ Document verification

**Warehouse Operator** - Limited access:
- ✅ View receivings
- ✅ Create receiving records
- ✅ Update temperature and packaging data
- ❌ Cannot delete receivings

**Branch Manager** - View only for their branch

**Auditor** - View only:
- ✅ View all receiving records
- ❌ Cannot modify data

---

## Model Methods & Scopes

### Scopes

```php
// Get ordered receivings (most recent first)
$receivings = Receiving::ordered()->get();

// Get pending receivings
$pending = Receiving::pending()->get();

// Get accepted receivings
$accepted = Receiving::accepted()->get();

// Get rejected receivings
$rejected = Receiving::rejected()->get();

// Get quarantined receivings
$quarantined = Receiving::quarantined()->get();

// Get temperature non-compliant receivings
$tempIssues = Receiving::temperatureNonCompliant()->get();

// Combine scopes
$recentRejections = Receiving::rejected()
    ->ordered()
    ->limit(10)
    ->get();
```

### Helper Methods

```php
$receiving = Receiving::find(1);

// Check status
$isAccepted = $receiving->isAccepted();        // boolean
$isRejected = $receiving->isRejected();        // boolean
$isQuarantined = $receiving->isQuarantined();  // boolean
$isPending = $receiving->isPending();          // boolean

// Check quality
$hasPhotos = $receiving->hasPhotos();                      // boolean
$hasMissingDocs = $receiving->hasMissingDocuments();       // boolean
$isTempOk = $receiving->isTemperatureCompliant();          // boolean
$isDocsVerified = $receiving->isDocumentsVerified();       // boolean
$isPackagingOk = $receiving->isPackagingAcceptable();      // boolean
$isDocsComplete = $receiving->areDocumentsComplete();      // boolean
```

### Relationships

```php
// Get receiving with relationships
$receiving = Receiving::with([
    'batch.product',
    'warehouseLocation',
    'receivedBy',
    'verifiedBy'
])->find(1);

// Access relationships
$productName = $receiving->batch->product->name;
$locationName = $receiving->warehouseLocation->name;
$receiverName = $receiving->receivedBy->name;
$verifierName = $receiving->verifiedBy?->name;
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
  "message": "No query results for model [App\\Models\\Receiving] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The batch id field is required. (and 2 more errors)",
  "errors": {
    "batch_id": ["The batch id field is required."],
    "received_quantity": ["The received quantity field must be at least 0.01."],
    "packaging_condition": ["The selected packaging condition is invalid."]
  }
}
```

---

## Photo Evidence Management

### Uploading Photos

Photos are referenced as file paths/URLs in the JSON array:

```json
{
  "photos": [
    "storage/receivings/2026/01/receiving_001_overall.jpg",
    "storage/receivings/2026/01/receiving_001_packaging.jpg",
    "storage/receivings/2026/01/receiving_001_label.jpg"
  ]
}
```

### Recommended Photo Types

1. **Overall View** - General condition of shipment
2. **Packaging Detail** - Close-up of packaging condition
3. **Label Detail** - Product labels and batch information
4. **Temperature Reading** - Digital thermometer display
5. **Damage Evidence** - Any damage or quality issues
6. **Document Evidence** - Missing or incorrect documents

---

## Temperature Monitoring

### Temperature Tracking

```json
{
  "recorded_temperature": 2.5,
  "temperature_compliant": true,
  "temperature_notes": "Temperature within specification (0-4°C)"
}
```

### Non-Compliant Temperature

```json
{
  "recorded_temperature": 8.5,
  "temperature_compliant": false,
  "temperature_notes": "Temperature exceeds maximum acceptable (4°C)",
  "status": "quarantined"
}
```

**Valid Temperature Range:** -50°C to 50°C

---

## Packaging Condition Assessment

### Acceptable Conditions
- `excellent` - Perfect condition, no issues
- `good` - Normal wear, acceptable
- `acceptable` - Minor issues, still usable

### Unacceptable Conditions
- `damaged` - Significant damage detected
- `rejected` - Unacceptable condition

### Example Assessment

```json
{
  "packaging_condition": "damaged",
  "quality_notes": "Box crushed on one corner, product integrity uncertain",
  "photos": ["damage_photo_1.jpg", "damage_photo_2.jpg"],
  "status": "quarantined"
}
```

---

## Document Verification

### Complete Documentation

```json
{
  "documents_verified": true,
  "missing_documents": null
}
```

### Incomplete Documentation

```json
{
  "documents_verified": false,
  "missing_documents": [
    "certificate_of_origin",
    "quality_certificate",
    "lab_test_results"
  ],
  "notes": "Awaiting missing certificates from supplier"
}
```

---

## Factory Usage

```php
use App\Models\Receiving;

// Create basic receiving
$receiving = Receiving::factory()->create();

// Create with specific status
$pending = Receiving::factory()->pending()->create();
$accepted = Receiving::factory()->accepted()->create();
$rejected = Receiving::factory()->rejected()->create();
$quarantined = Receiving::factory()->quarantined()->create();

// Create with quality issues
$tempIssue = Receiving::factory()->temperatureNonCompliant()->create();
$withPhotos = Receiving::factory()->withPhotos()->create();
$missingDocs = Receiving::factory()->withMissingDocuments()->create();

// Combine states
$receiving = Receiving::factory()
    ->accepted()
    ->withPhotos()
    ->create();
```

---

## Performance Optimizations

1. **Indexes** - Added on:
   - `receipt_number`
   - `receipt_datetime`
   - `batch_id`
   - `warehouse_location_id`
   - `status`
   - `received_by_user_id`
   - Compound: `(status, receipt_datetime)`

2. **Cursor Pagination** - Efficient for large datasets

3. **Deterministic Sorting** - Ordered by `receipt_datetime` desc, then `id` desc

4. **Eager Loading** - Batch (with product), warehouse location, and users loaded by default

5. **JSON Columns** - Flexible storage for photos and documents

---

## Testing

Run tests with:
```bash
composer test
```

**Test Files:**
- `tests/Feature/Api/ReceivingTest.php` - API endpoint tests (24 tests)
- `tests/Feature/Actions/CreateReceivingActionTest.php` - Create action tests
- `tests/Feature/Actions/UpdateReceivingActionTest.php` - Update action tests
- `tests/Feature/Actions/DeleteReceivingActionTest.php` - Delete action tests
- `tests/Feature/Models/ReceivingTest.php` - Model tests (31 tests)
- `tests/Feature/Policies/ReceivingPolicyTest.php` - Policy tests (14 tests)

**Total:** 75 comprehensive tests

---

## Integration with Other Modules

The Receiving module integrates with:

1. **Batch Module** - Every receiving creates or updates a batch
2. **Warehouse Location Module** - Receivings assigned to specific locations
3. **Product Module** - Via batch relationship
4. **User Module** - Tracks who received and verified
5. **Temperature Monitoring** (upcoming) - Temperature history
6. **Movement Traceability** (upcoming) - Initial movement record
7. **Document Management** (upcoming) - Linked document files
8. **Audit Module** (upcoming) - Receiving audit trail

---

## Best Practices

### Receiving Workflow

1. **Create Pending Record**
   - Record basic receipt information
   - Capture initial temperature
   - Take overview photos
   - Set status to `pending`

2. **Quality Inspection**
   - Check temperature compliance
   - Inspect packaging condition
   - Verify documents
   - Take detailed photos
   - Add quality notes

3. **Make Decision**
   - **Accept:** Approve for storage
   - **Reject:** Return to supplier
   - **Quarantine:** Hold for investigation

4. **Final Verification**
   - Assign verified_by_user
   - Update final notes
   - Ensure all photos uploaded

### Photo Documentation

- Capture minimum 3 photos per receiving
- Include overall view, packaging, and labels
- Document any damage or issues
- Include temperature display in photo
- Use clear, well-lit images

### Quality Notes

- Be specific and factual
- Include measurements when relevant
- Document deviations from specifications
- Reference batch/invoice numbers
- Note any corrective actions taken

---

## Notes

- Receipt numbers are optional but recommended for traceability
- Default status is `pending` if not specified
- Default packaging condition is `good`
- Temperature compliance defaults to `true`
- Document verification defaults to `false`
- Deleting a batch cascades to delete its receivings
- Deleting a warehouse location sets receiving's location to `null`
- All text fields are trimmed automatically
- Empty strings are converted to `null` in nullable fields
