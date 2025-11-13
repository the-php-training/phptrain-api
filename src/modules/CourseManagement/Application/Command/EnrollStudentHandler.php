<?php

declare(strict_types=1);

namespace CourseManagement\Application\Command;

use CourseManagement\Domain\Entity\Course;
use CourseManagement\Domain\Repository\ICourseRepository;
use CourseManagement\Domain\ValueObject\CourseId;
use CourseManagement\Domain\ValueObject\StudentId;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Handler for EnrollStudentCommand
 *
 * USE CASE: Record enrollment in the administrative system
 *
 * This handler is called when a StudentEnrolled event arrives from StudentLearning context.
 * It records the enrollment for administrative tracking and reporting purposes.
 */
final readonly class EnrollStudentHandler
{
    public function __construct(
        private ICourseRepository $courseRepository,
        private LoggerInterface   $logger,
    ) {
    }

    public function handle(EnrollStudentCommand $command): void
    {
        $courseId = CourseId::fromString($command->courseId);
        $studentId = StudentId::fromString($command->studentId);
        $enrolledAt = new DateTimeImmutable($command->enrolledAt);

        // Find the course in our administrative system
        $course = $this->courseRepository->findById($courseId);

        if ($course === null) {
            // If course doesn't exist in CourseManagement context yet, create a placeholder
            // In a real system, you might want to sync course data from another source
            $course = Course::create(
                id: $courseId,
                title: 'Course Placeholder', // This should ideally come from another source
                description: 'Course created from enrollment event',
                maxCapacity: 100,
            );

            $this->logger->info('Created course placeholder from enrollment event', [
                'course_id' => $courseId->toString(),
            ]);
        }

        // Record the enrollment in our administrative system
        try {
            $course->recordEnrollment(
                studentId: $studentId,
                studentName: $command->studentName,
                studentEmail: $command->studentEmail,
                enrolledAt: $enrolledAt
            );

            // Persist the course with the new enrollment record
            $this->courseRepository->save($course);

            $this->logger->info('Enrollment recorded in administrative system', [
                'course_id' => $courseId->toString(),
                'student_id' => $studentId->toString(),
                'student_name' => $command->studentName,
                'enrolled_at' => $enrolledAt->format('Y-m-d H:i:s'),
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Failed to record enrollment', [
                'course_id' => $courseId->toString(),
                'student_id' => $studentId->toString(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
