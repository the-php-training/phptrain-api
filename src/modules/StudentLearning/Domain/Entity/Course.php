<?php

declare(strict_types=1);

namespace StudentLearning\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use Shared\Domain\Traits\HasDomainEvent;
use StudentLearning\Domain\ValueObject\CourseId;
use StudentLearning\Domain\ValueObject\EnrollmentDate;
use StudentLearning\Events\StudentEnrolled;

/**
 * Course Aggregate Root (in StudentLearning context)
 *
 * RESPONSIBILITY: Manage student learning access and permissions
 *
 * This represents a course from the STUDENT'S LEARNING PERSPECTIVE:
 * - Who has access to course materials?
 * - Can a student enroll to gain learning access?
 * - Is there room for more learners?
 *
 * This is NOT about administrative enrollment tracking (that's CourseManagement's job).
 * This IS about controlling access to learning resources.
 */
class Course
{
    use HasDomainEvent;

    /** @var array<string, array{student: Student, enrolledAt: EnrollmentDate}> Students who have learning access */
    private array $enrolledStudents = [];

    private function __construct(
        private readonly CourseId $id,
        private string $title,
        private int $maxStudents,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->validateTitle($title);
        $this->validateMaxStudents($maxStudents);
    }

    /**
     * Create a new Course (Factory method)
     */
    public static function create(
        CourseId $id,
        string $title,
        int $maxStudents = 100,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            title: $title,
            maxStudents: $maxStudents,
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
        int $maxStudents,
        array $enrolledStudents,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        $course = new self(
            id: $id,
            title: $title,
            maxStudents: $maxStudents,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
        $course->enrolledStudents = $enrolledStudents;
        return $course;
    }

    /**
     * Grant a student learning access to this course
     *
     * LEARNING PERSPECTIVE: This gives the student permission to access course materials,
     * participate in learning activities, and view course content.
     *
     * Emits StudentEnrolled event for other contexts (like CourseManagement) to handle
     * their own concerns (administrative tracking, reporting, etc.)
     */
    public function grantLearningAccess(Student $student): void
    {
        // Business rules: Learning access control
        if ($this->hasLearningAccess($student)) {
            throw new InvalidArgumentException(
                "Student {$student->getId()->toString()} already has learning access to this course"
            );
        }

        if ($this->isAtCapacity()) {
            throw new InvalidArgumentException(
                "Course is at capacity. No more learners can be accepted (max: {$this->maxStudents})"
            );
        }

        // Grant access
        $enrollmentDate = EnrollmentDate::now();
        $this->enrolledStudents[$student->getId()->toString()] = [
            'student' => $student,
            'enrolledAt' => $enrollmentDate,
        ];

        $this->updatedAt = new DateTimeImmutable();

        // Notify other contexts that a student enrolled
        $this->recordEvent(new StudentEnrolled(
            courseId: $this->id->toString(),
            studentId: $student->getId()->toString(),
            studentName: $student->getName(),
            studentEmail: $student->getEmail(),
            enrolledAt: $enrollmentDate->toDateTime()
        ));
    }

    /**
     * Check if a student has learning access to this course
     *
     * Used to determine if student can view materials, submit assignments, etc.
     */
    public function hasLearningAccess(Student $student): bool
    {
        return isset($this->enrolledStudents[$student->getId()->toString()]);
    }

    /**
     * Check if course is at capacity for learners
     */
    public function isAtCapacity(): bool
    {
        return count($this->enrolledStudents) >= $this->maxStudents;
    }

    /**
     * Get number of students with learning access
     */
    public function getLearnerCount(): int
    {
        return count($this->enrolledStudents);
    }

    /**
     * Get available learning slots
     */
    public function getAvailableLearningSlots(): int
    {
        return $this->maxStudents - $this->getLearnerCount();
    }

    /**
     * Get all students who have learning access
     *
     * @return array<string, array{student: Student, enrolledAt: EnrollmentDate}>
     */
    public function getStudentsWithAccess(): array
    {
        return $this->enrolledStudents;
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

    public function getMaxStudents(): int
    {
        return $this->maxStudents;
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

    private function validateMaxStudents(int $maxStudents): void
    {
        if ($maxStudents <= 0) {
            throw new InvalidArgumentException('Max students must be greater than 0');
        }

        if ($maxStudents > 1000) {
            throw new InvalidArgumentException('Max students cannot exceed 1000');
        }
    }
}
