# Employee Onboarding Platform — Phase 1

This is the Phase 1 foundation for the Employee Onboarding & Asset Management Platform.

## What Phase 1 provides

- PHP 8.3+ project structure
- Composer PSR-4 autoloading
- `.env` configuration
- MySQL PDO database layer
- Transaction support
- Clean URL routing
- `/api/v1` routing
- HTTP request/response abstractions
- Centralized logging
- Basic security headers
- Secure session configuration
- CSRF utility
- Migration foundation
- PHPUnit setup
- Minimal health endpoints
- Enterprise-oriented CSS variables/base styling
- No business modules yet

## Requirements

- PHP 8.3+
- Composer 2+
- MySQL 8+ or MariaDB
- PDO MySQL extension
- PHPUnit through Composer

## Setup

1. Copy the environment file:

```bash
cp .env.example .env
```

2. Create the database:

```sql
CREATE DATABASE onboarding_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

3. Install dependencies:

```bash
composer install
```

4. Run the development server:

```bash
composer serve
```

5. Open:

```text
http://127.0.0.1:8000/
http://127.0.0.1:8000/health
http://127.0.0.1:8000/api/v1/health
```

## Architecture direction

The application is a modular monolith.

```text
HTTP
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

Phase 1 deliberately does not add HRMS, employees, assets, onboarding or signatures. Those are later phases and should be added without breaking the foundation.

## Security principles

- Never trust `organization_id` from browser input.
- Tenant resolution will be introduced before tenant-owned modules.
- Use prepared PDO statements.
- Never store plaintext passwords.
- Keep secrets in `.env`/secret management, never Git.
- Keep private documents outside `public/`.
- Do not expose stack traces in production.
- Do not use database ENUMs.
- Keep controllers thin.
- Do not put SQL in controllers or views.

## Next phase

Phase 2 should introduce:

- Platform database
- Organizations
- Domains
- Tenant resolver
- Tenant context
- Shared database mode
- Dedicated database abstraction
- Encrypted dedicated database credentials
- Tenant isolation tests
