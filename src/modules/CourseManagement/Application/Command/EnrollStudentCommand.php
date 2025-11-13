<?php

declare(strict_types=1);

namespace CourseManagement\Application\Command;

/**
 * Command: Enroll Student
 *
 * Represents the intention to enroll a student in a course
 * within the CourseManagement bounded context
 */
final readonly class EnrollStudentCommand
{
    public function __construct(
        public string $courseId,
        public string $studentId,
        public string $studentName,
        public string $studentEmail,
        public string $enrolledAt,
    ) {
    }
}
