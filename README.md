# Employee Onboarding Platform

PHP 8.3+ modular monolith for employee onboarding, IT asset handover, approvals and digital signatures.

## Current state: Phase 3

This build is based on the latest working `sajili-main.zip` supplied for Phase 3.

Phase 1 foundation has been hardened and Phase 2 adds the multi-tenant control plane.

### Phase 1

- PSR-4 Composer autoloading
- HTTP request/response layer
- Hardened router
- Route groups and middleware
- API versioning at `/api/v1`
- MySQL PDO layer
- Transaction support
- Environment configuration
- Security headers
- Request IDs
- CSRF utility
- Centralized logging
- PHPUnit foundation

### Phase 2

- Platform/control-plane database
- Organization management
- Company domains
- Host-based tenant resolution
- Tenant context
- Shared database mode
- Dedicated database mode
- AES-256-GCM encrypted dedicated database configuration
- Platform and tenant migration separation
- Organization CLI provisioning
- Tenant context API endpoint
- Tenant isolation architecture

## Requirements

- PHP 8.3+
- Composer 2+
- MySQL 8+ or MariaDB 10.6+
- PDO MySQL
- OpenSSL
- XAMPP is fine for local development

## Local setup

Create two databases:

```sql
CREATE DATABASE onboarding_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE DATABASE onboarding_tenants
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Copy `.env.example` to `.env` and configure both databases.

Generate an application key:

```bash
php bin/console key:generate
```

Put the generated value into `APP_KEY` in `.env`.

Install dependencies:

```bash
composer install
```

Run platform migrations:

```bash
php bin/console migrate:platform
```

Create a company using shared tenant storage:

```bash
php bin/console org:create --name="Glee Nairobi" --slug=glee-nairobi --domain=it.gleenairobi.co.ke --database-mode=shared
```

Run tenant migrations:

```bash
php bin/console migrate:tenant --organization=1
```

For local testing, map the company hostname in the Windows hosts file, for example:

```text
127.0.0.1 it.gleenairobi.co.ke
```

Then run:

```bash
composer serve
```

Test:

```text
http://127.0.0.1:8000/
http://127.0.0.1:8000/api/v1/health
http://it.gleenairobi.co.ke:8000/api/v1/tenant/context
```

## Architecture rule

The application is a modular monolith:

```text
Request
  ↓
Router
  ↓
Middleware
  ↓
Controller
  ↓
Service
  ↓
Repository
  ↓
Database
```

Controllers should orchestrate HTTP concerns only. Business rules belong in services. SQL belongs in repositories. Views contain presentation only.

## Multi-tenancy rule

Never trust this from browser input:

```text
organization_id
```

Tenant identity is established by the host/domain and stored in `TenantContext`.

For shared database tables, services/repositories should obtain the organization ID from `TenantContext`.

For dedicated databases, the connection is selected from the server-side organization record after tenant resolution.

## Database separation

```text
                    PLATFORM DB
                         │
                 organizations
                 organization_domains
                         │
                         │ resolve host
                         ▼
                    TenantContext
                      /       \
                     /         \
                SHARED       DEDICATED
                 DB              DB
```

The platform database is never exposed to normal tenant business queries.

## No ENUMs

The project deliberately avoids database `ENUM` columns. Statuses, modes and roles are represented using strings with application-level validation and, where useful, lookup tables.

## Naming

Use:

```text
first_name
last_name
```

not `fullname`.

Migrations are numbered and applied forward. Do not edit an already-applied production migration.

## Phase 3

- Tenant-bound authentication
- Secure sessions
- JSON API login/logout/me
- User accounts
- Departments
- Roles
- Permissions
- User-role mapping
- Role-permission mapping
- Department HOD mapping
- Permission middleware
- CSRF protection
- Basic administration dashboard
- User/department/role administration APIs
- CLI bootstrap commands

## Next phase

Phase 4 will build employees, HRMS/API integration, CSV/Excel imports and the employee identity/linking layer.
