# CARREFOUR Supply Chain Management System

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

## Documentation

- `docs/FRONTEND_INTEGRATION.md` — step-by-step integration guide for product dates, documents (multi-upload + storage URLs), batches, and traceability APIs
