<?php

declare(strict_types=1);

namespace StudentLearning\Application\Command;

/**
 * Command: Enroll Student
 *
 * Represents the intention for a student to enroll in a course
 * within the StudentLearning bounded context (to gain learning access)
 */
final readonly class EnrollStudentCommand
{
    public function __construct(
        public string $courseId,
        public string $studentId,
    ) {
    }
}
