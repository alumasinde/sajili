# Database migrations

Phase 1 intentionally contains only the migrations tracking table.

Tenant and business tables will be introduced in later phases after the foundation is stable.

Migration rules:

- Use numbered migration filenames.
- Never edit an already-applied production migration.
- Add a new migration for schema changes.
- Use InnoDB.
- Use `utf8mb4`.
- Do not use MySQL/MariaDB `ENUM`.
- Add `organization_id` to tenant-owned tables when those modules are introduced.
