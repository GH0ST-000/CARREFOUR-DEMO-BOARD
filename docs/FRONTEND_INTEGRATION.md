# Frontend Integration Guide (Products, Batches, Documents, Traceability)

This document describes the API changes added for:

- Product lifecycle dates (production + shelf life + computed expiration)
- Safety/compliance document uploads (S3-backed storage)
- Batch (lot) management (product → many batches)
- Product traceability across branches (transfer history + per-branch batch inventory)

All endpoints below are **under** `/api` and require **JWT auth** unless noted otherwise.

---

## Auth

Add these headers to requests:

- `Authorization: Bearer <JWT>`
- `Accept: application/json`

Auth endpoints:

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/refresh`
- `POST /api/auth/logout`

---

## Date/time rules (timezone-safe)

- **Date fields**: `YYYY-MM-DD` (e.g. `2026-01-01`)
- **Datetime fields**: ISO-8601 with timezone (e.g. `2026-02-01T10:00:00+00:00`)

Validation rules enforced server-side:

- **Product**: `expiration_date` is not accepted as input; it is computed.
  - `expiration_date = production_date + shelf_life_days`
  - If `production_date` is `null`, `expiration_date` becomes `null`.
- **Batch**:
  - `production_date` is required and must be `<= today`.
  - `expiry_date` must be `>= production_date` when provided.
  - If `expiry_date` is omitted, the server calculates it using the related product’s `shelf_life_days`.

---

## Pagination (cursor pagination)

List endpoints use cursor pagination.

Typical query params:

- `per_page` (clamped server-side)

Typical response shape:

```json
{
  "data": [/* items */],
  "meta": { "next_cursor": "..." },
  "links": { "next": "..." }
}
```

---

## Products

### Create product

`POST /api/products`

Payload (JSON):

```json
{
  "name": "Milk 3.2%",
  "sku": "SKU-001",
  "barcode": "1234567890123",
  "qr_code": "QR-001",
  "brand": "Brand",
  "category": "Dairy",
  "unit": "kg",
  "origin_type": "local",
  "country_of_origin": "Georgia",
  "storage_temp_min": 0,
  "storage_temp_max": 4,
  "production_date": "2026-01-01",
  "shelf_life_days": 30,
  "inventory_policy": "fefo",
  "allergens": ["milk"],
  "risk_indicators": ["perishable"],
  "required_documents": ["quality_certificate"],
  "manufacturer_id": 1,
  "is_active": true
}
```

Response `data` includes:

- `production_date` (nullable)
- `shelf_life_days`
- `expiration_date` (nullable, computed)

### Update product

`PUT /api/products/{productId}`

Payload is the same as create, but fields are `sometimes`.

### List / get / delete

- `GET /api/products?per_page=25`
- `GET /api/products/{productId}`
- `DELETE /api/products/{productId}`

---

## Batches (lots)

### Create batch

`POST /api/batches`

Payload (JSON):

```json
{
  "batch_number": "LOT-001",
  "production_date": "2026-01-01",
  "expiry_date": "2026-01-31",
  "quantity": 100.5,
  "unit": "kg",
  "product_id": 1,
  "linked_documents": ["invoice.pdf"]
}
```

Notes:

- `expiry_date` is **optional**. If omitted, server calculates it.

### Update batch

`PUT /api/batches/{batchId}`

Supports partial updates. If you update `production_date` (or change `product_id`) and do **not** supply `expiry_date`, the server recalculates `expiry_date`.

### List / get / delete

- `GET /api/batches?per_page=25`
- `GET /api/batches/{batchId}`
- `DELETE /api/batches/{batchId}`

---

## Documents (Laravel Storage, multi-upload)

Documents are stored using Laravel filesystem (the active disk is whatever the backend config sets, e.g. `local` or **S3**).

Supported file types:

- `application/pdf`
- `image/jpeg`, `image/png`, `image/webp`

### Upload a product document

`POST /api/products/{productId}/documents`

Request type: `multipart/form-data`

Fields:

- `type` (string, required): examples `certificate`, `safety_document`
- `description` (string, optional)
- `external_reference` (string, optional)
- `files[]` (file[], required): upload 1..10 files

Frontend example (JS/TS):

```ts
const form = new FormData();
form.append("type", "certificate");
form.append("description", "QA certificate");
for (const f of files) form.append("files[]", f); // File[]

const res = await fetch(`/api/products/${productId}/documents`, {
  method: "POST",
  headers: {
    Authorization: `Bearer ${token}`,
    // Do NOT set Content-Type manually for FormData
  },
  body: form,
});

const json = await res.json();
```

Returns: `201 Created`

### Upload a batch document

`POST /api/batches/{batchId}/documents`

Same format/fields as product document upload.

Returns: `201 Created`

### Document response shape

```json
{
  "data": [
    {
      "id": 1,
      "product_id": 10,
      "batch_id": null,
      "type": "certificate",
      "description": "QA certificate",
      "source": "uploaded",
      "external_reference": "EXT-1",
      "original_name": "certificate.pdf",
      "mime_type": "application/pdf",
      "size_bytes": 102400,
      "disk": "s3",
      "path": "products/10/documents/<uuid>.pdf",
      "url": "https://...",
      "uploaded_by_user_id": 123,
      "uploaded_at": "2026-04-14T12:00:00+00:00",
      "created_at": "2026-04-14T12:00:00+00:00"
    }
  ]
}
```

---

## Traceability (branch transfers)

Traceability is represented as:

- **Immutable history**: `product_traceability_events`
- **Current per-branch batch inventory**: `branch_product_batches`

### Retrieve traceability history

`GET /api/products/{productId}/traceability?per_page=50`

Returns a cursor-paginated list of transfer events (most recent first).

### Assign/transfer product to a branch

`POST /api/products/{productId}/branch-assignments`

Payload (JSON):

```json
{
  "to_branch_id": 2,
  "from_branch_id": 1,
  "batch_id": 10,
  "quantity": 5,
  "transferred_at": "2026-02-01T10:00:00+00:00",
  "responsible_user_id": 123
}
```

Rules:

- If `batch_id` is **omitted**, you must provide `batch_number`.
- If `transferred_at` is omitted, server uses `now()`.

Returns: `201 Created`

---

## Error handling

Validation failures return `422 Unprocessable Entity`:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

Authorization failures return `403 Forbidden`.

