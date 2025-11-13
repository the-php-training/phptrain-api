<?php

declare(strict_types=1);

namespace StudentLearning\Events;

use DateTimeImmutable;
use Shared\Domain\Event\DomainEvent;

/**
 * Domain Event: Student Enrolled
 *
 * Emitted when a student enrolls in a course within the StudentLearning context.
 * This event is consumed synchronously by the CourseManagement bounded context
 * to record the enrollment from the course management perspective.
 */
final readonly class StudentEnrolled implements DomainEvent
{
    public function __construct(
        public string $courseId,
        public string $studentId,
        public string $studentName,
        public string $studentEmail,
        public DateTimeImmutable $enrolledAt,
    ) {
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function eventName(): string
    {
        return 'student_learning.student_enrolled';
    }

    public function toArray(): array
    {
        return [
            'course_id' => $this->courseId,
            'student_id' => $this->studentId,
            'student_name' => $this->studentName,
            'student_email' => $this->studentEmail,
            'enrolled_at' => $this->enrolledAt->format('Y-m-d H:i:s'),
        ];
    }
}
