<?php

declare(strict_types=1);

namespace CourseManagement\Infrastructure\Persistence\Repository;

use CourseManagement\Domain\Entity\Course;
use CourseManagement\Domain\Entity\Enrollment;
use CourseManagement\Domain\Repository\ICourseRepository;
use CourseManagement\Domain\ValueObject\CourseId;
use CourseManagement\Domain\ValueObject\EnrollmentStatus;
use CourseManagement\Domain\ValueObject\StudentId;
use CourseManagement\Infrastructure\Persistence\Model\CourseModel;
use CourseManagement\Infrastructure\Persistence\Model\EnrollmentModel;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * Course Repository Implementation (CourseManagement context)
 *
 * Translates between Domain Entities and Persistence Models
 */
final class CourseRepository implements ICourseRepository
{
    public function save(Course $course): void
    {
        // Save course
        $courseModel = CourseModel::query()->find($course->getId()->toString());

        if ($courseModel === null) {
            $courseModel = new CourseModel();
            $courseModel->id = $course->getId()->toString();
        }

        $courseModel->title = $course->getTitle();
        $courseModel->description = $course->getDescription();
        $courseModel->max_capacity = $course->getMaxCapacity();
        $courseModel->instructor_id = $course->getInstructorId();

        $courseModel->save();

        // Save enrollments
        $this->saveEnrollments($course);
    }

    public function findById(CourseId $id): ?Course
    {
        $model = CourseModel::query()
            ->with('enrollments')
            ->find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function exists(CourseId $id): bool
    {
        return CourseModel::query()
            ->where('id', $id->toString())
            ->exists();
    }

    public function delete(CourseId $id): void
    {
        // Delete enrollments first (cascade)
        EnrollmentModel::query()
            ->where('course_id', $id->toString())
            ->delete();

        // Delete course
        CourseModel::query()
            ->where('id', $id->toString())
            ->delete();
    }

    public function findAll(): array
    {
        $models = CourseModel::query()
            ->with('enrollments')
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    /**
     * Save enrollments for a course
     */
    private function saveEnrollments(Course $course): void
    {
        foreach ($course->getEnrollments() as $enrollment) {
            $enrollmentModel = EnrollmentModel::query()
                ->where('course_id', $course->getId()->toString())
                ->where('student_id', $enrollment->getStudentId()->toString())
                ->first();

            if ($enrollmentModel === null) {
                $enrollmentModel = new EnrollmentModel();
                $enrollmentModel->id = Uuid::uuid4()->toString();
                $enrollmentModel->course_id = $course->getId()->toString();
                $enrollmentModel->student_id = $enrollment->getStudentId()->toString();
            }

            $enrollmentModel->student_name = $enrollment->getStudentName();
            $enrollmentModel->student_email = $enrollment->getStudentEmail();
            $enrollmentModel->status = $enrollment->getStatus()->value;
            $enrollmentModel->enrolled_at = $enrollment->getEnrolledAt();
            $enrollmentModel->completed_at = $enrollment->getCompletedAt();
            $enrollmentModel->dropped_at = $enrollment->getDroppedAt();

            $enrollmentModel->save();
        }
    }

    /**
     * Convert Eloquent Model to Domain Entity
     */
    private function toDomain(CourseModel $model): Course
    {
        // Convert enrollment models to entities
        $enrollments = [];
        foreach ($model->enrollments as $enrollmentModel) {
            $enrollment = Enrollment::reconstitute(
                studentId: StudentId::fromString($enrollmentModel->student_id),
                courseId: CourseId::fromString($enrollmentModel->course_id),
                studentName: $enrollmentModel->student_name,
                studentEmail: $enrollmentModel->student_email,
                status: EnrollmentStatus::fromString($enrollmentModel->status),
                enrolledAt: DateTimeImmutable::createFromMutable($enrollmentModel->enrolled_at),
                updatedAt: DateTimeImmutable::createFromMutable($enrollmentModel->updated_at),
                completedAt: $enrollmentModel->completed_at
                    ? DateTimeImmutable::createFromMutable($enrollmentModel->completed_at)
                    : null,
                droppedAt: $enrollmentModel->dropped_at
                    ? DateTimeImmutable::createFromMutable($enrollmentModel->dropped_at)
                    : null,
            );

            $enrollments[$enrollmentModel->student_id] = $enrollment;
        }

        return Course::reconstitute(
            id: CourseId::fromString($model->id),
            title: $model->title,
            description: $model->description,
            maxCapacity: $model->max_capacity,
            instructorId: $model->instructor_id,
            enrollments: $enrollments,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
