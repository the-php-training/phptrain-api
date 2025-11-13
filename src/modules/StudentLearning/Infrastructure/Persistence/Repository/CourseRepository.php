<?php

declare(strict_types=1);

namespace StudentLearning\Infrastructure\Persistence\Repository;

use DateTimeImmutable;
use StudentLearning\Domain\Entity\Course;
use StudentLearning\Domain\Entity\Student;
use StudentLearning\Domain\Repository\ICourseRepository;
use StudentLearning\Domain\ValueObject\CourseId;
use StudentLearning\Domain\ValueObject\EnrollmentDate;
use StudentLearning\Domain\ValueObject\StudentId;
use StudentLearning\Infrastructure\Persistence\Model\CourseModel;

/**
 * Course Repository Implementation (StudentLearning context)
 *
 * Translates between Domain Entities and Persistence Models
 */
final class CourseRepository implements ICourseRepository
{
    public function save(Course $course): void
    {
        $model = CourseModel::query()->find($course->getId()->toString());

        if ($model === null) {
            $model = new CourseModel();
            $model->id = $course->getId()->toString();
        }

        $model->title = $course->getTitle();
        $model->max_students = $course->getMaxStudents();

        // Serialize enrolled students to JSON
        $enrolledStudents = [];
        foreach ($course->getStudentsWithAccess() as $studentId => $enrollmentData) {
            $enrolledStudents[$studentId] = [
                'student_id' => $enrollmentData['student']->getId()->toString(),
                'student_name' => $enrollmentData['student']->getName(),
                'student_email' => $enrollmentData['student']->getEmail(),
                'enrolled_at' => $enrollmentData['enrolledAt']->toDateTime()->format('Y-m-d H:i:s'),
            ];
        }
        $model->enrolled_students = $enrolledStudents;

        $model->save();
    }

    public function findById(CourseId $id): ?Course
    {
        $model = CourseModel::query()->find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function exists(CourseId $id): bool
    {
        return CourseModel::query()
            ->where('id', $id->toString())
            ->exists();
    }

    public function findAll(): array
    {
        $models = CourseModel::query()
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    /**
     * Convert Eloquent Model to Domain Entity
     */
    private function toDomain(CourseModel $model): Course
    {
        // Deserialize enrolled students from JSON
        $enrolledStudents = [];
        if (!empty($model->enrolled_students)) {
            foreach ($model->enrolled_students as $studentId => $enrollmentData) {
                // Reconstitute Student entity from stored data
                $student = Student::reconstitute(
                    id: StudentId::fromString($enrollmentData['student_id']),
                    name: $enrollmentData['student_name'],
                    email: $enrollmentData['student_email'],
                    createdAt: new DateTimeImmutable(), // Not critical for this context
                    updatedAt: new DateTimeImmutable(),
                );

                $enrolledStudents[$studentId] = [
                    'student' => $student,
                    'enrolledAt' => EnrollmentDate::fromString($enrollmentData['enrolled_at']),
                ];
            }
        }

        return Course::reconstitute(
            id: CourseId::fromString($model->id),
            title: $model->title,
            maxStudents: $model->max_students,
            enrolledStudents: $enrolledStudents,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
