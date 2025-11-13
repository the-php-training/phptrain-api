# Layer Organization in DDD

## Why Event Listeners Belong in Infrastructure

You asked a great question: "Isn't event listener an item that belongs to infra?"

**Answer: YES! Absolutely correct!**

Event listeners are infrastructure concerns and should be in the **Infrastructure layer**, not Application.

---

## The Layers in DDD

```
┌─────────────────────────────────────────────────┐
│ Presentation Layer (HTTP Controllers, CLI)      │
│  - User interface concerns                      │
│  - HTTP requests/responses                      │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ Application Layer (Use Cases, Commands)         │
│  - Orchestration logic                          │
│  - Commands and Handlers                        │
│  - DTOs                                         │
│  - NO infrastructure details                    │
└──────────────────┬──────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────┐
│ Domain Layer (Entities, Value Objects, Events)  │
│  - Business logic                               │
│  - Domain entities and aggregates               │
│  - Value objects                                │
│  - Domain events (definitions only)             │
│  - Repository interfaces                        │
│  - NO infrastructure details                    │
└──────────────────┬──────────────────────────────┘
                   ↑
┌─────────────────────────────────────────────────┐
│ Infrastructure Layer (Technical Details)        │
│  - Database implementations                     │
│  - Event listeners/subscribers ← HERE!          │
│  - Email services                               │
│  - HTTP clients                                 │
│  - Framework-specific code                      │
│  - Message queue implementations                │
└─────────────────────────────────────────────────┘
```

---

## Why Event Listeners are Infrastructure

### 1. Framework Dependency
```php
use Hyperf\Event\Annotation\Listener;  // ← Framework-specific!
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]  // ← Hyperf annotation
class StudentEnrolledListener implements ListenerInterface
```

Event listeners depend on framework-specific code (Hyperf in this case). Domain and Application layers should be framework-agnostic.

### 2. Technical Mechanism
Event listeners are the **HOW**, not the **WHAT**:
- **Domain Event** (Domain layer): "StudentEnrolled" - WHAT happened
- **Event Listener** (Infrastructure): HOW to react using Hyperf's event system

### 3. They Wire Things Together
Listeners connect different parts of the system using technical infrastructure:
```php
// Infrastructure concern: Listening to events via Hyperf
public function listen(): array
{
    return [StudentEnrolled::class];
}

// Infrastructure concern: Invoking application handlers
public function process(object $event): void
{
    $this->enrollStudentHandler->handle($command);
}
```

---

## What Goes Where?

### Domain Layer
```
Domain/
├── Entity/
│   ├── Course.php          ✅ Business entities
│   └── Enrollment.php      ✅ Business entities
├── ValueObject/
│   └── CourseId.php        ✅ Domain value objects
├── Event/
│   └── StudentEnrolled.php ✅ Event DEFINITION (interface/class)
└── Repository/
    └── CourseRepositoryInterface.php ✅ Repository INTERFACE
```

**Domain contains**: Pure business logic, no framework code

### Application Layer
```
Application/
├── Command/
│   ├── EnrollStudentCommand.php  ✅ Command (DTO)
│   └── EnrollStudentHandler.php  ✅ Use case orchestration
├── Query/
│   └── GetCourseQuery.php        ✅ Query handlers
└── DTO/
    └── CourseDTO.php             ✅ Data transfer objects
```

**Application contains**: Use cases, orchestration, NO infrastructure details

### Infrastructure Layer
```
Infrastructure/
├── EventListener/
│   └── StudentEnrolledListener.php     ✅ Event listeners (framework-specific)
├── Persistence/
│   ├── Model/
│   │   └── CourseModel.php             ✅ ORM models (Eloquent, etc.)
│   └── Repository/
│       └── CourseRepository.php        ✅ Repository IMPLEMENTATION
├── Email/
│   └── SmtpEmailService.php            ✅ Email implementation
└── Http/
    └── HyperfHttpClient.php            ✅ HTTP clients
```

**Infrastructure contains**: All technical implementation details

---

## The Corrected Structure

### Before (Wrong) ❌
```
CourseManagement/
└── Application/
    └── EventListener/          ❌ Wrong layer!
        └── StudentEnrolledListener.php
```

### After (Correct) ✅
```
CourseManagement/
└── Infrastructure/
    └── EventListener/          ✅ Correct layer!
        └── StudentEnrolledListener.php
```

---

## Current Project Structure

```
src/modules/
├── TenantManagement/
│   ├── Domain/
│   │   ├── Entity/
│   │   ├── Event/
│   │   │   └── TenantCreated.php         (Domain event definition)
│   │   ├── ValueObject/
│   │   └── Repository/
│   ├── Application/
│   │   ├── Command/
│   │   ├── Query/
│   │   └── DTO/
│   └── Infrastructure/
│       ├── EventListener/
│       │   └── TenantCreatedListener.php  ✅ (Infrastructure)
│       └── Persistence/
│
├── StudentLearning/
│   ├── Domain/
│   │   ├── Entity/
│   │   └── ValueObject/
│   └── Events/
│       └── StudentEnrolled.php            (Domain event definition)
│
└── CourseManagement/
    ├── Domain/
    │   ├── Entity/
    │   ├── ValueObject/
    │   └── Repository/
    ├── Application/
    │   └── Command/
    └── Infrastructure/
        ├── EventListener/
        │   └── StudentEnrolledListener.php ✅ (Infrastructure)
        └── Persistence/
```

---

## Key Principles

1. **Domain Layer**: Framework-agnostic, pure business logic
2. **Application Layer**: Orchestration, no infrastructure details
3. **Infrastructure Layer**: Framework code, technical implementations
4. **Event Listeners**: Always Infrastructure (they're technical mechanisms)

---

## Benefits of Correct Layering

1. ✅ **Testability**: Domain and Application can be tested without framework
2. ✅ **Portability**: Can switch frameworks (Hyperf → Laravel) without changing domain
3. ✅ **Clarity**: Clear separation between business and technical concerns
4. ✅ **Maintainability**: Changes to infrastructure don't affect domain

---

## Summary

**Event Listeners = Infrastructure**

They are:
- Framework-dependent (Hyperf annotations)
- Technical mechanisms (HOW to react to events)
- Implementation details (wiring between layers)

Therefore, they belong in the **Infrastructure layer**, not Application.

You were 100% correct to question this! 🎯
