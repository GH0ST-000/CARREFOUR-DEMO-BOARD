# Warehouse & Location Structure API Documentation

## Overview

Complete RESTful API for managing hierarchical warehouse and storage locations with temperature specifications, sensor support, and inspection scheduling. Supports unlimited depth hierarchies from central warehouses down to individual zones.

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
│   └── WarehouseLocation.php
├── Actions/WarehouseLocation/
│   ├── CreateWarehouseLocationAction.php
│   ├── UpdateWarehouseLocationAction.php
│   └── DeleteWarehouseLocationAction.php
├── Http/
│   ├── Controllers/Api/
│   │   └── WarehouseLocationController.php
│   ├── Requests/Api/
│   │   ├── CreateWarehouseLocationRequest.php
│   │   └── UpdateWarehouseLocationRequest.php
│   └── Resources/
│       └── WarehouseLocationResource.php
├── Policies/
│   └── WarehouseLocationPolicy.php
database/
├── migrations/
│   └── 2026_04_06_100000_create_warehouse_locations_table.php
└── factories/
    └── WarehouseLocationFactory.php
```

---

## Database Schema

```sql
CREATE TABLE warehouse_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Information
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    type ENUM(
        'central_warehouse',
        'regional_warehouse',
        'branch',
        'storage_unit',
        'zone'
    ) NOT NULL,
    
    -- Hierarchy
    parent_id BIGINT UNSIGNED NULL,
    
    -- Temperature Specifications
    temp_min DECIMAL(5,2) NULL,
    temp_max DECIMAL(5,2) NULL,
    
    -- Management
    responsible_user_id BIGINT UNSIGNED NULL,
    inspection_frequency_hours INT NULL,
    
    -- Additional Information
    description TEXT NULL,
    address VARCHAR(255) NULL,
    has_sensor BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (parent_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_type (type),
    INDEX idx_parent_id (parent_id),
    INDEX idx_responsible_user_id (responsible_user_id),
    INDEX idx_is_active (is_active),
    INDEX idx_type_active (type, is_active)
);
```

---

## Pagination

The Warehouse Location API uses **cursor pagination** for efficient querying.

### Query Parameters

| Parameter | Type | Default | Range | Description |
|-----------|------|---------|-------|-------------|
| `per_page` | integer | 25 | 1-100 | Number of items per page |
| `cursor` | string | null | - | Cursor token from previous response |

### Example Requests

```bash
# Get first page
GET /api/warehouse-locations
Authorization: Bearer {token}

# Custom page size
GET /api/warehouse-locations?per_page=50
Authorization: Bearer {token}

# Navigate to next page
GET /api/warehouse-locations?cursor=eyJuYW1lIjoiV2FyZWhvdXNlIEEi...
Authorization: Bearer {token}
```

---

## API Endpoints

### Authentication Required

All endpoints require JWT authentication:
```
Authorization: Bearer {token}
```

### List Warehouse Locations

**GET** `/api/warehouse-locations`

Returns paginated list of locations, ordered by name.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Central Warehouse",
      "code": "CW-001",
      "type": "central_warehouse",
      "parent_id": null,
      "parent": null,
      "temp_min": null,
      "temp_max": null,
      "responsible_user_id": 1,
      "responsible_user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@nikora.ge"
      },
      "inspection_frequency_hours": 24,
      "description": "Main central warehouse in Tbilisi",
      "address": "123 Warehouse St, Tbilisi, Georgia",
      "has_sensor": true,
      "is_active": true,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### Create Warehouse Location

**POST** `/api/warehouse-locations`

**Request Body:**
```json
{
  "name": "Cold Storage A",
  "code": "CS-A-001",
  "type": "storage_unit",
  "parent_id": 1,
  "temp_min": 0,
  "temp_max": 4,
  "responsible_user_id": 2,
  "inspection_frequency_hours": 8,
  "description": "Temperature-controlled cold storage",
  "address": "Building A, Floor 1",
  "has_sensor": true,
  "is_active": true
}
```

**Validation Rules:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255 |
| `code` | string | Yes | max:255, unique |
| `type` | string | Yes | in:central_warehouse,regional_warehouse,branch,storage_unit,zone |
| `parent_id` | integer | No | exists:warehouse_locations,id, nullable |
| `temp_min` | numeric | No | min:-50, max:50, nullable |
| `temp_max` | numeric | No | min:-50, max:50, gte:temp_min, nullable |
| `responsible_user_id` | integer | No | exists:users,id, nullable |
| `inspection_frequency_hours` | integer | No | min:1, max:168, nullable |
| `description` | string | No | max:1000, nullable |
| `address` | string | No | max:500, nullable |
| `has_sensor` | boolean | No | default: false |
| `is_active` | boolean | No | default: true |

**Features:**
- Hierarchical structure support
- Temperature range validation (max ≥ min)
- Code uniqueness check
- String trimming for text fields

**Response:** `201 Created`
```json
{
  "data": {
    "id": 2,
    "name": "Cold Storage A",
    "code": "CS-A-001",
    ...
  }
}
```

---

### View Warehouse Location

**GET** `/api/warehouse-locations/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": 2,
    "name": "Cold Storage A",
    "parent": {
      "id": 1,
      "name": "Central Warehouse",
      "code": "CW-001",
      "type": "central_warehouse"
    },
    ...
  }
}
```

---

### Update Warehouse Location

**PUT/PATCH** `/api/warehouse-locations/{id}`

Supports partial updates.

**Request Body Example:**
```json
{
  "temp_min": -1,
  "temp_max": 5,
  "inspection_frequency_hours": 6,
  "is_active": false
}
```

**Validation Rules:**
- Same as create, but all fields are optional (`sometimes`)
- `code` uniqueness ignores current record
- Temperature validation still applies

**Response:** `200 OK`

---

### Delete Warehouse Location

**DELETE** `/api/warehouse-locations/{id}`

**Response:** `200 OK`
```json
{
  "message": "Warehouse location deleted successfully"
}
```

**Note:** Child locations' `parent_id` will be set to `null` (cascade handled by database).

---

## Location Types & Hierarchy

### Location Types

| Type | Description | Typical Parent |
|------|-------------|----------------|
| `central_warehouse` | Main distribution center | None (root) |
| `regional_warehouse` | Regional distribution point | Central warehouse |
| `branch` | Store/retail location | Regional warehouse or Central |
| `storage_unit` | Storage room/area | Warehouse or Branch |
| `zone` | Specific zone within storage | Storage unit |

### Hierarchical Structure Example

```
Central Warehouse (CW-001)
├── Regional Warehouse East (RW-E-001)
│   ├── Branch Tbilisi (BR-TBS-001)
│   │   ├── Storage Unit 1 (SU-TBS-001)
│   │   │   ├── Zone A (Z-TBS-001-A)
│   │   │   └── Zone B (Z-TBS-001-B)
│   │   └── Storage Unit 2 (SU-TBS-002)
│   └── Branch Batumi (BR-BAT-001)
└── Regional Warehouse West (RW-W-001)
    └── Branch Kutaisi (BR-KUT-001)
```

### Creating Hierarchy

```bash
# 1. Create Central Warehouse (no parent)
POST /api/warehouse-locations
{
  "name": "Central Warehouse",
  "code": "CW-001",
  "type": "central_warehouse",
  "parent_id": null
}

# 2. Create Regional Warehouse (parent: central)
POST /api/warehouse-locations
{
  "name": "Regional Warehouse East",
  "code": "RW-E-001",
  "type": "regional_warehouse",
  "parent_id": 1
}

# 3. Create Branch (parent: regional)
POST /api/warehouse-locations
{
  "name": "Branch Tbilisi",
  "code": "BR-TBS-001",
  "type": "branch",
  "parent_id": 2
}

# 4. Create Storage Unit (parent: branch)
POST /api/warehouse-locations
{
  "name": "Cold Storage",
  "code": "CS-TBS-001",
  "type": "storage_unit",
  "parent_id": 3,
  "temp_min": 0,
  "temp_max": 4
}
```

---

## Temperature Control

### Temperature-Controlled Locations

```json
{
  "name": "Frozen Storage",
  "code": "FS-001",
  "type": "storage_unit",
  "temp_min": -18,
  "temp_max": -15
}
```

### Common Temperature Ranges

| Storage Type | Min (°C) | Max (°C) |
|--------------|----------|----------|
| Frozen | -18 | -15 |
| Chilled | 0 | 4 |
| Cool | 10 | 15 |
| Ambient | null | null |

### Temperature Validation

- Both `temp_min` and `temp_max` are optional
- `temp_max` must be ≥ `temp_min`
- Range: -50°C to 50°C
- Precision: 2 decimal places

---

## Inspection Management

### Setting Inspection Frequency

```json
{
  "inspection_frequency_hours": 8
}
```

**Valid Range:** 1-168 hours (1 hour to 1 week)

**Common Frequencies:**
- Every 4 hours: Temperature-sensitive storage
- Every 8 hours: Standard cold storage
- Every 12 hours: Cool storage
- Every 24 hours: Ambient storage

---

## Sensor Integration

### Sensor-Enabled Locations

```json
{
  "has_sensor": true,
  "temp_min": 0,
  "temp_max": 4,
  "inspection_frequency_hours": 4
}
```

**Benefits:**
- Automatic temperature monitoring
- Real-time alerts for violations
- Reduced manual inspection frequency
- Historical data tracking

---

## Authorization

### Permissions

- `view_any_warehouse_location` - List locations
- `view_warehouse_location` - View specific location
- `create_warehouse_location` - Create new location
- `update_warehouse_location` - Update location
- `delete_warehouse_location` - Delete location
- `restore_warehouse_location` - Restore deleted location
- `force_delete_warehouse_location` - Permanently delete

### Role Access

**Recall Admin** - Full access

**Quality Manager** - Full CRUD access:
- ✅ Create, Read, Update, Delete locations
- ✅ Manage temperature specifications

**Branch Manager** - Limited access:
- ✅ View locations for their branch
- ❌ Cannot modify location structure

**Warehouse Operator** - View only:
- ✅ View locations
- ❌ Cannot modify

**Auditor** - View only:
- ✅ View all locations
- ❌ Cannot modify

---

## Model Methods & Scopes

### Scopes

```php
// Get active locations only
$locations = WarehouseLocation::active()->get();

// Get ordered locations (by name)
$ordered = WarehouseLocation::ordered()->get();

// Get root locations (no parent)
$roots = WarehouseLocation::roots()->get();

// Combine scopes
$activeRoots = WarehouseLocation::active()
    ->roots()
    ->ordered()
    ->get();
```

### Helper Methods

```php
$location = WarehouseLocation::find(1);

// Check temperature control
$hasTemp = $location->hasTemperatureControl();  // boolean

// Validate temperature
$isValid = $location->isTemperatureInRange(2.5);  // boolean

// Example usage
if ($location->hasTemperatureControl()) {
    if (!$location->isTemperatureInRange($recordedTemp)) {
        // Alert: Temperature out of range!
    }
}
```

### Relationships

```php
// Get location with relationships
$location = WarehouseLocation::with(['parent', 'children', 'responsibleUser'])->find(1);

// Access relationships
$parentName = $location->parent?->name;
$childCount = $location->children->count();
$managerName = $location->responsibleUser?->name;

// Get all descendants (recursive query needed in application)
$descendants = $location->children->flatMap(fn($child) => 
    collect([$child])->merge($child->children)
);
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
  "message": "No query results for model [App\\Models\\WarehouseLocation] {id}"
}
```

### 422 Validation Error
```json
{
  "message": "The code has already been taken. (and 1 more error)",
  "errors": {
    "code": ["The code has already been taken."],
    "temp_max": ["The temp max field must be greater than or equal to temp min."]
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

# 2. Create Central Warehouse
curl -X POST http://localhost/api/warehouse-locations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Central Warehouse Tbilisi",
    "code": "CW-TBS-001",
    "type": "central_warehouse",
    "address": "123 Warehouse District, Tbilisi",
    "is_active": true
  }'

# 3. Create Temperature-Controlled Storage Unit
curl -X POST http://localhost/api/warehouse-locations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Cold Storage A",
    "code": "CS-A-001",
    "type": "storage_unit",
    "parent_id": 1,
    "temp_min": 0,
    "temp_max": 4,
    "has_sensor": true,
    "inspection_frequency_hours": 8,
    "responsible_user_id": 2
  }'

# 4. Create Zones within Storage Unit
curl -X POST http://localhost/api/warehouse-locations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Zone A",
    "code": "CS-A-001-ZA",
    "type": "zone",
    "parent_id": 2
  }'

# 5. Update Temperature Range
curl -X PUT http://localhost/api/warehouse-locations/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "temp_min": -1,
    "temp_max": 5
  }'

# 6. Deactivate Location
curl -X PUT http://localhost/api/warehouse-locations/3 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "is_active": false
  }'

# 7. List All Locations
curl -X GET "http://localhost/api/warehouse-locations?per_page=50" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Factory Usage

```php
use App\Models\WarehouseLocation;

// Create basic location
$location = WarehouseLocation::factory()->create();

// Create central warehouse
$central = WarehouseLocation::factory()->centralWarehouse()->create();

// Create regional warehouse
$regional = WarehouseLocation::factory()->regionalWarehouse()->create();

// Create branch
$branch = WarehouseLocation::factory()->branch()->create();

// Create storage unit
$storage = WarehouseLocation::factory()->storageUnit()->create();

// Create zone
$zone = WarehouseLocation::factory()->zone()->create();

// Create temperature-controlled location
$coldStorage = WarehouseLocation::factory()
    ->temperatureControlled()
    ->create();

// Create frozen storage
$frozen = WarehouseLocation::factory()
    ->frozen()
    ->create();

// Create active location with sensor
$location = WarehouseLocation::factory()
    ->active()
    ->withSensor()
    ->create();

// Create hierarchical structure
$central = WarehouseLocation::factory()->centralWarehouse()->create();
$regional = WarehouseLocation::factory()
    ->regionalWarehouse()
    ->create(['parent_id' => $central->id]);
$branch = WarehouseLocation::factory()
    ->branch()
    ->create(['parent_id' => $regional->id]);
```

---

## Performance Optimizations

1. **Indexes** - Added on:
   - `type`
   - `parent_id`
   - `responsible_user_id`
   - `is_active`
   - Compound: `(type, is_active)`

2. **Cursor Pagination** - Efficient for large datasets

3. **Deterministic Sorting** - Ordered by `name` then `id`

4. **Eager Loading** - Parent and responsible user loaded by default

5. **Self-Referencing** - Efficient parent-child queries

---

## Testing

Run tests with:
```bash
composer test
```

**Test Files:**
- `tests/Feature/Api/WarehouseLocationTest.php` - API endpoint tests
- `tests/Feature/Actions/CreateWarehouseLocationActionTest.php` - Create action tests
- `tests/Feature/Actions/UpdateWarehouseLocationActionTest.php` - Update action tests
- `tests/Feature/Actions/DeleteWarehouseLocationActionTest.php` - Delete action tests
- `tests/Feature/Models/WarehouseLocationTest.php` - Model tests
- `tests/Feature/Policies/WarehouseLocationPolicyTest.php` - Policy tests

---

## Integration with Other Modules

The Warehouse Location module integrates with:

1. **Batch Module** - Batches stored in locations
2. **Receiving Module** (upcoming) - Receipts assigned to locations
3. **Temperature Monitoring** (upcoming) - Location temperature tracking
4. **Movement Traceability** (upcoming) - Movement between locations
5. **User Module** - Responsible user assignment

---

## Best Practices

### Naming Conventions

```
Central Warehouse: CW-{CITY}-{NUMBER}
Regional Warehouse: RW-{REGION}-{NUMBER}
Branch: BR-{CITY}-{NUMBER}
Storage Unit: SU-{CITY}-{NUMBER} or CS-{CODE} for cold storage
Zone: Z-{PARENT_CODE}-{LETTER}
```

**Examples:**
- `CW-TBS-001` - Central Warehouse Tbilisi #1
- `RW-E-001` - Regional Warehouse East #1
- `BR-BAT-001` - Branch Batumi #1
- `CS-A-001` - Cold Storage A #1
- `Z-CS-A-001-A` - Zone A in Cold Storage A

### Hierarchy Design

- Keep hierarchy depth reasonable (typically 3-5 levels)
- Use consistent naming patterns
- Document parent-child relationships
- Plan for future expansion

### Temperature Management

- Set realistic temperature ranges
- Enable sensors for critical storage
- Regular inspection scheduling
- Document temperature requirements

---

## Notes

- Location codes must be unique across all locations
- Parent-child relationships support unlimited depth
- Temperature ranges are optional (for ambient storage)
- Inspection frequency range: 1-168 hours
- Deleting a location sets children's `parent_id` to `null`
- Active/inactive flag for operational management
- Sensor flag for IoT integration readiness
