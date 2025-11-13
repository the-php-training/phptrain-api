# Bounded Context Comparison: Course Entity

This document clearly shows how the same "Course" concept has **different responsibilities** in each bounded context.

---

## StudentLearning Context

### Perspective
**Student's Learning Journey**

### Responsibility
**Control access to learning resources**

### Key Questions
- Who has access to course materials?
- Can a student enroll to gain learning access?
- Is there room for more learners?

### Code Location
`src/modules/StudentLearning/Domain/Entity/Course.php`

### Key Methods

```php
// LEARNING ACCESS CONTROL
$course->grantLearningAccess(Student $student): void
$course->hasLearningAccess(Student $student): bool
$course->isAtCapacity(): bool
$course->getLearnerCount(): int
$course->getAvailableLearningSlots(): int
$course->getStudentsWithAccess(): array
```

### Properties
```php
private CourseId $id;
private string $title;
private int $maxStudents;              // Max learners
private array $enrolledStudents;       // Students with learning access
```

### Domain Language
- "Learning access"
- "Learners"
- "Course materials"
- "Learning capacity"

### What It Does
- Grants students permission to access course content
- Controls who can view materials and submit assignments
- Tracks learning capacity (how many students can learn)
- Emits `StudentEnrolled` event when granting access

### What It Does NOT Do
- ❌ Track enrollment status (active/completed/dropped)
- ❌ Manage instructors
- ❌ Generate administrative reports
- ❌ Track enrollment lifecycle

---

## CourseManagement Context

### Perspective
**Administrative/Management View**

### Responsibility
**Track enrollment records and generate reports**

### Key Questions
- **How many enrollments are active/completed/dropped?** ✅ (Main focus!)
- Who is the instructor?
- What is the enrollment capacity?
- Generate enrollment reports for administration

### Code Location
`src/modules/CourseManagement/Domain/Entity/Course.php`

### Key Methods

```php
// ADMINISTRATIVE ENROLLMENT TRACKING
$course->recordEnrollment(...): void
$course->hasEnrollmentRecord(StudentId $studentId): bool
$course->getEnrollmentRecord(StudentId $studentId): ?Enrollment

// REPORTING - Answer: "How many enrollments are active/completed/dropped?"
$course->countActiveEnrollments(): int
$course->countCompletedEnrollments(): int
$course->countDroppedEnrollments(): int
$course->countSuspendedEnrollments(): int
$course->getTotalEnrollmentCount(): int
$course->getEnrollmentStatistics(): array

// GET RECORDS BY STATUS
$course->getActiveEnrollments(): array
$course->getCompletedEnrollments(): array
$course->getDroppedEnrollments(): array

// CAPACITY MANAGEMENT
$course->isAtMaxCapacity(): bool
$course->getAvailableCapacity(): int
```

### Properties
```php
private CourseId $id;
private string $title;
private string $description;
private int $maxCapacity;              // Max administrative capacity
private ?string $instructorId;         // Who teaches it
private array $enrollments;            // Enrollment records (with status)
```

### Domain Language
- "Enrollment records"
- "Administrative tracking"
- "Enrollment status" (active/completed/dropped/suspended)
- "Instructor"
- "Course catalog"

### What It Does
- Records enrollment for administrative purposes
- Tracks enrollment lifecycle (active → completed/dropped/suspended)
- Generates enrollment statistics and reports
- Manages instructor assignments
- Tracks administrative capacity

### What It Does NOT Do
- ❌ Control learning access to materials
- ❌ Manage student progress
- ❌ Handle course content

---

## Side-by-Side Comparison

| Aspect | StudentLearning::Course | CourseManagement::Course |
|--------|------------------------|--------------------------|
| **Main Focus** | Learning access control | Enrollment tracking & reporting |
| **Method Name** | `grantLearningAccess()` | `recordEnrollment()` |
| **Check Method** | `hasLearningAccess()` | `hasEnrollmentRecord()` |
| **Capacity Check** | `isAtCapacity()` | `isAtMaxCapacity()` |
| **Count Method** | `getLearnerCount()` | `countActiveEnrollments()` |
| **Status Tracking** | ❌ None | ✅ Active/Completed/Dropped/Suspended |
| **Reporting** | ❌ None | ✅ `getEnrollmentStatistics()` |
| **Instructor** | ❌ Not relevant | ✅ `$instructorId` |
| **Purpose** | Who can learn? | How many enrolled? What status? |

---

## Communication Flow

```
┌─────────────────────────────────────────────────────────┐
│ StudentLearning Context                                 │
│                                                          │
│ Student wants to enroll                                 │
│         ↓                                                │
│ $course->grantLearningAccess($student)                  │
│         ↓                                                │
│ ✓ Student gets learning access                          │
│ ✓ Can view materials, submit assignments                │
│ ✓ Emits StudentEnrolled event                           │
└─────────────────────────┬───────────────────────────────┘
                          │
                          │ StudentEnrolled Event
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│ CourseManagement Context                                │
│                                                          │
│ StudentEnrolledListener receives event                  │
│         ↓                                                │
│ EnrollStudentHandler                                    │
│         ↓                                                │
│ $course->recordEnrollment(...)                          │
│         ↓                                                │
│ ✓ Enrollment record created (status: ACTIVE)            │
│ ✓ Can now track in reports                              │
│ ✓ Can later mark as COMPLETED/DROPPED                   │
│                                                          │
│ Later: Administrator can query                          │
│ - How many active? → $course->countActiveEnrollments()  │
│ - How many dropped? → $course->countDroppedEnrollments()│
│ - Get statistics → $course->getEnrollmentStatistics()   │
└─────────────────────────────────────────────────────────┘
```

---

## Real-World Usage Examples

### StudentLearning Context
```php
// Student portal: Can I access this course?
$student = Student::create(...);
$course = Course::create(...);

if ($course->hasLearningAccess($student)) {
    // Show course materials
    // Allow assignment submission
} else {
    // Show "Enroll" button
}

// Student tries to enroll
$course->grantLearningAccess($student);
// Now student can access learning materials
```

### CourseManagement Context
```php
// Administrator dashboard: Show enrollment report
$course = $courseRepository->findById($courseId);

$stats = $course->getEnrollmentStatistics();
// Returns:
// [
//     'active' => 45,
//     'completed' => 12,
//     'dropped' => 3,
//     'suspended' => 1,
//     'total' => 61
// ]

// Registrar: How many students completed the course?
$completedCount = $course->countCompletedEnrollments();

// Administration: Get list of all active enrollments
$activeEnrollments = $course->getActiveEnrollments();
foreach ($activeEnrollments as $enrollment) {
    echo $enrollment->getStudentName();
    echo $enrollment->getEnrolledAt()->format('Y-m-d');
}
```

---

## Key Takeaway

**This is NOT code duplication—it's intentional modeling!**

Each `Course` entity serves a **completely different purpose** aligned with its bounded context:

- **StudentLearning::Course** = "Who can learn in this course?"
- **CourseManagement::Course** = "How many enrolled? What are their statuses?"

They happen to share the same name because they both relate to the concept of a "course," but they represent **different perspectives** of the same domain concept.

This is **proper Domain-Driven Design**.

---

## Benefits of This Approach

1. ✅ **Clear Separation of Concerns**
   - StudentLearning doesn't care about enrollment statuses
   - CourseManagement doesn't care about learning materials

2. ✅ **Independent Evolution**
   - Can change StudentLearning without affecting CourseManagement
   - Can add new enrollment statuses without touching StudentLearning

3. ✅ **Aligned with Business Language**
   - Students talk about "learning access"
   - Administrators talk about "enrollment records"

4. ✅ **Testable**
   - Each context can be tested independently
   - Mock the event bus to isolate contexts

5. ✅ **Maintainable**
   - Each Course entity is focused on one responsibility
   - No god objects with too many concerns
