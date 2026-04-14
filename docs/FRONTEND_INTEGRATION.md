# Integration Guide: Product Dates, Documents, Batches, Traceability

This guide explains **what was added**, **how it works**, and **exactly how to integrate it** from a frontend (and, briefly, backend) perspective.

Scope:

- Product lifecycle dates (`production_date`, `shelf_life_days`, computed `expiration_date`)
- Safety/compliance documents stored via **Laravel Storage** (local or cloud disk such as **S3**)
- Batch (lot) management (a product can have many batches)
- Product traceability across branches (immutable history + per-branch batch inventory snapshot)

All API endpoints below are **under** `/api` and require **JWT auth** unless noted otherwise.

---

## 0) What you need before integrating

### Required headers (all protected endpoints)

- `Authorization: Bearer <JWT>`
- `Accept: application/json`

### Permissions (Spatie)

These endpoints reuse existing product/batch permissions:

- Product create/update endpoints require `create_product` / `update_product` (via `ProductPolicy`).
- Batch create/update endpoints require `create_batch` / `update_batch` (via `BatchPolicy`).
- Document uploads are authorized as **updating** the related `Product` / `Batch` (`authorize('update', ...)`).
- Traceability history (`GET .../traceability`) requires `view_product`.
- Branch assignment (`POST .../branch-assignments`) requires `update_product`.

If your UI user cannot upload, verify the role includes `update_product` / `update_batch`.

---

## 1) Backend “map” (where the functionality lives)

If you are extending/maintaining the backend, these are the primary touchpoints:

- **Routes**: `routes/api.php`
  - `POST /api/products/{product}/documents`
  - `POST /api/batches/{batch}/documents`
  - `GET /api/products/{product}/traceability`
  - `POST /api/products/{product}/branch-assignments`
- **Controllers**:
  - `app/Http/Controllers/Api/ProductDocumentController.php`
  - `app/Http/Controllers/Api/BatchDocumentController.php`
  - `app/Http/Controllers/Api/ProductTraceabilityController.php`
- **Validation**:
  - `app/Http/Requests/Api/UploadDocumentRequest.php`
  - `app/Http/Requests/Api/AssignProductToBranchRequest.php`
  - `app/Http/Requests/Api/CreateProductRequest.php` / `UpdateProductRequest.php`
  - `app/Http/Requests/Api/CreateBatchRequest.php` / `UpdateBatchRequest.php`
- **Resources (API JSON shape)**:
  - `app/Http/Resources/DocumentResource.php`
  - `app/Http/Resources/ProductTraceabilityEventResource.php`
  - `app/Http/Resources/ProductResource.php`
- **Models**:
  - `app/Models/Document.php`
  - `app/Models/ProductTraceabilityEvent.php`
  - `app/Models/BranchProductBatch.php`
  - `app/Models/Product.php` (computed expiration)
- **Migrations** (schema):
  - `database/migrations/2026_04_14_000001_add_dates_to_products_table.php`
  - `database/migrations/2026_04_14_000002_create_documents_table.php`
  - `database/migrations/2026_04_14_000003_create_branch_responsible_users_table.php`
  - `database/migrations/2026_04_14_000004_create_branch_product_batches_table.php`
  - `database/migrations/2026_04_14_000005_create_product_traceability_events_table.php`

### Deploy checklist (backend)

1. Run migrations: `php artisan migrate`
2. Configure filesystem disk in `.env` / `config/filesystems.php`:
   - For local dev: `FILESYSTEM_DISK=local` (or whatever your app default is)
   - For production object storage: configure `s3` disk + credentials
3. Ensure the default disk used by uploads matches your intent:
   - Upload code uses `config('filesystems.default')` as the active disk name stored on each `documents.disk` row.

---

## 2) Auth

Auth endpoints:

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/refresh`
- `POST /api/auth/logout`

---

## 3) Date/time rules (timezone-safe)

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

## 4) Pagination (cursor pagination)

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

## 5) Products

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

## 6) Batches (lots)

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

## 7) Documents (Laravel Storage + multi-upload + returned link)

### Storage model (important for frontend expectations)

- Files are stored using Laravel’s `Storage` facade on the **configured default disk** (`config('filesystems.default')`).
- Uploads are written with **`private` visibility** (important for S3: objects are not “public website files” by default).
- The API persists metadata in the `documents` table, including:
  - `disk` (string)
  - `path` (string; stable identifier for the object in storage)
  - `url` (string|null; convenience link for the UI)

`url` generation rules:

- If the filesystem adapter implements `temporaryUrl(...)`, the API uses a **short-lived signed URL** (currently **30 minutes**).
- Otherwise it falls back to `url(...)` (works for public/local setups depending on configuration).
- If URL generation fails (misconfiguration, adapter limitations), `url` may be `null` even though the file exists (`path` is still returned).

Frontend guidance:

- Treat `path` + `disk` as the canonical reference.
- Treat `url` as a convenience for preview/download when present.

### Supported file types

- `application/pdf`
- `image/jpeg`, `image/png`, `image/webp`

### Upload product documents (multi-file)

`POST /api/products/{productId}/documents`

Request type: `multipart/form-data`

Fields:

- `type` (string, required): examples `certificate`, `safety_document`
- `description` (string, optional)
- `source` (string, optional): `uploaded` (default) or `external`
- `external_reference` (string, optional)
- `files[]` (file[], required): **1..10** files per request
  - Each file: **max 20 MB**
  - Allowed mime types: `application/pdf`, `image/jpeg`, `image/png`, `image/webp`

Important: uploads always require at least one file (`files` is required). The `source` / `external_reference` fields are metadata for classification and linking; they do **not** replace file uploads in the current API.

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

### Upload batch documents (multi-file)

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

### Recommended UI flow (documents)

1. Create/select the `product` (or `batch`) first (you need the id).
2. Upload documents with `files[]`.
3. Store returned `data[].id` in your UI state if you need server-side references.
4. For preview:
   - If `url` exists: open it in a new tab / `<iframe>` / `<img>` depending on mime type.
   - If `url` is null: keep the `id` and implement a dedicated download endpoint later (not included here) OR fix storage configuration so URL generation works.

---

## 8) Traceability (branch transfers)

Traceability is represented as:

- **Immutable history**: `product_traceability_events`
- **Current per-branch batch inventory**: `branch_product_batches`

### Branch responsibility users (data model)

There is a pivot table `branch_responsible_users` to support **multiple responsible users per branch** (many-to-many).

Note: there is **no dedicated public API** for managing this pivot in this iteration, and the Filament branch form does not currently expose it either. If you need it in the UI, add either an admin form field or an API endpoint that writes to `branch_responsible_users`.

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

### Recommended UI flow (traceability)

1. User selects destination branch (`to_branch_id`).
2. User selects an existing batch (`batch_id`) OR types a `batch_number` (for external lots / integrations).
3. User enters `quantity` and optional `transferred_at`.
4. After success (`201`), refresh history via `GET /traceability`.

---

## 9) Error handling

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

### Traceability-specific non-validation failures

Some traceability mistakes return **422** with a plain message (not always `errors.*`), for example:

- batch does not belong to the product
- missing `batch_number` when `batch_id` is not provided

Example bodies:

```json
{ "message": "Batch does not belong to product." }
```

```json
{ "message": "batch_number is required when batch_id is not provided." }
```

The frontend should handle `422` generically: show `message` and/or field errors when present.

