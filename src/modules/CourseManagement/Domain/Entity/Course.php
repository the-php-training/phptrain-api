<?php

declare(strict_types=1);

namespace CourseManagement\Domain\Entity;

use CourseManagement\Domain\ValueObject\CourseId;
use CourseManagement\Domain\ValueObject\StudentId;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Course Aggregate Root (in CourseManagement context)
 *
 * RESPONSIBILITY: Administrative enrollment tracking and reporting
 *
 * This represents a course from the ADMINISTRATIVE/MANAGEMENT PERSPECTIVE:
 * - How many enrollments are active/completed/dropped?
 * - Who is the instructor?
 * - What is the enrollment capacity?
 * - Generate enrollment reports for administration
 *
 * This is NOT about learning access (that's StudentLearning's job).
 * This IS about tracking enrollment records, statuses, and administrative data.
 */
class Course
{
    /** @var array<string, Enrollment> Administrative enrollment records */
    private array $enrollments = [];

    private function __construct(
        private readonly CourseId $id,
        private string $title,
        private string $description,
        private int $maxCapacity,
        private ?string $instructorId,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->validateTitle($title);
        $this->validateMaxCapacity($maxCapacity);
    }

    /**
     * Create a new Course (Factory method)
     */
    public static function create(
        CourseId $id,
        string $title,
        string $description,
        int $maxCapacity = 100,
        ?string $instructorId = null,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            title: $title,
            description: $description,
            maxCapacity: $maxCapacity,
            instructorId: $instructorId,
            createdAt: $now,
            updatedAt: $now
        );
    }

    /**
     * Reconstitute Course from persistence (for Repository)
     */
    public static function reconstitute(
        CourseId $id,
        string $title,
        string $description,
        int $maxCapacity,
        ?string $instructorId,
        array $enrollments,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        $course = new self(
            id: $id,
            title: $title,
            description: $description,
            maxCapacity: $maxCapacity,
            instructorId: $instructorId,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
        $course->enrollments = $enrollments;
        return $course;
    }

    /**
     * Record an enrollment in the administrative system
     *
     * ADMINISTRATIVE PERSPECTIVE: This creates an enrollment record for tracking,
     * reporting, and administrative purposes.
     *
     * Called when StudentLearning context grants learning access and emits StudentEnrolled event.
     */
    public function recordEnrollment(
        StudentId $studentId,
        string $studentName,
        string $studentEmail,
        DateTimeImmutable $enrolledAt
    ): void {
        // Business rules: Administrative record keeping
        if ($this->hasEnrollmentRecord($studentId)) {
            throw new InvalidArgumentException(
                "Enrollment record for student {$studentId->toString()} already exists"
            );
        }

        if ($this->isAtMaxCapacity()) {
            throw new InvalidArgumentException(
                "Course is at maximum capacity. Cannot record more enrollments (max: {$this->maxCapacity})"
            );
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
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Check if an enrollment record exists for a student
     */
    public function hasEnrollmentRecord(StudentId $studentId): bool
    {
        return isset($this->enrollments[$studentId->toString()]);
    }

    /**
     * Get an enrollment record by student ID
     */
    public function getEnrollmentRecord(StudentId $studentId): ?Enrollment
    {
        return $this->enrollments[$studentId->toString()] ?? null;
    }

    /**
     * Check if course is at maximum capacity (based on active enrollments)
     */
    public function isAtMaxCapacity(): bool
    {
        return $this->countActiveEnrollments() >= $this->maxCapacity;
    }

    // ========================================================================
    // ENROLLMENT TRACKING & REPORTING
    // Answer: "How many enrollments are active/completed/dropped?"
    // ========================================================================

    /**
     * Count enrollments by status: ACTIVE
     *
     * These are students currently enrolled and can attend classes.
     */
    public function countActiveEnrollments(): int
    {
        return count(array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isActive()
        ));
    }

    /**
     * Count enrollments by status: COMPLETED
     *
     * These are students who finished the course successfully.
     */
    public function countCompletedEnrollments(): int
    {
        return count(array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isCompleted()
        ));
    }

    /**
     * Count enrollments by status: DROPPED
     *
     * These are students who dropped out of the course.
     */
    public function countDroppedEnrollments(): int
    {
        return count(array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isDropped()
        ));
    }

    /**
     * Count enrollments by status: SUSPENDED
     *
     * These are students temporarily suspended from the course.
     */
    public function countSuspendedEnrollments(): int
    {
        return count(array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isSuspended()
        ));
    }

    /**
     * Get total enrollment count (all statuses)
     */
    public function getTotalEnrollmentCount(): int
    {
        return count($this->enrollments);
    }

    /**
     * Get enrollment statistics summary
     *
     * Returns a summary of enrollments by status for reporting.
     *
     * @return array{active: int, completed: int, dropped: int, suspended: int, total: int}
     */
    public function getEnrollmentStatistics(): array
    {
        return [
            'active' => $this->countActiveEnrollments(),
            'completed' => $this->countCompletedEnrollments(),
            'dropped' => $this->countDroppedEnrollments(),
            'suspended' => $this->countSuspendedEnrollments(),
            'total' => $this->getTotalEnrollmentCount(),
        ];
    }

    /**
     * Get available capacity (max capacity - active enrollments)
     */
    public function getAvailableCapacity(): int
    {
        return $this->maxCapacity - $this->countActiveEnrollments();
    }

    /**
     * Get all active enrollment records
     *
     * @return Enrollment[]
     */
    public function getActiveEnrollments(): array
    {
        return array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isActive()
        );
    }

    /**
     * Get all completed enrollment records
     *
     * @return Enrollment[]
     */
    public function getCompletedEnrollments(): array
    {
        return array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isCompleted()
        );
    }

    /**
     * Get all dropped enrollment records
     *
     * @return Enrollment[]
     */
    public function getDroppedEnrollments(): array
    {
        return array_filter(
            $this->enrollments,
            fn(Enrollment $e) => $e->getStatus()->isDropped()
        );
    }

    /**
     * Update course information
     */
    public function updateInfo(string $title, string $description, ?string $instructorId = null): void
    {
        $this->validateTitle($title);

        $this->title = $title;
        $this->description = $description;
        $this->instructorId = $instructorId;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): CourseId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMaxCapacity(): int
    {
        return $this->maxCapacity;
    }

    public function getInstructorId(): ?string
    {
        return $this->instructorId;
    }

    /**
     * @return array<string, Enrollment>
     */
    public function getEnrollments(): array
    {
        return $this->enrollments;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Validation
    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Course title cannot be empty');
        }

        if (strlen($title) < 3) {
            throw new InvalidArgumentException('Course title must be at least 3 characters long');
        }

        if (strlen($title) > 255) {
            throw new InvalidArgumentException('Course title cannot exceed 255 characters');
        }
    }

    private function validateMaxCapacity(int $maxCapacity): void
    {
        if ($maxCapacity <= 0) {
            throw new InvalidArgumentException('Max capacity must be greater than 0');
        }

        if ($maxCapacity > 1000) {
            throw new InvalidArgumentException('Max capacity cannot exceed 1000');
        }
    }
}
