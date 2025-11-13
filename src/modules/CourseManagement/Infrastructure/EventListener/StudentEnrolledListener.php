<?php

declare(strict_types=1);

namespace CourseManagement\Infrastructure\EventListener;

use CourseManagement\Application\Command\EnrollStudentCommand;
use CourseManagement\Application\Command\EnrollStudentHandler;
use StudentLearning\Events\StudentEnrolled;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * Listener for StudentEnrolled domain event
 *
 * Handles the cross-bounded-context enrollment process.
 * When a student enrolls in a course in the StudentLearning context,
 * this listener synchronously processes the event to record the enrollment
 * in the CourseManagement context.
 *
 * This is a synchronous listener (no queues) as requested.
 */
#[Listener]
class StudentEnrolledListener implements ListenerInterface
{
    public function __construct(
        private readonly EnrollStudentHandler $enrollStudentHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function listen(): array
    {
        return [
            StudentEnrolled::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof StudentEnrolled) {
            return;
        }

        $this->logger->info('Processing StudentEnrolled event in CourseManagement context', [
            'course_id' => $event->courseId,
            'student_id' => $event->studentId,
            'student_name' => $event->studentName,
            'enrolled_at' => $event->enrolledAt->format('Y-m-d H:i:s'),
        ]);

        try {
            // Create command from event data
            $command = new EnrollStudentCommand(
                courseId: $event->courseId,
                studentId: $event->studentId,
                studentName: $event->studentName,
                studentEmail: $event->studentEmail,
                enrolledAt: $event->enrolledAt->format('Y-m-d H:i:s'),
            );

            // Execute the enrollment use case
            $this->enrollStudentHandler->handle($command);

            $this->logger->info('Successfully processed StudentEnrolled event', [
                'course_id' => $event->courseId,
                'student_id' => $event->studentId,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to process StudentEnrolled event', [
                'course_id' => $event->courseId,
                'student_id' => $event->studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to ensure the original enrollment fails if we can't record it
            // in CourseManagement context
            throw $e;
        }
    }
}
