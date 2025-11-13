# Repository Implementations

This document shows all repository implementations across both bounded contexts.

---

## Overview

Repositories implement the **Repository Pattern**, which:
- Abstracts persistence layer from domain layer
- Translates between Domain Entities and Persistence Models
- Implements domain repository interfaces (defined in Domain layer)
- Lives in Infrastructure layer (framework-specific code)

---

## StudentLearning Context

### Domain Repository Interfaces

**Location**: `src/modules/StudentLearning/Domain/Repository/`

#### StudentRepositoryInterface.php
```php
interface StudentRepositoryInterface
{
    public function save(Student $student): void;
    public function findById(StudentId $id): ?Student;
    public function exists(StudentId $id): bool;
    public function findByEmail(string $email): ?Student;
    public function findAll(): array;
}
```

#### CourseRepositoryInterface.php
```php
interface CourseRepositoryInterface
{
    public function save(Course $course): void;
    public function findById(CourseId $id): ?Course;
    public function exists(CourseId $id): bool;
    public function findAll(): array;
}
```

---

### Infrastructure - Persistence Models

**Location**: `src/modules/StudentLearning/Infrastructure/Persistence/Model/`

#### StudentModel.php
- **Table**: `student_learning_students`
- **Fields**: id, name, email, created_at, updated_at
- **Key Type**: UUID (string, non-incrementing)

#### CourseModel.php
- **Table**: `student_learning_courses`
- **Fields**: id, title, max_students, enrolled_students (JSON), created_at, updated_at
- **Key Type**: UUID (string, non-incrementing)
- **Special**: `enrolled_students` stored as JSON (denormalized for learning access)

---

### Infrastructure - Repository Implementations

**Location**: `src/modules/StudentLearning/Infrastructure/Persistence/Repository/`

#### StudentRepository.php

**Implements**: `StudentRepositoryInterface`

**Key Methods**:
```php
save(Student $student): void
- Finds existing model by ID
- Creates new if not exists
- Maps domain properties to model fields
- Saves to database

findById(StudentId $id): ?Student
- Queries by ID
- Converts model to domain entity using toDomain()
- Returns null if not found

findByEmail(string $email): ?Student
- Queries by email
- Converts to domain entity

findAll(): array
- Fetches all students
- Maps each model to domain entity
```

**Translation Method**:
```php
private function toDomain(StudentModel $model): Student
{
    return Student::reconstitute(
        id: StudentId::fromString($model->id),
        name: $model->name,
        email: $model->email,
        createdAt: DateTimeImmutable::createFromMutable($model->created_at),
        updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
    );
}
```

---

#### CourseRepository.php

**Implements**: `CourseRepositoryInterface`

**Key Methods**:
```php
save(Course $course): void
- Finds existing model by ID
- Serializes enrolled students to JSON format
  {
    "student_id": {
      "student_id": "uuid",
      "student_name": "John Doe",
      "student_email": "john@example.com",
      "enrolled_at": "2025-01-15 10:30:00"
    }
  }
- Saves to database

findById(CourseId $id): ?Course
- Queries by ID
- Deserializes enrolled_students JSON
- Reconstitutes Student entities from JSON data
- Converts to Course domain entity

findAll(): array
- Fetches all courses
- Deserializes and reconstitutes for each
```

**Translation Method**:
```php
private function toDomain(CourseModel $model): Course
{
    // Deserialize enrolled students from JSON
    $enrolledStudents = [];
    foreach ($model->enrolled_students as $studentId => $data) {
        $student = Student::reconstitute(...); // From JSON data
        $enrolledStudents[$studentId] = [
            'student' => $student,
            'enrolledAt' => EnrollmentDate::fromString($data['enrolled_at']),
        ];
    }

    return Course::reconstitute(
        enrolledStudents: $enrolledStudents,
        ...
    );
}
```

**Why JSON for enrolled_students?**
- StudentLearning context only needs learning access list
- No complex querying needed on individual enrollments
- Simpler than separate table for this context
- CourseManagement handles detailed enrollment tracking

---

## CourseManagement Context

### Domain Repository Interface

**Location**: `src/modules/CourseManagement/Domain/Repository/`

#### CourseRepositoryInterface.php
```php
interface CourseRepositoryInterface
{
    public function save(Course $course): void;
    public function findById(CourseId $id): ?Course;
    public function exists(CourseId $id): bool;
    public function delete(CourseId $id): void;
    public function findAll(): array;
}
```

---

### Infrastructure - Persistence Models

**Location**: `src/modules/CourseManagement/Infrastructure/Persistence/Model/`

#### CourseModel.php
- **Table**: `course_management_courses`
- **Fields**: id, title, description, max_capacity, instructor_id, created_at, updated_at
- **Key Type**: UUID (string, non-incrementing)
- **Relationships**: hasMany(EnrollmentModel)

#### EnrollmentModel.php
- **Table**: `course_management_enrollments`
- **Fields**: id, course_id, student_id, student_name, student_email, status, enrolled_at, completed_at, dropped_at, created_at, updated_at
- **Key Type**: UUID (string, non-incrementing)
- **Relationships**: belongsTo(CourseModel)

---

### Infrastructure - Repository Implementation

**Location**: `src/modules/CourseManagement/Infrastructure/Persistence/Repository/`

#### CourseRepository.php

**Implements**: `CourseRepositoryInterface`

**Key Methods**:
```php
save(Course $course): void
- Saves course to course_management_courses table
- Calls saveEnrollments() to save all enrollment entities
- Each enrollment saved to course_management_enrollments table

saveEnrollments(Course $course): void
- Iterates through course enrollments
- For each enrollment:
  - Finds existing by course_id + student_id
  - Creates new if not exists
  - Maps enrollment entity properties to model
  - Saves to database

findById(CourseId $id): ?Course
- Queries course with eager-loaded enrollments
- Converts enrollment models to Enrollment entities
- Reconstitutes Course aggregate with enrollments

delete(CourseId $id): void
- Deletes all enrollments first (cascade)
- Then deletes course

findAll(): array
- Fetches all courses with enrollments
- Converts each to domain entity
```

**Translation Method**:
```php
private function toDomain(CourseModel $model): Course
{
    // Convert enrollment models to entities
    $enrollments = [];
    foreach ($model->enrollments as $enrollmentModel) {
        $enrollment = Enrollment::reconstitute(
            studentId: StudentId::fromString($enrollmentModel->student_id),
            courseId: CourseId::fromString($enrollmentModel->course_id),
            status: EnrollmentStatus::fromString($enrollmentModel->status),
            ...
        );
        $enrollments[$enrollmentModel->student_id] = $enrollment;
    }

    return Course::reconstitute(
        enrollments: $enrollments,
        ...
    );
}
```

**Why separate Enrollment table?**
- CourseManagement needs to query/filter enrollments by status
- Track enrollment lifecycle (active → completed/dropped)
- Generate reports by enrollment status
- Proper relational model for administrative tracking

---

## Comparison: StudentLearning vs CourseManagement

| Aspect | StudentLearning | CourseManagement |
|--------|-----------------|------------------|
| **Course Table** | `student_learning_courses` | `course_management_courses` |
| **Enrollment Storage** | JSON field in Course table | Separate `enrollments` table |
| **Reason** | Simple access list | Complex tracking & reporting |
| **Querying** | Just check if student enrolled | Filter by status, count, reports |
| **Data** | Student ID + enrolled date | Full enrollment entity with status |
| **Relationships** | Embedded JSON | Proper foreign key relation |
| **Purpose** | "Who can access materials?" | "Track enrollment lifecycle" |

---

## Database Schema

### StudentLearning Tables

```sql
-- student_learning_students
CREATE TABLE student_learning_students (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- student_learning_courses
CREATE TABLE student_learning_courses (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    max_students INT NOT NULL,
    enrolled_students JSON, -- { "student_id": {...} }
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### CourseManagement Tables

```sql
-- course_management_courses
CREATE TABLE course_management_courses (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    max_capacity INT NOT NULL,
    instructor_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- course_management_enrollments
CREATE TABLE course_management_enrollments (
    id VARCHAR(36) PRIMARY KEY,
    course_id VARCHAR(36) NOT NULL,
    student_id VARCHAR(36) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    status ENUM('active', 'completed', 'dropped', 'suspended') NOT NULL,
    enrolled_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    dropped_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course_management_courses(id),
    UNIQUE KEY (course_id, student_id)
);
```

---

## Files Created

### StudentLearning Context

**Models**:
- `src/modules/StudentLearning/Infrastructure/Persistence/Model/StudentModel.php`
- `src/modules/StudentLearning/Infrastructure/Persistence/Model/CourseModel.php`

**Repository Implementations**:
- `src/modules/StudentLearning/Infrastructure/Persistence/Repository/StudentRepository.php`
- `src/modules/StudentLearning/Infrastructure/Persistence/Repository/CourseRepository.php`

### CourseManagement Context

**Models**:
- `src/modules/CourseManagement/Infrastructure/Persistence/Model/CourseModel.php`
- `src/modules/CourseManagement/Infrastructure/Persistence/Model/EnrollmentModel.php`

**Repository Implementation**:
- `src/modules/CourseManagement/Infrastructure/Persistence/Repository/CourseRepository.php`

---

## Key Takeaways

1. ✅ **Repository Pattern**: Abstracts persistence from domain
2. ✅ **Interface in Domain**: Domain defines contract, infrastructure implements
3. ✅ **Different Storage Strategies**: JSON vs relational based on context needs
4. ✅ **Translation Layer**: `toDomain()` converts models to entities
5. ✅ **Reconstitution**: Uses static `reconstitute()` methods to rebuild entities
6. ✅ **Context-Specific**: Each bounded context has its own persistence strategy

The repository implementations are complete and ready to use!
