# CARREFOUR Supply Chain Management System

Enterprise-grade Product Catalog and Inventory Traceability System with role-based access control, batch tracking, temperature monitoring, and compliance workflows.

## Features

### ✅ Implemented Modules

1. **Authentication & Authorization**
   - JWT-based authentication
   - Role-Based Access Control (RBAC)
   - 5 predefined roles with granular permissions

2. **Manufacturer Management**
   - Complete CRUD API
   - Geographic tracking
   - Legal information management
   - 📚 [View Documentation](MANUFACTURER_API.md)

3. **Product Catalog**
   - Comprehensive product metadata
   - Support for local and imported products
   - Temperature range tracking
   - Allergen and compliance management
   - FIFO/FEFO inventory policies
   - 📚 [View Documentation](PRODUCT_API.md)

4. **Batch/Lot Management** 🆕
   - Full traceability for individual batches
   - Local and imported batch tracking
   - Quantity and consumption tracking
   - Status management (pending, received, in_storage, blocked, recalled, etc.)
   - Temperature and movement history
   - 📚 [View Documentation](BATCH_API.md)

5. **Warehouse & Location Structure** 🆕
   - Hierarchical location management (unlimited depth)
   - Temperature-controlled zones
   - Sensor support for IoT integration
   - Inspection frequency scheduling
   - Responsible user assignment
   - 📚 [View Documentation](WAREHOUSE_LOCATION_API.md)

6. **Branch Management**
   - Branch hierarchy
   - User assignment to branches

7. **Recall Management** (Basic)
   - Recall workflow structure
   - Approval system

### 🚧 Planned Modules

8. Receiving Process
9. Temperature Monitoring
10. Movement Traceability
11. Expiry Management
12. Audit & Checklists
13. Document Management
14. Analytics Dashboard

## Quick Start

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

### Run Tests

```bash
composer test
```

### Test Results

- Framework: **Pest PHP**
- Test coverage: **100%**
- Type coverage: **100%** (PHPStan Level Max)
- Total tests: **335 passing**
- Total assertions: **1203**

## API Endpoints

### Authentication
```bash
POST   /api/auth/login      # Login
POST   /api/auth/logout     # Logout
POST   /api/auth/refresh    # Refresh token
GET    /api/auth/me         # Get current user
```

### Manufacturers
```bash
GET    /api/manufacturers           # List manufacturers
POST   /api/manufacturers           # Create manufacturer
GET    /api/manufacturers/{id}      # View manufacturer
PUT    /api/manufacturers/{id}      # Update manufacturer
DELETE /api/manufacturers/{id}      # Delete manufacturer
```

### Products
```bash
GET    /api/products                # List products
POST   /api/products                # Create product
GET    /api/products/{id}           # View product
PUT    /api/products/{id}           # Update product
DELETE /api/products/{id}           # Delete product
```

### Batches
```bash
GET    /api/batches                 # List batches
POST   /api/batches                 # Create batch
GET    /api/batches/{id}            # View batch
PUT    /api/batches/{id}            # Update batch
DELETE /api/batches/{id}            # Delete batch
```

### Warehouse Locations
```bash
GET    /api/warehouse-locations           # List locations
POST   /api/warehouse-locations           # Create location
GET    /api/warehouse-locations/{id}      # View location
PUT    /api/warehouse-locations/{id}      # Update location
DELETE /api/warehouse-locations/{id}      # Delete location
```

### Roles & Permissions
```bash
GET    /api/roles                   # List roles
GET    /api/roles/{id}              # View role
GET    /api/permissions             # List permissions
POST   /api/users/{user}/roles      # Assign role
DELETE /api/users/{user}/roles/{id} # Remove role
```

## Technology Stack

- **Framework:** Laravel 13.x
- **PHP:** 8.4+
- **Database:** PostgreSQL / MySQL
- **Cache:** Redis (recommended)
- **Testing:** Pest PHP
- **Type Checking:** PHPStan Level Max
- **Code Style:** Laravel Pint
- **Code Quality:** Rector
- **Admin Panel:** Filament 3.x

## Additional Tools

- **PHPStan** - Static analysis
- **Rector** - Automated refactoring
- **Laravel Pint** - Code formatting

## Documentation

- 📚 [API Quick Reference](API_QUICK_REFERENCE.md)
- 📚 [Receiving API Documentation](RECEIVING_API.md)
- 📚 [Batch/Lot API Documentation](BATCH_API.md)
- 📚 [Warehouse Location API Documentation](WAREHOUSE_LOCATION_API.md)
- 📚 [Product API Documentation](PRODUCT_API.md)
- 📚 [Manufacturer API Documentation](MANUFACTURER_API.md)
- 📚 [Authentication Guide](API_AUTHENTICATION.md)
- 📚 [Project Changelog](PROJECT_CHANGELOG.md)
- 📚 [Coding Standards](.cursor/rules/laravel-boost.mdc)

## Development Standards

All code must meet:
- ✅ 100% test coverage
- ✅ 100% type coverage
- ✅ Laravel Pint formatting
- ✅ Action pattern for business logic
- ✅ Policy-based authorization
- ✅ Cursor pagination for large datasets

## Architecture Patterns

- **Action Pattern** - Business logic in dedicated classes
- **Repository Pattern** - Eloquent models as data layer
- **Form Request Validation** - Dedicated validation classes
- **API Resources** - Consistent JSON responses
- **Policy-based Authorization** - Permission-driven access control

## Version

- **Laravel:** 13.x
- **API Version:** 1.0
- **Project Version:** 1.2.0
