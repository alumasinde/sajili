# Database migrations

Phase 2 separates the platform database from tenant data.

## Platform database

`database/migrations/platform/` contains platform-control-plane tables:

- organizations
- organization_domains
- platform_migrations

The platform database should contain only SaaS/control-plane data.

## Tenant database

`database/migrations/tenant/` is applied to the shared tenant database and later to each dedicated tenant database.

Tenant-owned business tables will be added in later phases.

## Rules

- Use numbered migration filenames.
- Never edit an applied production migration.
- Add a new migration for schema changes.
- Use InnoDB and utf8mb4.
- Do not use MySQL/MariaDB ENUM.
- Tenant-owned tables must carry `organization_id` when stored in the shared database.
- Never trust `organization_id` from browser input; derive it from `TenantContext`.
- Platform tables must never be exposed through tenant database connections.
