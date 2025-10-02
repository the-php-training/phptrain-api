# Tenant Management - Domain Documentation

## Overview

The Tenant Management bounded context implements a multi-tenant architecture following Domain-Driven Design (DDD) and Clean Architecture principles. Each tenant represents an independent organization/institution that uses the platform to manage courses.

## Architecture Layers

### 1. Domain Layer (`app/Domain/Tenant/`)

The core business logic with no external dependencies.

#### Entity: Tenant
**Location**: `app/Domain/Tenant/Entity/Tenant.php`

The Tenant aggregate root encapsulates all business rules and invariants:

```php
// Create a new tenant
$tenant = Tenant::create(
    id: TenantId::generate(),
    name: "Acme University",
    slug: TenantSlug::fromString("acme-university"),
    contactEmail: ContactEmail::fromString("contact@acme.edu"),
    contactPhone: "+1-555-0100"
);

// Activate tenant
$tenant->activate();

// Check platform access
if ($tenant->canAccessPlatform()) {
    // Allow access
}
```

#### Value Objects
- **TenantId**: UUID-based unique identifier
- **TenantSlug**: URL-friendly identifier (e.g., `acme-university`)
- **TenantStatus**: Enum (`active`, `inactive`, `suspended`, `pending`)
- **ContactEmail**: Validated email address

#### Repository Interface
**Location**: `app/Domain/Tenant/Repository/TenantRepositoryInterface.php`

Defines the contract for persistence operations without coupling to specific implementations.

#### Domain Events
**Location**: `app/Domain/Tenant/Event/TenantCreated.php`

Emitted when a new tenant is created, allowing other parts of the system to react.

---

### 2. Application Layer (`app/Application/Tenant/`)

Orchestrates use cases and coordinates domain objects.

#### Commands (CQRS Write Side)

**CreateTenantCommand & Handler**:
```php
$command = new CreateTenantCommand(
    name: "Acme University",
    slug: "acme-university",
    contactEmail: "contact@acme.edu",
    contactPhone: "+1-555-0100"
);

$tenantDTO = $createTenantHandler->handle($command);
```

#### Queries (CQRS Read Side)

**GetTenantQuery & Handler**:
```php
$query = new GetTenantQuery(tenantId: "uuid-here");
$tenantDTO = $getTenantHandler->handle($query);
```

**ListTenantsQuery & Handler**:
```php
$query = new ListTenantsQuery(limit: 20, offset: 0);
$result = $listTenantsHandler->handle($query);
```

#### Event Listeners
**TenantCreatedListener**: Handles side effects when a tenant is created (logging, notifications, etc.)

---

### 3. Infrastructure Layer (`app/Infrastructure/Persistence/`)

Implements technical details for persistence.

#### TenantRepository
**Location**: `app/Infrastructure/Persistence/Repository/TenantRepository.php`

Concrete implementation using Hyperf's Eloquent ORM, translating between domain entities and database models.

#### TenantModel
**Location**: `app/Infrastructure/Persistence/Model/TenantModel.php`

Eloquent model for database interactions.

---

### 4. Presentation Layer (`app/Presentation/Http/Controller/`)

HTTP interface for the Tenant Management API.

#### TenantController
**Location**: `app/Presentation/Http/Controller/TenantController.php`

RESTful endpoints for tenant operations.

---

## API Endpoints

### Create Tenant
```http
POST /api/tenants
Content-Type: application/json

{
  "name": "Acme University",
  "slug": "acme-university",
  "contact_email": "contact@acme.edu",
  "contact_phone": "+1-555-0100"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Tenant created successfully",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Acme University",
    "slug": "acme-university",
    "contact_email": "contact@acme.edu",
    "contact_phone": "+1-555-0100",
    "status": "pending",
    "created_at": "2024-10-01 12:00:00",
    "updated_at": "2024-10-01 12:00:00"
  }
}
```

### Get Tenant by ID
```http
GET /api/tenants/{id}
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Acme University",
    "slug": "acme-university",
    "contact_email": "contact@acme.edu",
    "contact_phone": "+1-555-0100",
    "status": "active",
    "created_at": "2024-10-01 12:00:00",
    "updated_at": "2024-10-01 12:30:00"
  }
}
```

### List Tenants
```http
GET /api/tenants?limit=20&offset=0
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 150,
    "limit": 20,
    "offset": 0
  }
}
```

---

## Business Rules

### Tenant Creation
1. Name must be 3-255 characters
2. Slug must be unique, URL-friendly (lowercase, hyphens allowed)
3. Contact email must be valid
4. Initial status is `pending` by default
5. Domain event `TenantCreated` is emitted

### Tenant Status Transitions
- `pending` → `active`: Activate the tenant
- `active` → `suspended`: Temporarily disable access
- `active` → `inactive`: Permanently disable
- `suspended` → `active`: Restore access

### Platform Access
Only tenants with `active` status can access the platform features.

---

## Database Schema

### Tenants Table
```sql
CREATE TABLE tenants (
    id CHAR(36) PRIMARY KEY COMMENT 'UUID',
    name VARCHAR(255) NOT NULL COMMENT 'Organization name',
    slug VARCHAR(50) UNIQUE NOT NULL COMMENT 'URL-friendly identifier',
    contact_email VARCHAR(255) NOT NULL COMMENT 'Primary contact email',
    contact_phone VARCHAR(20) NULL COMMENT 'Primary contact phone',
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

---

## Running Migrations

```bash
php bin/hyperf.php migrate
```

---

## Configuration

### Dependency Injection
**Location**: `config/autoload/tenant_dependencies.php`

Maps the `TenantRepositoryInterface` to the concrete `TenantRepository` implementation.

---

## Testing

### Unit Tests Example
```php
public function testTenantCreation(): void
{
    $tenant = Tenant::create(
        id: TenantId::generate(),
        name: "Test University",
        slug: TenantSlug::fromString("test-university"),
        contactEmail: ContactEmail::fromString("test@university.edu")
    );

    $this->assertEquals("Test University", $tenant->getName());
    $this->assertEquals("pending", $tenant->getStatus()->value);
}
```

---

## Future Enhancements

1. **Tenant Configuration**: Custom branding, domains, policies
2. **Multi-tenancy Middleware**: Automatic tenant resolution from subdomain/header
3. **Tenant Isolation**: Database-level or schema-level isolation
4. **Billing Integration**: Subscription management per tenant
5. **Analytics**: Usage metrics per tenant
6. **Onboarding Workflow**: Automated setup tasks after creation

---

## Event Storming Alignment

This implementation aligns with the Event Storming session:

- **Event**: `TenantCreated` ✅
- **Aggregate**: Tenant ✅
- **Commands**: Create Tenant ✅
- **Queries**: Get Tenant, List Tenants ✅
- **Integration Point**: Teacher notification (TODO in listener)

---

## Related Bounded Contexts

- **Course Management**: Courses belong to tenants
- **Student Learning**: Students enrolled under specific tenants
- **Certification Management**: Certificates issued by tenants
- **Teacher Management**: Teachers assigned to tenants
