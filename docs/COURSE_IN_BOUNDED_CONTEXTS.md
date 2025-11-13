# Course Meaning in Different Bounded Contexts

  The Key Insight: Same Name ≠ Same Concept

  In DDD, different bounded contexts can have entities with the same name but DIFFERENT responsibilities and meanings. This is not duplication—its intentional modeling of different perspectives.

### Course in StudentLearning Context

  Perspective: The students learning journey

  Responsibilities:
  - Track which students are enrolled (for learning purposes)
  - Manage enrollment capacity from a learning perspective
  - Track student progress, attendance, assignments
  - Handle learning materials access control
  - "Can this student access this course content?"

  Key Questions It Answers:
  - Is student X enrolled in this course?
  - How many students are learning in this course?
  - What materials can student X access?
  - What is student Xs progress in this course?

  Domain Language: "Learning", "Progress", "Materials", "Access"

  ### Course in CourseManagement Context

  Perspective: Administrative/management view

  Responsibilities:
  - Manage course catalog and details
  - Track instructor assignments
  - Handle scheduling and classrooms
  - Manage enrollment from administrative perspective (status, completion, drops)
  - Generate reports for administration
  - "Who is teaching this course? When and where?"

  Key Questions It Answers:
  - Who is the instructor?
  - What is the schedule?
  - How many enrollments are active/completed/dropped?
  - What is the capacity and availability?
  - Which courses are offered this semester?

  Domain Language: "Administration", "Scheduling", "Capacity", "Instructor", "Catalog"

  Real-World Analogy

  Think of a university:

  Student Portal (StudentLearning):
  - Shows "My Courses" with access to materials
  - "You are enrolled in 5 courses"
  - Shows your grades and progress
  - Focused on: "What am I learning?"

  Registrars Office (CourseManagement):
  - Manages course catalog
  - Assigns instructors to courses
  - Handles enrollment records and transcripts
  - Focused on: "How do we manage courses?"

  Same "Course" concept, but completely different concerns!

  How to Resolve the "Conflict"

  1. Different Attributes

  // StudentLearning\Domain\Entity\Course
  class Course {
      private CourseId $id;
      private string $title;
      private int $maxStudents;
      private array $enrolledStudents;    // For learning access
      private array $materials;           // Learning materials
      private array $assignments;         // Student assignments
      // Focus: What students can access and learn
  }

  // CourseManagement\Domain\Entity\Course
  class Course {
      private CourseId $id;
      private string $title;
      private string $description;
      private int $maxCapacity;
      private ?string $instructorId;      // Who teaches it
      private Schedule $schedule;         // When/where it happens
      private array $enrollments;         // Admin records (with status)
      // Focus: How the course is managed
  }

  2. Different Methods/Behavior

  // StudentLearning - from student perspective
  $course->enrollStudent($student);           // Give student access
  $course->canStudentAccessMaterial($student); // Check permissions
  $course->getStudentProgress($student);      // Track learning

  // CourseManagement - from admin perspective
  $course->assignInstructor($instructorId);   // Admin assigns teacher
  $course->updateSchedule($schedule);         // Admin sets schedule
  $course->generateEnrollmentReport();        // Admin reports

  3. Accept the "Duplication"

  This is NOT duplication in the bad sense. It's modeling different aspects of reality.

  Bad Duplication: Same code doing the same thing in multiple places
  Good Separation: Same concept modeled from different perspectives

  Why NOT Share a Single Course Entity?

  If you tried to create one "Course" entity for both contexts:

  class Course {
      // StudentLearning needs
      private array $materials;
      private array $studentProgress;

      // CourseManagement needs
      private string $instructorId;
      private Schedule $schedule;

      // Now it's a GOD OBJECT - too many responsibilities!
  }

  Problems:
  - ❌ Violates Single Responsibility Principle
  - ❌ Tight coupling between contexts
  - ❌ Changes in one context break the other
  - ❌ Cannot evolve independently
  - ❌ Confusing domain model

  Your Current Implementation is CORRECT!

  Looking at your code:

  StudentLearning/Course.php:84 - enrollStudent():
  - Focuses on giving student access
  - Records event for other contexts to react

  CourseManagement/Course.php:79 - enrollStudent():
  - Receives the event
  - Creates administrative enrollment record
  - Tracks enrollment status (active/completed/dropped)

  This is exactly how DDD is supposed to work!

  Communication via Events

  The contexts stay independent but synchronized via domain events:

  StudentLearning: "A student enrolled in my course (for learning)"
       ↓ (event)
  CourseManagement: "I'll record that enrollment (for administration)"

  Each context maintains its own model and truth about what "enrollment" means in their context.

  Mental Model Shift

  Stop thinking: "I have duplicate Course entities"

  Start thinking: "I have two different concepts that happen to share a name in English, but mean different things in different contexts"

  It's like:
  - "Order" in Sales vs "Order" in Shipping
  - "Product" in Catalog vs "Product" in Inventory
  - "Customer" in Marketing vs "Customer" in Billing

  Summary

  Your "conflict" is actually good DDD design!

  The key is to ensure each Course entity has clear, distinct responsibilities aligned with its bounded context. They may share some common data (like courseId, title) for reference, but their behaviors and concerns should remain separate.

  Does this help clarify the confusion?
