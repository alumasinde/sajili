# Phase 2 — Multi-Tenancy & Company Setup

Phase 2 establishes the control plane and tenant resolution layer.

## Architecture

```text
                    Platform DB
                         │
              ┌──────────┴──────────┐
              │                     │
        organizations       organization_domains
              │                     │
              └──────────┬──────────┘
                         │ resolve host
                         ▼
                  TenantResolver
                         │
                         ▼
                   TenantContext
                    /          \
                   /            \
              shared          dedicated
                DB                DB
```

### Shared mode

All companies use the same tenant database, but tenant-owned records are isolated using `organization_id`.

### Dedicated mode

A company has its own MySQL database. Its connection configuration is encrypted in the platform database using AES-256-GCM and the application `APP_KEY`.

The application never accepts a database connection string from the browser.

## Host resolution

A request such as:

```text
https://it.gleenairobi.co.ke/api/v1/tenant/context
```

is resolved by:

```text
HTTP Host
  ↓
organization_domains.domain
  ↓
organization_id
  ↓
organizations
  ↓
TenantContext
```

The browser does not provide `organization_id` for tenant selection.

## Platform database

The platform database stores only control-plane information such as:

- organization identity
- company slug
- company status
- domain mappings
- database mode
- encrypted dedicated database configuration

Do not place employee, asset or onboarding data in the platform database.

## Commands

Generate a key:

```bash
php bin/console key:generate
```

Apply platform migrations:

```bash
php bin/console migrate:platform
```

Create a shared-database organization:

```bash
php bin/console org:create --name="Glee Nairobi" --slug=glee-nairobi --domain=it.gleenairobi.co.ke --database-mode=shared
```

Create a dedicated-database organization:

```bash
php bin/console org:create --name="Example" --slug=example --domain=it.example.com --database-mode=dedicated --database-host=127.0.0.1 --database-port=3306 --database-name=example_onboarding --database-username=alumasinde --database-password="21082108"
```

Apply tenant migrations:

```bash
php bin/console migrate:tenant --organization=1
```

Add another company domain:

```bash
php bin/console org:add-domain --organization=1 --domain=hr.gleenairobi.co.ke
```

## API

Public application health:

```http
GET /api/v1/health
```

Tenant context test:

```http
GET /api/v1/tenant/context
Host: it.gleenairobi.co.ke
```

Successful response contains only safe organization metadata. Database credentials are never returned.

## Security rules

1. Tenant resolution happens from the request host, not a client-supplied organization ID.
2. Dedicated connection credentials are encrypted at rest.
3. `APP_KEY` must be stored outside source control.
4. Never log decrypted database credentials.
5. Never return database configuration through API responses.
6. Shared tenant queries must derive `organization_id` from `TenantContext`.
7. Tenant-owned tables must have foreign keys and tenant-scoped unique indexes where appropriate.
8. Platform and tenant database connections are separate.
9. A tenant cannot select another tenant's connection.
10. Production dedicated database credentials should be rotated through an administrative process rather than edited directly in the database.
