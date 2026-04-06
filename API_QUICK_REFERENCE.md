# API Quick Reference

Complete API endpoint reference for NIKORA Supply Chain Management System.

---

## Base URL

```
http://localhost/api
```

All endpoints require JWT authentication (except login):
```
Authorization: Bearer {your_token}
```

---

## Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login and get JWT token |
| POST | `/auth/logout` | Logout current session |
| POST | `/auth/refresh` | Refresh JWT token |
| GET | `/auth/me` | Get current user info |

---

## Manufacturers

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/manufacturers` | List all manufacturers | ✅ Cursor |
| POST | `/manufacturers` | Create new manufacturer | - |
| GET | `/manufacturers/{id}` | View manufacturer details | - |
| PUT | `/manufacturers/{id}` | Update manufacturer | - |
| DELETE | `/manufacturers/{id}` | Delete manufacturer | - |

📚 **Documentation:** [MANUFACTURER_API.md](MANUFACTURER_API.md)

---

## Products

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/products` | List all products | ✅ Cursor |
| POST | `/products` | Create new product | - |
| GET | `/products/{id}` | View product details | - |
| PUT | `/products/{id}` | Update product | - |
| DELETE | `/products/{id}` | Delete product | - |

📚 **Documentation:** [PRODUCT_API.md](PRODUCT_API.md)

**Key Features:**
- Local and imported products
- Temperature range tracking
- Allergen management
- FIFO/FEFO inventory policies
- SKU and barcode uniqueness

---

## Batches/Lots

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/batches` | List all batches | ✅ Cursor |
| POST | `/batches` | Create new batch | - |
| GET | `/batches/{id}` | View batch details | - |
| PUT | `/batches/{id}` | Update batch | - |
| DELETE | `/batches/{id}` | Delete batch | - |

📚 **Documentation:** [BATCH_API.md](BATCH_API.md)

**Key Features:**
- Full traceability
- Quantity and consumption tracking
- Status management (8 states)
- Temperature history
- Movement history
- Local/imported batch types

**Batch Statuses:**
- `pending` - Awaiting receipt
- `received` - Just received
- `in_storage` - Stored in warehouse
- `in_transit` - Being moved
- `blocked` - Blocked for quality
- `recalled` - Product recall
- `expired` - Past expiry date
- `disposed` - Disposed/destroyed

---

## Warehouse Locations

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/warehouse-locations` | List all locations | ✅ Cursor |
| POST | `/warehouse-locations` | Create new location | - |
| GET | `/warehouse-locations/{id}` | View location details | - |
| PUT | `/warehouse-locations/{id}` | Update location | - |
| DELETE | `/warehouse-locations/{id}` | Delete location | - |

📚 **Documentation:** [WAREHOUSE_LOCATION_API.md](WAREHOUSE_LOCATION_API.md)

**Key Features:**
- Hierarchical structure (unlimited depth)
- Temperature-controlled zones
- Sensor support
- Inspection scheduling
- Responsible user assignment

**Location Types:**
- `central_warehouse` - Main distribution center
- `regional_warehouse` - Regional hub
- `branch` - Store/retail location
- `storage_unit` - Storage room/area
- `zone` - Specific zone within storage

---

## Receiving Process

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/receivings` | List all receivings | ✅ Cursor |
| POST | `/receivings` | Create new receiving | - |
| GET | `/receivings/{id}` | View receiving details | - |
| PUT | `/receivings/{id}` | Update receiving | - |
| DELETE | `/receivings/{id}` | Delete receiving | - |

📚 **Documentation:** [RECEIVING_API.md](RECEIVING_API.md)

**Key Features:**
- Photo evidence capture
- Temperature compliance tracking
- Quality inspection workflow
- Document verification
- Multi-status workflow (pending, accepted, rejected, quarantined)
- Packaging condition assessment

**Receiving Statuses:**
- `pending` - Awaiting inspection
- `accepted` - Approved for storage
- `rejected` - Quality failed, return to supplier
- `quarantined` - Under investigation

**Packaging Conditions:**
- `excellent` - Perfect condition
- `good` - Normal wear
- `acceptable` - Minor issues
- `damaged` - Significant damage
- `rejected` - Unacceptable

---

## Users & Roles

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/roles` | List all roles with permissions |
| GET | `/roles/{id}` | View specific role details |
| GET | `/permissions` | List all permissions |
| POST | `/users/{user}/roles` | Assign role to user |
| DELETE | `/users/{user}/roles/{role}` | Remove role from user |

📚 **Documentation:** [USER_MANAGEMENT.md](USER_MANAGEMENT.md)

**Key Features:**
- Role-based access control (RBAC)
- 5 predefined roles: Recall Admin, Quality Manager, Branch Manager, Warehouse Operator, Auditor
- Granular permissions for all resources
- Multi-role support (users can have multiple roles)
- Branch-level access control

**Available Roles:**
- `Recall Admin` - Full system access
- `Quality Manager` - Quality control and oversight
- `Branch Manager` - Branch-level management
- `Warehouse Operator` - Inventory operations
- `Auditor` - Read-only compliance access

**User Creation:**
- Users are created via Filament Admin Panel at `/admin`
- Roles can be assigned during creation or via API afterward
- See full guide in [USER_MANAGEMENT.md](USER_MANAGEMENT.md)

---

## Dashboard

| Method | Endpoint | Description | Pagination |
|--------|----------|-------------|------------|
| GET | `/dashboard/stats` | Get comprehensive statistics | - |
| GET | `/dashboard/expiring-batches` | Get batches expiring soon | ✅ Cursor |
| GET | `/dashboard/recent-additions` | Get recently added items | - |
| GET | `/dashboard/visualization` | Get visualization data | - |

📚 **Documentation:** [DASHBOARD_API.md](DASHBOARD_API.md)

**Key Features:**
- Real-time statistics (products, batches, manufacturers, receivings)
- Expiring batch monitoring with customizable timeframes
- Recent additions tracking (manufacturers, products)
- Multiple visualization types for charts and graphs
- Inventory distribution by location

**Available Visualizations:**
- `overview` - High-level system overview
- `expiry_timeline` - Batches grouped by expiry ranges
- `product_categories` - Product count by category
- `receiving_status` - Receiving records by status
- `batch_status` - Batches by status
- `inventory_by_location` - Inventory by warehouse location

---

## Pagination

All list endpoints use **cursor pagination** for optimal performance.

### Query Parameters

| Parameter | Type | Default | Range | Description |
|-----------|------|---------|-------|-------------|
| `per_page` | integer | 25 | 1-100 | Items per page |
| `cursor` | string | null | - | Cursor from previous response |

### Example

```bash
# First page (default 25 items)
GET /api/products

# Custom page size
GET /api/products?per_page=50

# Next page
GET /api/products?cursor=eyJuYW1lIjoiQUJDI...
```

---

## Standard Response Format

### Success Response
```json
{
  "data": {
    "id": 1,
    "name": "Example",
    ...
  }
}
```

### List Response (Paginated)
```json
{
  "data": [...],
  "meta": {
    "path": "http://api/resource",
    "per_page": 25,
    "next_cursor": "...",
    "prev_cursor": null
  },
  "links": {
    "first": "...",
    "last": null,
    "prev": null,
    "next": "..."
  }
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

---

## Status Codes

| Code | Meaning | When |
|------|---------|------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Internal error |

---

## Complete Integration Example

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"quality@nikora.ge","password":"password123"}' \
  | jq -r '.access_token')

# 2. Create Manufacturer
MANUFACTURER_ID=$(curl -s -X POST http://localhost/api/manufacturers \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Georgian Dairy LLC",
    "short_name": "GD",
    "country": "Georgia",
    "region": "Tbilisi",
    "city": "Tbilisi"
  }' | jq -r '.data.id')

# 3. Create Product
PRODUCT_ID=$(curl -s -X POST http://localhost/api/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Fresh Milk",
    "sku": "SKU-MILK-001",
    "category": "Dairy",
    "unit": "l",
    "origin_type": "local",
    "country_of_origin": "Georgia",
    "storage_temp_min": 0,
    "storage_temp_max": 4,
    "shelf_life_days": 7,
    "inventory_policy": "fefo",
    "manufacturer_id": '$MANUFACTURER_ID'
  }' | jq -r '.data.id')

# 4. Create Warehouse Location
LOCATION_ID=$(curl -s -X POST http://localhost/api/warehouse-locations \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Cold Storage A",
    "code": "CS-A-001",
    "type": "storage_unit",
    "temp_min": 0,
    "temp_max": 4,
    "has_sensor": true
  }' | jq -r '.data.id')

# 5. Create Batch
BATCH_ID=$(curl -s -X POST http://localhost/api/batches \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "batch_number": "BATCH-001",
    "local_production_number": "LOC-123456",
    "production_date": "2026-01-01",
    "expiry_date": "2026-07-01",
    "quantity": 500,
    "unit": "l",
    "product_id": '$PRODUCT_ID',
    "warehouse_location_id": '$LOCATION_ID',
    "receiving_temperature": 2.5,
    "status": "pending"
  }' | jq -r '.data.id')

# 6. Create Receiving Record
RECEIVING_ID=$(curl -s -X POST http://localhost/api/receivings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "batch_id": '$BATCH_ID',
    "warehouse_location_id": '$LOCATION_ID',
    "received_by_user_id": 1,
    "receipt_datetime": "2026-01-05T10:30:00",
    "received_quantity": 500,
    "unit": "l",
    "recorded_temperature": 2.5,
    "temperature_compliant": true,
    "packaging_condition": "excellent",
    "status": "pending"
  }' | jq -r '.data.id')

# 7. Accept Receiving
curl -X PUT http://localhost/api/receivings/$RECEIVING_ID \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "accepted",
    "documents_verified": true
  }' | jq

# 8. View Batch with Relationships
curl -X GET http://localhost/api/batches/$BATCH_ID \
  -H "Authorization: Bearer $TOKEN" | jq
```

---

## Role-Based Access

| Role | Manufacturers | Products | Batches | Locations | Receivings |
|------|---------------|----------|---------|-----------|------------|
| **Recall Admin** | Full CRUD | Full CRUD | Full CRUD | Full CRUD | Full CRUD |
| **Quality Manager** | Full CRUD | Full CRUD | Full CRUD | Full CRUD | Full CRUD |
| **Branch Manager** | View Own | View Own | View Own | View Own | View Own |
| **Warehouse Operator** | View | View | View + Update | View | View + Create |
| **Auditor** | View | View | View | View | View |

---

## Module Integration Flow

```
Manufacturer → Product → Batch → Receiving → Warehouse Location
     ↓            ↓         ↓         ↓              ↓
  (supplies) (cataloged) (tracked) (received) (stored at)
```

**Example Flow:**
1. Create manufacturer (supplier)
2. Create product (what is being tracked)
3. Create warehouse location (where it will be stored)
4. Create batch (specific lot/shipment of the product)
5. Create receiving record (document receipt and quality)
6. Assign batch to location
7. Track quantity consumption
8. Monitor expiry dates

---

## Testing

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/pest tests/Feature/Api/BatchTest.php

# Run with coverage
./vendor/bin/pest --coverage
```

**Current Stats:**
- ✅ 416 tests
- ✅ 1650 assertions
- ✅ 100% coverage

---

## Resources

### API Documentation
- [Full README](README.md)
- [API Authentication](API_AUTHENTICATION.md)
- [User Management Guide](USER_MANAGEMENT.md)
- [Dashboard API](DASHBOARD_API.md)
- [Manufacturer API](MANUFACTURER_API.md)
- [Product API](PRODUCT_API.md)
- [Batch API](BATCH_API.md)
- [Warehouse Location API](WAREHOUSE_LOCATION_API.md)
- [Receiving API](RECEIVING_API.md)

### Development
- [Project Changelog](PROJECT_CHANGELOG.md)
- [Coding Standards](.cursor/rules/laravel-boost.mdc)
