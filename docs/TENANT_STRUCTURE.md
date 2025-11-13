# Tenant Management - Complete Structure

## 📁 Project Structure

```
src/
├── app/
│   ├── Domain/
│   │   └── Tenant/
│   │       ├── Entity/
│   │       │   └── Tenant.php                         # Aggregate Root
│   │       ├── ValueObject/
│   │       │   ├── TenantId.php                       # UUID identifier
│   │       │   ├── TenantSlug.php                     # URL-friendly slug
│   │       │   ├── TenantStatus.php                   # Enum (active/inactive/suspended/pending)
│   │       │   └── ContactEmail.php                   # Validated email
│   │       ├── Repository/
│   │       │   └── TenantRepositoryInterface.php      # Repository contract
│   │       └── Event/
│   │           └── TenantCreated.php                  # Domain event
│   │
│   ├── Application/
│   │   └── Tenant/
│   │       ├── Command/
│   │       │   ├── CreateTenantCommand.php            # CQRS Command
│   │       │   └── CreateTenantHandler.php            # Command handler
│   │       ├── Query/
│   │       │   ├── GetTenantQuery.php                 # CQRS Query
│   │       │   ├── GetTenantHandler.php               # Query handler
│   │       │   ├── ListTenantsQuery.php               # Pagination query
│   │       │   └── ListTenantsHandler.php             # List handler
│   │       ├── DTO/
│   │       │   ├── CreateTenantDTO.php                # Input DTO
│   │       │   └── TenantDTO.php                      # Output DTO
│   │       └── EventListener/
│   │           └── TenantCreatedListener.php          # Event listener
│   │
│   ├── Infrastructure/
│   │   └── Persistence/
│   │       ├── Model/
│   │       │   └── TenantModel.php                    # Eloquent model
│   │       └── Repository/
│   │           └── TenantRepository.php               # Repository implementation
│   │
│   └── Presentation/
│       └── Http/
│           └── Controller/
│               └── TenantController.php               # REST API controller
│
├── config/
│   └── autoload/
│       └── tenant_dependencies.php                    # DI configuration
│
├── migrations/
│   └── 2024_10_01_000001_create_tenants_table.php    # Database migration
│
└── docs/
    ├── TENANT_MANAGEMENT.md                           # Domain documentation
    └── API_EXAMPLES.md                                # API usage examples
```

## 🎯 Domain-Driven Design Implementation

### Bounded Context: Tenant Management
Manages the lifecycle of tenants (organizations/institutions) in the multi-tenant platform.

### Ubiquitous Language
- **Tenant**: An organization/institution using the platform
- **Slug**: URL-friendly unique identifier
- **Status**: Operational state (active/inactive/suspended/pending)
- **Aggregate**: Tenant is the root
- **Domain Event**: TenantCreated

### Key Patterns Applied
✅ **Entity**: Tenant with identity (TenantId)
✅ **Value Objects**: TenantId, TenantSlug, TenantStatus, ContactEmail
✅ **Aggregate Root**: Tenant enforces invariants
✅ **Repository Pattern**: Abstract persistence
✅ **Domain Events**: TenantCreated for decoupling
✅ **CQRS**: Separate Commands and Queries
✅ **Clean Architecture**: Layered separation
✅ **Factory Method**: `Tenant::create()`

## 🏗️ Architecture Layers

### 1. Domain Layer (Core Business Logic)
- **No external dependencies**
- Contains business rules and invariants
- Pure PHP entities and value objects
- Domain events for communication

**Key Files:**
- `Tenant.php` (167 lines) - Aggregate root with business rules
- `TenantId.php` - UUID-based identifier
- `TenantSlug.php` - URL validation
- `TenantStatus.php` - Enum with behavior

### 2. Application Layer (Use Cases)
- Orchestrates domain objects
- Implements CQRS (Commands/Queries)
- Handles application logic
- Dispatches events

**Key Files:**
- `CreateTenantHandler.php` - Creates tenant, validates uniqueness
- `GetTenantHandler.php` - Retrieves tenant by ID
- `ListTenantsHandler.php` - Paginated list
- `TenantCreatedListener.php` - Reacts to domain events

### 3. Infrastructure Layer (Technical Implementation)
- Database persistence
- ORM mapping (Eloquent)
- Repository implementation

**Key Files:**
- `TenantRepository.php` - Translates between domain/persistence
- `TenantModel.php` - Eloquent model

### 4. Presentation Layer (API/UI)
- HTTP endpoints
- Request validation
- Response formatting

**Key Files:**
- `TenantController.php` - REST API with 3 endpoints

## 🔄 Data Flow

### Create Tenant Flow
```
HTTP POST /api/tenants
    ↓
TenantController::create()
    ↓ (validation)
CreateTenantDTO
    ↓
CreateTenantCommand
    ↓
CreateTenantHandler::handle()
    ↓ (business logic)
Tenant::create() → TenantCreated event
    ↓ (persistence)
TenantRepository::save()
    ↓ (database)
TenantModel → MySQL
    ↓ (event dispatch)
TenantCreatedListener::process()
    ↓
Return TenantDTO → JSON Response
```

## 📊 Database Schema

```sql
CREATE TABLE tenants (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(20) NULL,
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

## 🚀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/tenants` | Create new tenant |
| GET | `/api/tenants/{id}` | Get tenant by ID |
| GET | `/api/tenants?limit=20&offset=0` | List tenants (paginated) |

## ✅ Business Rules Implemented

1. **Tenant Creation**
   - Name: 3-255 characters
   - Slug: Unique, lowercase, URL-friendly pattern
   - Contact email: Valid email format
   - Initial status: `pending`

2. **Slug Validation**
   - Pattern: `^[a-z0-9]+(?:-[a-z0-9]+)*$`
   - Min length: 3 characters
   - Max length: 50 characters
   - Must be unique across all tenants

3. **Status Transitions**
   - `pending` → `active` (activate)
   - `active` → `suspended` (suspend)
   - `active` → `inactive` (deactivate)
   - Only `active` status allows platform access

4. **Domain Events**
   - `TenantCreated` emitted on creation
   - Logged and ready for integration (notifications, etc.)

## 🧪 Testing

### Run Migration
```bash
php bin/hyperf.php migrate
```

### Test API
```bash
# Create tenant
curl -X POST http://localhost:9501/api/tenants \
  -H "Content-Type: application/json" \
  -d '{"name":"Test University","slug":"test-university","contact_email":"test@uni.edu"}'

# Get tenant
curl http://localhost:9501/api/tenants/{id}

# List tenants
curl http://localhost:9501/api/tenants?limit=10
```

## 📦 Dependencies

- **Hyperf 3.1**: Framework
- **PHP 8.3+**: Language (using 8.4 features)
- **MySQL**: Database
- **PSR-11**: Dependency Injection
- **PSR-14**: Event Dispatcher

## 🔍 Key Features

### PHP 8.4 Features Used
✅ Readonly properties
✅ Constructor property promotion
✅ Typed properties
✅ Union types
✅ Enums (TenantStatus)
✅ Attributes (Controller routing, DI)
✅ Named arguments

### DDD Tactical Patterns
✅ Entity
✅ Value Objects
✅ Aggregate Root
✅ Repository
✅ Domain Events
✅ Factory Method
✅ Ubiquitous Language

### Clean Architecture Principles
✅ Dependency Inversion
✅ Separation of Concerns
✅ Layer Independence
✅ Testability

## 📖 Documentation

- `docs/TENANT_MANAGEMENT.md` - Complete domain documentation
- `docs/API_EXAMPLES.md` - API usage examples with cURL, Postman, HTTPie

## 🔮 Next Steps / TODOs

1. **Implement Additional Commands**
   - UpdateTenantCommand
   - ActivateTenantCommand
   - SuspendTenantCommand

2. **Add Multi-tenancy Middleware**
   - Tenant resolution from subdomain/header
   - Automatic tenant context injection

3. **Expand Event Handling**
   - Send welcome emails on TenantCreated
   - Notify teachers (as per event storming)
   - Create initial configurations

4. **Testing**
   - Unit tests for domain entities
   - Integration tests for repositories
   - API tests for controllers

5. **Related Contexts**
   - Course aggregate
   - Student aggregate
   - Teacher aggregate
   - Certification aggregate

## 📊 Code Statistics

- **Total Files Created**: 19 PHP classes + 1 migration + 2 docs
- **Total Lines**: ~2,000 lines of production code
- **Architecture Layers**: 4 (Domain, Application, Infrastructure, Presentation)
- **Value Objects**: 4
- **Commands**: 1 (CreateTenant)
- **Queries**: 2 (GetTenant, ListTenants)
- **Events**: 1 (TenantCreated)
- **Endpoints**: 3 REST APIs

---

## 🎓 Learning Resources

This implementation demonstrates:
- Domain-Driven Design tactical patterns
- Clean Architecture principles
- CQRS pattern
- Event-Driven Architecture
- Repository pattern
- Dependency Injection
- RESTful API design
- Multi-tenant architecture foundations

Perfect foundation for expanding with Course, Student, Teacher, and Certification bounded contexts!
