<?php

declare(strict_types=1);

namespace CourseManagement\Domain\Entity;

use CourseManagement\Domain\ValueObject\CourseId;
use CourseManagement\Domain\ValueObject\EnrollmentStatus;
use CourseManagement\Domain\ValueObject\StudentId;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Enrollment Entity
 *
 * Represents a student enrollment in a course from the CourseManagement perspective.
 * This is an entity within the Course aggregate.
 */
class Enrollment
{
    private function __construct(
        private readonly StudentId $studentId,
        private readonly CourseId $courseId,
        private readonly string $studentName,
        private readonly string $studentEmail,
        private EnrollmentStatus $status,
        private readonly DateTimeImmutable $enrolledAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $completedAt = null,
        private ?DateTimeImmutable $droppedAt = null,
    ) {
    }

    /**
     * Create a new Enrollment (Factory method)
     */
    public static function create(
        StudentId $studentId,
        CourseId $courseId,
        string $studentName,
        string $studentEmail,
        DateTimeImmutable $enrolledAt,
    ): self {
        return new self(
            studentId: $studentId,
            courseId: $courseId,
            studentName: $studentName,
            studentEmail: $studentEmail,
            status: EnrollmentStatus::ACTIVE,
            enrolledAt: $enrolledAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    /**
     * Reconstitute Enrollment from persistence
     */
    public static function reconstitute(
        StudentId $studentId,
        CourseId $courseId,
        string $studentName,
        string $studentEmail,
        EnrollmentStatus $status,
        DateTimeImmutable $enrolledAt,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $completedAt = null,
        ?DateTimeImmutable $droppedAt = null,
    ): self {
        return new self(
            studentId: $studentId,
            courseId: $courseId,
            studentName: $studentName,
            studentEmail: $studentEmail,
            status: $status,
            enrolledAt: $enrolledAt,
            updatedAt: $updatedAt,
            completedAt: $completedAt,
            droppedAt: $droppedAt,
        );
    }

    /**
     * Mark enrollment as completed
     */
    public function complete(): void
    {
        if (!$this->status->isActive()) {
            throw new InvalidArgumentException('Only active enrollments can be completed');
        }

        $this->status = EnrollmentStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Drop the enrollment
     */
    public function drop(): void
    {
        if ($this->status->isCompleted()) {
            throw new InvalidArgumentException('Cannot drop a completed enrollment');
        }

        if ($this->status->isDropped()) {
            throw new InvalidArgumentException('Enrollment is already dropped');
        }

        $this->status = EnrollmentStatus::DROPPED;
        $this->droppedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Suspend the enrollment
     */
    public function suspend(): void
    {
        if (!$this->status->isActive()) {
            throw new InvalidArgumentException('Only active enrollments can be suspended');
        }

        $this->status = EnrollmentStatus::SUSPENDED;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Reactivate a suspended enrollment
     */
    public function reactivate(): void
    {
        if (!$this->status->isSuspended()) {
            throw new InvalidArgumentException('Only suspended enrollments can be reactivated');
        }

        $this->status = EnrollmentStatus::ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getStudentId(): StudentId
    {
        return $this->studentId;
    }

    public function getCourseId(): CourseId
    {
        return $this->courseId;
    }

    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function getStudentEmail(): string
    {
        return $this->studentEmail;
    }

    public function getStatus(): EnrollmentStatus
    {
        return $this->status;
    }

    public function getEnrolledAt(): DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getDroppedAt(): ?DateTimeImmutable
    {
        return $this->droppedAt;
    }
}
