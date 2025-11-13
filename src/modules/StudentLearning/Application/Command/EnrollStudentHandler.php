<?php

declare(strict_types=1);

namespace StudentLearning\Application\Command;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shared\Domain\Event\IDomainEventBus;
use StudentLearning\Domain\Entity\Course;
use StudentLearning\Domain\Entity\Student;
use StudentLearning\Domain\Repository\ICourseRepository;
use StudentLearning\Domain\Repository\IStudentRepository;
use StudentLearning\Domain\ValueObject\CourseId;
use StudentLearning\Domain\ValueObject\StudentId;

/**
 * Handler for EnrollStudentCommand
 *
 * USE CASE: Grant a student learning access to a course
 *
 * This orchestrates the enrollment from the learning perspective:
 * - Verifies student and course exist
 * - Grants learning access
 * - Publishes StudentEnrolled event for other contexts
 */
final readonly class EnrollStudentHandler
{
    public function __construct(
        private ICourseRepository  $courseRepository,
        private IStudentRepository $studentRepository,
        private IDomainEventBus    $eventBus,
        private LoggerInterface    $logger,
    ) {
    }

    public function handle(EnrollStudentCommand $command): void
    {
        $courseId = CourseId::fromString($command->courseId);
        $studentId = StudentId::fromString($command->studentId);

        // Find student
        $student = $this->studentRepository->findById($studentId);
        if ($student === null) {
            throw new InvalidArgumentException("Student {$studentId->toString()} not found");
        }

        // Find course
        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            throw new InvalidArgumentException("Course {$courseId->toString()} not found");
        }

        try {
            // Grant learning access
            $course->grantLearningAccess($student);

            // Persist the course with updated enrollment
            $this->courseRepository->save($course);

            // Publish domain events (StudentEnrolled)
            $this->eventBus->publishEntity($course);

            $this->logger->info('Student granted learning access to course', [
                'course_id' => $courseId->toString(),
                'student_id' => $studentId->toString(),
                'student_name' => $student->getName(),
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Failed to grant learning access', [
                'course_id' => $courseId->toString(),
                'student_id' => $studentId->toString(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
