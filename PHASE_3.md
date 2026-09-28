# Phase 3 — Authentication, Users, Roles, Permissions & Departments

Phase 3 is built on the uploaded Phase 2 Sajili codebase.

## Scope

- Tenant-scoped authentication
- Secure PHP sessions
- Login/logout
- JSON API login
- `/api/v1/auth/me`
- Tenant-bound sessions
- Password hashing and rehash support
- User accounts
- Departments
- Roles
- Permissions
- User-role mapping
- Role-permission mapping
- Department HOD mapping
- Permission middleware
- CSRF protection for state-changing session requests
- Basic tenant administration dashboard
- CLI bootstrap commands

## Security model

A session is bound to the current organization's `public_id`.

The application does not trust an `organization_id` submitted by a browser.

Tenant identity is established first:

```text
Request Host
   ↓
TenantResolver
   ↓
TenantContext
   ↓
Session + Authentication
   ↓
User
   ↓
Roles
   ↓
Permissions
```

A user session from one organization is rejected when presented to another organization.

## Database

Tenant migrations:

```text
0002_create_departments.sql
0003_create_users.sql
0004_create_permissions.sql
0005_create_roles.sql
0006_create_department_hods.sql
```

No database ENUMs are used.

## Bootstrap an organization

After Phase 2 organization creation:

```bash
php bin/console migrate:tenant --organization=1
php bin/console roles:seed --organization=1
```

Create a department:

```bash
php bin/console department:create  --organization=1  --name="Information Technology"  --slug=it
```

Create the first administrator:

```bash
php bin/console user:create  --organization=1  --first-name="Jane"  --last-name="Wanjiku" --email="jane@example.com"  --password="21082108"  --role=it-admin --department=1
```

Assign a HOD:

```bash
php bin/console department:set-hod \
  --organization=1 \
  --department=1 \
  --user=1
```

For production, bootstrap passwords should be supplied through a secure secret mechanism rather than shell history.

## Authentication API

```http
POST /api/v1/auth/login
GET  /api/v1/auth/me
POST /api/v1/auth/logout
```

Login accepts either form data or JSON.

Example:

```json
{
  "email": "jane@example.com",
  "password": "ChangeMe12345!"
}
```

Successful login returns a CSRF token for subsequent state-changing browser/API requests.

Use:

```http
X-CSRF-Token: <token>
```

for state-changing API calls using the session cookie.

## Authorization

Permission middleware can be attached to routes using:

```php
'permission:users.view'
```

Examples:

```text
users.view
users.create
users.update
users.deactivate

departments.view
departments.manage
departments.assign_hod

roles.view
roles.manage

employees.view
employees.create
employees.update
employees.import

onboarding.view
onboarding.create
onboarding.sign
onboarding.approve

assets.view
assets.create
assets.assign
assets.return
```

The permission list is data-driven. Business modules should check permissions rather than hardcoding job titles.

## Important distinction

An Employee is not automatically a User.

An HRMS employee record may exist without a portal account.

When portal access is required, a User can later be linked to the Employee.

This separation is required for the Phase 4 HRMS/API/CSV import architecture.

## HOD model

The current structure supports one HOD per department:

```text
Department
   ↓
department_hods
   ↓
User
```

The user should also have the `hod` role when appropriate.

Later onboarding logic can resolve the employee's department and retrieve its HOD automatically.

## Basic administration UI

Tenant administrators can use:

```text
/login
/admin
```

The dashboard is intentionally minimal in Phase 3. The larger administration UI should be developed after the employee and onboarding workflows are established.

## API endpoints currently included

```text
GET    /api/v1/admin/users
POST   /api/v1/admin/users
PATCH  /api/v1/admin/users/{userId}/status

GET    /api/v1/admin/departments
POST   /api/v1/admin/departments
POST   /api/v1/admin/departments/{departmentId}/hod

GET    /api/v1/admin/roles
POST   /api/v1/admin/roles
```

All administration endpoints require authentication and the relevant permission.

## Architecture

```text
Controller
    ↓
Service
    ↓
Repository
    ↓
TenantContext
    ↓
Tenant Database
```

Controllers should not contain SQL.

Repositories should not contain business workflow rules.

Services should own validation and business rules.

Views should contain presentation only.

## Next phase

Phase 4 should build:

- Employees
- Employee profiles
- HRMS API integration abstraction
- CSV/Excel import pipeline
- Import validation
- Import history
- External employee IDs
- Employee/department mapping
- Employee portal identity linking
