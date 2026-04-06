# NIKORA Supply Chain Management System - Changelog

## Project Overview

Scalable Product Catalog and Inventory Traceability System with role-based access control, batch tracking, temperature monitoring, and compliance workflows.

---

## [Unreleased]

### In Progress
- Receiving Process Module
- Temperature Monitoring System
- Movement Traceability
- Expiry Management
- Recall/Withdrawal Module
- Audit & Checklists
- Document Management
- Dashboard & Analytics

---

## [v1.2.0] - 2026-04-06

### Added - Batch/Lot Management & Warehouse Structure Modules ✅

**Batch/Lot Management:**

*Database:*
- Batches table with comprehensive traceability
- Support for local and imported batches
- Quantity tracking (total and remaining)
- Multiple status states (pending, received, in_storage, in_transit, blocked, recalled, expired, disposed)
- Temperature and movement history (JSON)
- Linked documents support
- Indexes for optimized queries

*Models & Business Logic:*
- `Batch` model with full type safety
- Scopes: `active()`, `expired()`, `expiringWithinDays()`, `blocked()`, `recalled()`, `ordered()`
- Helper methods: `isExpired()`, `daysUntilExpiry()`, `isFullyConsumed()`, `hasQuantityAvailable()`, `isLocal()`, `isImported()`
- Relationships to Product, WarehouseLocation, and User (received by)
- Action classes: `CreateBatchAction`, `UpdateBatchAction`, `DeleteBatchAction`

*API Endpoints:*
- `GET /api/batches` - List batches with cursor pagination
- `POST /api/batches` - Create new batch
- `GET /api/batches/{id}` - View batch details
- `PUT /api/batches/{id}` - Update batch
- `DELETE /api/batches/{id}` - Delete batch

*Features:*
- Automatic `remaining_quantity` initialization
- Production and expiry date validation
- Dynamic remaining quantity validation against total quantity
- Batch number uniqueness
- Temperature history tracking
- Movement history tracking
- JSON fields for flexible metadata

**Warehouse & Location Structure:**

*Database:*
- Warehouse locations table with hierarchical structure
- Self-referencing parent-child relationships (unlimited depth)
- 5 location types: central_warehouse, regional_warehouse, branch, storage_unit, zone
- Temperature specifications per location
- Sensor support flag
- Inspection frequency scheduling
- Responsible user assignment

*Models & Business Logic:*
- `WarehouseLocation` model with full type safety
- Scopes: `active()`, `ordered()`, `roots()`
- Helper methods: `hasTemperatureControl()`, `isTemperatureInRange()`
- Hierarchical relationships: parent, children
- Relationship to responsible user
- Action classes: `CreateWarehouseLocationAction`, `UpdateWarehouseLocationAction`, `DeleteWarehouseLocationAction`

*API Endpoints:*
- `GET /api/warehouse-locations` - List locations with cursor pagination
- `POST /api/warehouse-locations` - Create new location
- `GET /api/warehouse-locations/{id}` - View location details
- `PUT /api/warehouse-locations/{id}` - Update location
- `DELETE /api/warehouse-locations/{id}` - Delete location

*Features:*
- Hierarchical structure (unlimited depth)
- Temperature range validation
- Unique location codes
- Inspection frequency: 1-168 hours
- Sensor integration support
- Cascade null on parent deletion

**Testing:**
- 116 new comprehensive tests (27 Batch API tests, 28 Warehouse Location API tests, plus Action/Model/Policy tests)
- 100% code coverage
- 100% type coverage (PHPStan Level Max)

**Documentation:**
- Complete API documentation in `BATCH_API.md`
- Complete API documentation in `WAREHOUSE_LOCATION_API.md`
- Updated README with new endpoints and features

**Quality Metrics:**
- ✅ 335 total tests passed (up from 219)
- ✅ 1203 assertions (up from 903)
- ✅ 100% type coverage
- ✅ 100% code coverage
- ✅ All linting passed

---

## [v1.1.0] - 2026-04-06

### Added - Product Catalog Module ✅

**Database:**
- Products table with comprehensive schema
- Support for local and imported products
- Temperature range tracking
- Allergen and risk indicator management
- JSON fields for flexible metadata storage
- Indexes for optimized queries

**Models & Business Logic:**
- `Product` model with full type safety
- Scopes: `active()`, `local()`, `imported()`, `ordered()`
- Helper methods: `isLocal()`, `isImported()`, `hasTemperatureRequirement()`, `isTemperatureInRange()`
- Relationship to Manufacturer model
- Action classes: `CreateProductAction`, `UpdateProductAction`, `DeleteProductAction`

**API Endpoints:**
- `GET /api/products` - List products with cursor pagination
- `POST /api/products` - Create new product
- `GET /api/products/{id}` - View product details
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

**Features:**
- Cursor pagination (1-100 items per page)
- Eager loading of manufacturer relationship
- Comprehensive validation with auto-trimming
- Policy-based authorization
- Support for FIFO and FEFO inventory policies
- Temperature range validation
- Unique SKU and barcode constraints

**Testing:**
- 63 comprehensive tests
- 100% code coverage
- 100% type coverage (PHPStan Level Max)
- API endpoint tests
- Action tests
- Model tests
- Policy tests
- Factory with state methods

**Documentation:**
- Complete API documentation in `PRODUCT_API.md`
- Code examples and usage patterns
- Integration guidelines

**Quality Metrics:**
- ✅ 219 total tests passed
- ✅ 903 assertions
- ✅ 100% type coverage
- ✅ 100% code coverage
- ✅ All linting passed

---

## [v1.0.0] - 2026-04-03

### Added - Foundation & Authentication

**Core Infrastructure:**
- Laravel 13.x framework setup
- JWT authentication system
- Role-Based Access Control (RBAC) with Spatie Permission
- Database migrations foundation

**User Management:**
- User model with branch assignment
- Authentication endpoints (login, logout, refresh, me)
- Password hashing and security

**Role & Permission System:**
- 5 predefined roles: Recall Admin, Quality Manager, Branch Manager, Warehouse Operator, Auditor
- Granular permissions per resource
- Permission seeding system
- API endpoints for role and permission management

**Branch Management:**
- Branch model and CRUD operations
- Branch assignment to users
- Hierarchical organization support

**Manufacturer Management:**
- Complete CRUD API for manufacturers
- Cursor pagination
- Geographic tracking (country, region, city)
- Legal information management
- 153 tests with 100% coverage
- Complete documentation in `MANUFACTURER_API.md`

**Recall Management:**
- Recall model and basic structure
- Approval workflow
- Status tracking
- Filament admin interface

**Testing & Quality:**
- Pest PHP testing framework
- 100% test coverage requirement
- 100% type coverage requirement (PHPStan Level Max)
- Laravel Pint for code style
- Rector for code quality

**Documentation:**
- `README.md` - Project overview
- `API_AUTHENTICATION.md` - Authentication guide
- `MANUFACTURER_API.md` - Manufacturer API documentation
- Coding standards in `.cursor/rules/laravel-boost.mdc`

---

## Technical Stack

- **Framework:** Laravel 13.x
- **PHP:** 8.4+
- **Database:** PostgreSQL (recommended) / MySQL
- **Cache:** Redis (recommended)
- **Testing:** Pest PHP
- **Type Checking:** PHPStan Level Max
- **Code Style:** Laravel Pint
- **Code Quality:** Rector
- **Authentication:** JWT (tymon/jwt-auth)
- **Permissions:** Spatie Laravel Permission
- **Admin Panel:** Filament 3.x

---

## Quality Standards

All code must meet these standards:
- ✅ 100% test coverage
- ✅ 100% type coverage (PHPStan Level Max)
- ✅ Laravel Pint formatting
- ✅ Rector quality checks
- ✅ Action pattern for business logic
- ✅ Policy-based authorization
- ✅ Form Request validation
- ✅ API Resources for responses
- ✅ Cursor pagination for large datasets

---

## Module Status

| Module | Status | Tests | Coverage | Documentation |
|--------|--------|-------|----------|---------------|
| Authentication & Users | ✅ Complete | ✅ 100% | ✅ 100% | ✅ API_AUTHENTICATION.md |
| Roles & Permissions | ✅ Complete | ✅ 100% | ✅ 100% | ✅ Documented |
| Branches | ✅ Complete | ✅ 100% | ✅ 100% | ✅ Documented |
| Manufacturers | ✅ Complete | ✅ 100% | ✅ 100% | ✅ MANUFACTURER_API.md |
| **Products** | ✅ Complete | ✅ 100% | ✅ 100% | ✅ PRODUCT_API.md |
| **Batches/Lots** | ✅ Complete | ✅ 100% | ✅ 100% | ✅ BATCH_API.md |
| **Warehouse Locations** | ✅ Complete | ✅ 100% | ✅ 100% | ✅ WAREHOUSE_LOCATION_API.md |
| Receiving Process | 🚧 In Progress | - | - | - |
| Warehouse & Locations | ⏸️ Planned | - | - | - |
| Receiving Process | ⏸️ Planned | - | - | - |
| Temperature Monitoring | ⏸️ Planned | - | - | - |
| Movement Traceability | ⏸️ Planned | - | - | - |
| Expiry Management | ⏸️ Planned | - | - | - |
| Recall/Withdrawal | 🚧 In Progress | - | - | - |
| Audit & Checklists | ⏸️ Planned | - | - | - |
| Document Management | ⏸️ Planned | - | - | - |
| Dashboard & Analytics | ⏸️ Planned | - | - | - |

---

## API Versioning

Current API Version: **v1.0**

All API endpoints are prefixed with `/api/`

---

## Getting Started

### Installation

```bash
# Clone repository
git clone <repository-url>
cd nikora-demo

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Run tests
composer test
```

### API Testing

```bash
# Login as Recall Admin (full access)
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nikora.ge",
    "password": "password123"
  }'

# Use the access_token in subsequent requests
curl -X GET http://localhost/api/products \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Contributing

### Development Workflow

1. Read coding standards in `.cursor/rules/laravel-boost.mdc`
2. Create feature branch
3. Implement following Action pattern
4. Write tests (aim for 100% coverage)
5. Run `composer test` - all must pass
6. Submit pull request

### Code Standards

- Follow Laravel 13 conventions
- Use Action pattern for business logic
- Keep controllers thin
- Add comprehensive tests
- Maintain 100% type and test coverage
- Use meaningful names
- Avoid unnecessary comments

---

## License

Proprietary - NIKORA Internal Use Only

---

## Support

For questions or issues, contact the development team.
