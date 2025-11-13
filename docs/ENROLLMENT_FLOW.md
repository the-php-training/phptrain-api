# Complete Enrollment Flow - From HTTP to Database

This document shows the complete end-to-end flow of student enrollment across both bounded contexts.

---

## HTTP Request

```http
POST /api/enrollments
Content-Type: application/json

{
  "course_id": "123e4567-e89b-12d3-a456-426614174000",
  "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
}
```

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│ 1. PRESENTATION LAYER (HTTP)                                        │
│    POST /api/enrollments                                            │
│    ↓                                                                 │
│    EnrollmentController::enroll()                                   │
│    src/modules/StudentLearning/Presentation/Http/Controller/        │
│    EnrollmentController.php                                         │
└──────────────────────────────┬──────────────────────────────────────┘
                               │ Creates EnrollStudentCommand
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 2. APPLICATION LAYER (StudentLearning Context)                      │
│    EnrollStudentHandler::handle()                                   │
│    src/modules/StudentLearning/Application/Command/                 │
│    EnrollStudentHandler.php                                         │
│                                                                      │
│    - Finds Student from StudentRepository                           │
│    - Finds Course from CourseRepository                             │
│    - Calls: $course->grantLearningAccess($student)                  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 3. DOMAIN LAYER (StudentLearning Context)                           │
│    Course::grantLearningAccess()                                    │
│    src/modules/StudentLearning/Domain/Entity/Course.php:95          │
│                                                                      │
│    - Validates: hasLearningAccess()                                 │
│    - Validates: isAtCapacity()                                      │
│    - Adds student to $enrolledStudents array                        │
│    - Records domain event: StudentEnrolled                          │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 4. INFRASTRUCTURE LAYER (StudentLearning Context)                   │
│    CourseRepository::save()                                         │
│    Saves to database: student_learning_courses table                │
│                                                                      │
│    EventBus::publishEntity()                                        │
│    Publishes StudentEnrolled event                                  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │ StudentEnrolled Event (Synchronous)
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 5. INFRASTRUCTURE LAYER (CourseManagement Context)                  │
│    StudentEnrolledListener::process()                               │
│    src/modules/CourseManagement/Infrastructure/EventListener/        │
│    StudentEnrolledListener.php                                      │
│                                                                      │
│    - Receives StudentEnrolled event                                 │
│    - Creates EnrollStudentCommand                                   │
│    - Calls EnrollStudentHandler                                     │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 6. APPLICATION LAYER (CourseManagement Context)                     │
│    EnrollStudentHandler::handle()                                   │
│    src/modules/CourseManagement/Application/Command/                 │
│    EnrollStudentHandler.php                                         │
│                                                                      │
│    - Finds/creates Course in CourseManagement                       │
│    - Calls: $course->recordEnrollment(...)                          │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 7. DOMAIN LAYER (CourseManagement Context)                          │
│    Course::recordEnrollment()                                       │
│    src/modules/CourseManagement/Domain/Entity/Course.php:101         │
│                                                                      │
│    - Validates: hasEnrollmentRecord()                               │
│    - Validates: isAtMaxCapacity()                                   │
│    - Creates Enrollment entity (status: ACTIVE)                     │
│    - Adds to $enrollments array                                     │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│ 8. INFRASTRUCTURE LAYER (CourseManagement Context)                  │
│    CourseRepository::save()                                         │
│    Saves to database:                                               │
│    - course_management_courses table                                │
│    - course_management_enrollments table                            │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │   SUCCESS RESPONSE   │
                    │   HTTP 201 Created   │
                    └──────────────────────┘
```

---

## Step-by-Step Breakdown

### Step 1: HTTP Request Received
**Location**: `src/modules/StudentLearning/Presentation/Http/Controller/EnrollmentController.php`

```php
POST /api/enrollments
{
  "course_id": "123e4567-e89b-12d3-a456-426614174000",
  "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
}
```

Controller validates input and creates command.

---

### Step 2: EnrollStudentHandler (StudentLearning)
**Location**: `src/modules/StudentLearning/Application/Command/EnrollStudentHandler.php`

```php
public function handle(EnrollStudentCommand $command): void
{
    $student = $this->studentRepository->findById($studentId);
    $course = $this->courseRepository->findById($courseId);

    // Grant learning access
    $course->grantLearningAccess($student);

    // Save and publish events
    $this->courseRepository->save($course);
    $this->eventBus->publishEntity($course);
}
```

**Purpose**: Orchestrate the enrollment from learning perspective

---

### Step 3: Course::grantLearningAccess (StudentLearning Domain)
**Location**: `src/modules/StudentLearning/Domain/Entity/Course.php:95`

```php
public function grantLearningAccess(Student $student): void
{
    // Business rules
    if ($this->hasLearningAccess($student)) {
        throw new InvalidArgumentException("Already has access");
    }

    if ($this->isAtCapacity()) {
        throw new InvalidArgumentException("Course is full");
    }

    // Grant access
    $this->enrolledStudents[$student->getId()->toString()] = [
        'student' => $student,
        'enrolledAt' => EnrollmentDate::now(),
    ];

    // Record event
    $this->recordEvent(new StudentEnrolled(...));
}
```

**Purpose**: Apply business rules and record domain event

---

### Step 4: Save & Publish Event (StudentLearning Infrastructure)

**CourseRepository**: Saves to `student_learning_courses` table

**EventBus**: Publishes `StudentEnrolled` event synchronously

---

### Step 5: StudentEnrolledListener (CourseManagement Infrastructure)
**Location**: `src/modules/CourseManagement/Infrastructure/EventListener/StudentEnrolledListener.php`

```php
public function process(object $event): void
{
    // Create command from event
    $command = new EnrollStudentCommand(
        courseId: $event->courseId,
        studentId: $event->studentId,
        studentName: $event->studentName,
        studentEmail: $event->studentEmail,
        enrolledAt: $event->enrolledAt->format('Y-m-d H:i:s'),
    );

    // Execute handler
    $this->enrollStudentHandler->handle($command);
}
```

**Purpose**: React to StudentEnrolled event and invoke CourseManagement use case

---

### Step 6: EnrollStudentHandler (CourseManagement)
**Location**: `src/modules/CourseManagement/Application/Command/EnrollStudentHandler.php`

```php
public function handle(EnrollStudentCommand $command): void
{
    // Find or create course
    $course = $this->courseRepository->findById($courseId);

    // Record enrollment
    $course->recordEnrollment(
        studentId: $studentId,
        studentName: $command->studentName,
        studentEmail: $command->studentEmail,
        enrolledAt: $enrolledAt
    );

    // Save
    $this->courseRepository->save($course);
}
```

**Purpose**: Orchestrate enrollment recording for administrative purposes

---

### Step 7: Course::recordEnrollment (CourseManagement Domain)
**Location**: `src/modules/CourseManagement/Domain/Entity/Course.php:101`

```php
public function recordEnrollment(...): void
{
    // Business rules
    if ($this->hasEnrollmentRecord($studentId)) {
        throw new InvalidArgumentException("Already enrolled");
    }

    if ($this->isAtMaxCapacity()) {
        throw new InvalidArgumentException("Course full");
    }

    // Create enrollment record
    $enrollment = Enrollment::create(
        studentId: $studentId,
        courseId: $this->id,
        studentName: $studentName,
        studentEmail: $studentEmail,
        enrolledAt: $enrolledAt
    );

    $this->enrollments[$studentId->toString()] = $enrollment;
}
```

**Purpose**: Record enrollment for administrative tracking

---

### Step 8: Save (CourseManagement Infrastructure)

**CourseRepository**: Saves to both:
- `course_management_courses` table
- `course_management_enrollments` table (with status: ACTIVE)

---

## Database Changes

### StudentLearning Database

**Table**: `student_learning_courses`
```sql
INSERT/UPDATE course with enrolled_students JSON
```

### CourseManagement Database

**Table**: `course_management_courses`
```sql
INSERT/UPDATE course record
```

**Table**: `course_management_enrollments`
```sql
INSERT enrollment record:
- id: uuid
- course_id: uuid
- student_id: uuid
- student_name: string
- student_email: string
- status: 'active'
- enrolled_at: timestamp
```

---

## Success Response

```json
{
  "success": true,
  "message": "Student successfully enrolled in course",
  "data": {
    "course_id": "123e4567-e89b-12d3-a456-426614174000",
    "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc",
    "status": "enrolled"
  }
}
```

**HTTP Status**: 201 Created

---

## Error Handling

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "course_id": ["The course_id field is required."]
  }
}
```

### Business Rule Violation (400)
```json
{
  "success": false,
  "message": "Student already has learning access to this course"
}
```

### Not Found (400)
```json
{
  "success": false,
  "message": "Student {id} not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "An error occurred while enrolling the student",
  "error": "Connection timeout"
}
```

---

## Files Created

### CourseManagement Context

**Infrastructure - Persistence**:
- `src/modules/CourseManagement/Infrastructure/Persistence/Model/CourseModel.php`
- `src/modules/CourseManagement/Infrastructure/Persistence/Model/EnrollmentModel.php`
- `src/modules/CourseManagement/Infrastructure/Persistence/Repository/CourseRepository.php`

### StudentLearning Context

**Domain - Repository Interfaces**:
- `src/modules/StudentLearning/Domain/Repository/CourseRepositoryInterface.php`
- `src/modules/StudentLearning/Domain/Repository/StudentRepositoryInterface.php`

**Application - Use Case**:
- `src/modules/StudentLearning/Application/Command/EnrollStudentCommand.php`
- `src/modules/StudentLearning/Application/Command/EnrollStudentHandler.php`

**Presentation - HTTP**:
- `src/modules/StudentLearning/Presentation/Http/Controller/EnrollmentController.php`

---

## Testing the Endpoint

### Using cURL

```bash
curl -X POST http://localhost:9501/api/enrollments \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": "123e4567-e89b-12d3-a456-426614174000",
    "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
  }'
```

### Using Postman

```
Method: POST
URL: http://localhost:9501/api/enrollments
Headers:
  Content-Type: application/json
Body (raw JSON):
{
  "course_id": "123e4567-e89b-12d3-a456-426614174000",
  "student_id": "987fcdeb-51a2-43d7-8f12-123456789abc"
}
```

---

## Summary

This enrollment flow demonstrates:

1. ✅ **Clean Architecture**: Presentation → Application → Domain → Infrastructure
2. ✅ **DDD**: Two bounded contexts with clear responsibilities
3. ✅ **Event-Driven**: Synchronous event communication between contexts
4. ✅ **CQRS**: Separate command handling (no queries mixed in)
5. ✅ **Repository Pattern**: Abstraction over persistence
6. ✅ **Domain Events**: StudentEnrolled communicates between contexts
7. ✅ **Separation of Concerns**: Each layer has clear responsibilities

The student gets enrolled in **both contexts** with **different meanings**:
- **StudentLearning**: Gets learning access to materials
- **CourseManagement**: Gets administrative enrollment record tracked
