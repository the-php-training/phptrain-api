# Student Enrollment Architecture (DDD)

This document describes the enrollment process between the StudentLearning and CourseManagement bounded contexts, following Domain-Driven Design principles.

## Overview

The enrollment process is a cross-bounded-context operation that involves two separate contexts:

1. **StudentLearning**: Manages students and their learning journey
2. **CourseManagement**: Manages courses, instructors, and enrollment tracking

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                   StudentLearning Context                        │
│                                                                  │
│  ┌──────────┐      enrollStudent()      ┌──────────────┐       │
│  │ Student  │ ──────────────────────────▶│    Course    │       │
│  │ (Entity) │                            │ (Aggregate)  │       │
│  └──────────┘                            └──────┬───────┘       │
│                                                  │               │
│                                    recordEvent() │               │
│                                                  ▼               │
│                                      ┌────────────────────┐     │
│                                      │ StudentEnrolled    │     │
│                                      │ (Domain Event)     │     │
│                                      └──────────┬─────────┘     │
└─────────────────────────────────────────────────┼───────────────┘
                                                  │
                            publishEntity()       │ Event Bus
                            (synchronous)         │
                                                  ▼
┌─────────────────────────────────────────────────┼───────────────┐
│                   CourseManagement Context      │               │
│                                                  │               │
│                                      ┌───────────▼──────────┐   │
│                                      │ StudentEnrolled      │   │
│                                      │ Listener             │   │
│                                      └──────────┬───────────┘   │
│                                                 │               │
│                                       handle()  │               │
│                                                 ▼               │
│                                      ┌────────────────────┐    │
│                                      │ EnrollStudent      │    │
│                                      │ Handler (UseCase)  │    │
│                                      └──────────┬─────────┘    │
│                                                 │               │
│                                    enrollStudent()              │
│                                                 ▼               │
│                              ┌──────────────────────────────┐  │
│                              │         Course               │  │
│                              │       (Aggregate)            │  │
│                              │  ┌────────────────────────┐  │  │
│                              │  │  Enrollment (Entity)   │  │  │
│                              │  └────────────────────────┘  │  │
│                              └──────────────────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Bounded Contexts

### StudentLearning Context

**Purpose**: Manages the student's learning journey and enrollment from the student's perspective.

**Key Components**:

- **Student** (Entity): Represents a student
  - Properties: StudentId, name, email
  - Location: `src/modules/StudentLearning/Domain/Entity/Student.php`

- **Course** (Aggregate Root): Represents a course from the learning perspective
  - Properties: CourseId, title, maxStudents, enrolledStudents
  - Business Rules:
    - Cannot enroll a student twice
    - Cannot exceed maximum capacity
  - Location: `src/modules/StudentLearning/Domain/Entity/Course.php:84`
  - Key Method: `enrollStudent()` at line 84

- **StudentEnrolled** (Domain Event): Emitted when enrollment happens
  - Properties: courseId, studentId, studentName, studentEmail, enrolledAt
  - Location: `src/modules/StudentLearning/Events/StudentEnrolled.php`

### CourseManagement Context

**Purpose**: Manages courses, instructors, schedules, and tracks enrollments from the administration perspective.

**Key Components**:

- **Course** (Aggregate Root): Represents a course from the management perspective
  - Properties: CourseId, title, description, maxCapacity, instructorId, enrollments
  - Location: `src/modules/CourseManagement/Domain/Entity/Course.php:79`
  - Key Method: `enrollStudent()` at line 79

- **Enrollment** (Entity): Represents a student's enrollment in a course
  - Properties: StudentId, CourseId, status, enrolledAt, completedAt, droppedAt
  - Lifecycle: ACTIVE → COMPLETED/DROPPED/SUSPENDED
  - Location: `src/modules/CourseManagement/Domain/Entity/Enrollment.php`

- **EnrollStudentHandler** (Use Case/Application Service):
  - Handles the enrollment command
  - Orchestrates the enrollment in the CourseManagement context
  - Location: `src/modules/CourseManagement/Application/Command/EnrollStudentHandler.php`

- **StudentEnrolledListener** (Event Listener - Infrastructure):
  - Listens for StudentEnrolled events from StudentLearning
  - Executes synchronously (no queues)
  - Invokes EnrollStudentHandler to record enrollment
  - Location: `src/modules/CourseManagement/Infrastructure/EventListener/StudentEnrolledListener.php`

## Enrollment Flow

1. **Student enrolls in course** (StudentLearning context)
   ```php
   $course->enrollStudent($student);
   ```

2. **Business rules validated** in StudentLearning.Course:
   - Is student already enrolled?
   - Is course full?

3. **Domain event recorded**:
   ```php
   $this->recordEvent(new StudentEnrolled(...));
   ```

4. **Event published** by EventBus (synchronous):
   ```php
   $eventBus->publishEntity($course);
   ```

5. **Event received** by CourseManagement.StudentEnrolledListener:
   - Listens for `StudentEnrolled` events
   - Creates `EnrollStudentCommand`
   - Invokes `EnrollStudentHandler`

6. **Enrollment recorded** in CourseManagement context:
   - Finds or creates Course entity
   - Validates business rules again (defensive programming)
   - Creates Enrollment entity
   - Persists to CourseManagement database

## Key DDD Principles Applied

### 1. Bounded Contexts
- Two separate contexts with their own models
- Each context has its own Course entity with different responsibilities
- StudentLearning.Course: Tracks who is enrolled (learning perspective)
- CourseManagement.Course: Tracks enrollments, status, administration

### 2. Domain Events
- `StudentEnrolled` represents a fact: "A student enrolled in a course"
- Events are immutable and readonly
- Events communicate between bounded contexts
- Events contain all necessary data to process in other contexts

### 3. Aggregates
- Course is the aggregate root in both contexts
- Enrollment is an entity within CourseManagement.Course aggregate
- Aggregates enforce business rules and consistency boundaries

### 4. Value Objects
- `StudentId`, `CourseId`: Identity value objects
- `EnrollmentDate`: Date with business rules (cannot be in future)
- `EnrollmentStatus`: Enum with behavior
- All value objects are immutable and validate themselves

### 5. Application Services (Use Cases)
- `EnrollStudentHandler`: Orchestrates the enrollment process
- Coordinates between domain objects and repositories
- No business logic (delegated to domain entities)

### 6. Repository Pattern
- `CourseRepositoryInterface`: Abstracts persistence
- Domain layer defines the interface
- Infrastructure layer implements it

### 7. Event-Driven Architecture
- Synchronous event handling (as requested, no queues)
- Events enable loose coupling between bounded contexts
- Each context can evolve independently

## Synchronous vs Asynchronous

**Current Implementation**: Synchronous (as requested)

The `StudentEnrolledListener` executes directly when the event is published. This means:
- ✅ Immediate consistency
- ✅ Transaction can be rolled back if enrollment fails in CourseManagement
- ⚠️ Slower response time (both contexts updated in same request)
- ⚠️ Tight coupling in terms of availability (if CourseManagement is down, enrollment fails)

**Alternative**: If you later need async processing, you can:
1. Configure the event bus to use queues (Kafka, RabbitMQ, etc.)
2. The listener remains the same, just processes messages from queue
3. Consider eventual consistency and compensating transactions

## Error Handling

The listener re-throws exceptions to ensure transactional integrity:

```php
try {
    $this->enrollStudentHandler->handle($command);
} catch (\Exception $e) {
    $this->logger->error('Failed to process event', [...]);
    throw $e; // Ensures enrollment in StudentLearning also fails
}
```

This maintains consistency: if enrollment can't be recorded in CourseManagement, the original enrollment in StudentLearning will also fail.

## File Structure

```
src/modules/
├── StudentLearning/
│   ├── Domain/
│   │   ├── Entity/
│   │   │   ├── Student.php
│   │   │   └── Course.php
│   │   └── ValueObject/
│   │       ├── StudentId.php
│   │       ├── CourseId.php
│   │       └── EnrollmentDate.php
│   └── Events/
│       └── StudentEnrolled.php
│
└── CourseManagement/
    ├── Domain/
    │   ├── Entity/
    │   │   ├── Course.php
    │   │   └── Enrollment.php
    │   ├── ValueObject/
    │   │   ├── CourseId.php
    │   │   ├── StudentId.php
    │   │   └── EnrollmentStatus.php
    │   └── Repository/
    │       └── CourseRepositoryInterface.php
    ├── Application/
    │   └── Command/
    │       ├── EnrollStudentCommand.php
    │       └── EnrollStudentHandler.php
    └── Infrastructure/
        ├── EventListener/
        │   └── StudentEnrolledListener.php
        └── Persistence/
            ├── Model/
            └── Repository/
```

## Future Enhancements

1. **Course Synchronization**: Currently, CourseManagement creates a placeholder course if it doesn't exist. Consider implementing proper course synchronization between contexts.

2. **Compensating Transactions**: If using async processing, implement compensating transactions for handling failures.

3. **Event Sourcing**: Consider storing all domain events for audit trail and replay capability.

4. **CQRS**: Separate read models for different views of enrollments.

5. **Additional Events**:
   - `EnrollmentCompleted`
   - `EnrollmentDropped`
   - `EnrollmentSuspended`

## Testing Strategy

1. **Unit Tests**:
   - Test Course.enrollStudent() business rules in both contexts
   - Test Enrollment lifecycle (activate, complete, drop, suspend)
   - Test Value Object validations

2. **Integration Tests**:
   - Test event publishing and handling
   - Test repository implementations
   - Test command handlers

3. **End-to-End Tests**:
   - Test complete enrollment flow across both contexts
   - Test error scenarios and rollbacks

## Summary

This architecture properly separates concerns between two bounded contexts while maintaining consistency through synchronous domain events. The StudentLearning context owns the student's learning journey, while CourseManagement owns the administrative view of courses and enrollments. The event-driven approach allows both contexts to evolve independently while staying synchronized.
