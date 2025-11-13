<?php

declare(strict_types=1);

namespace StudentLearning\Infrastructure\Persistence\Repository;

use DateTimeImmutable;
use StudentLearning\Domain\Entity\Student;
use StudentLearning\Domain\Repository\IStudentRepository;
use StudentLearning\Domain\ValueObject\StudentId;
use StudentLearning\Infrastructure\Persistence\Model\StudentModel;

/**
 * Student Repository Implementation (StudentLearning context)
 *
 * Translates between Domain Entities and Persistence Models
 */
final class StudentRepository implements IStudentRepository
{
    public function save(Student $student): void
    {
        $model = StudentModel::query()->find($student->getId()->toString());

        if ($model === null) {
            $model = new StudentModel();
            $model->id = $student->getId()->toString();
        }

        $model->name = $student->getName();
        $model->email = $student->getEmail();

        $model->save();
    }

    public function findById(StudentId $id): ?Student
    {
        $model = StudentModel::query()->find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function exists(StudentId $id): bool
    {
        return StudentModel::query()
            ->where('id', $id->toString())
            ->exists();
    }

    public function findByEmail(string $email): ?Student
    {
        $model = StudentModel::query()
            ->where('email', $email)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        $models = StudentModel::query()
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    /**
     * Convert Eloquent Model to Domain Entity
     */
    private function toDomain(StudentModel $model): Student
    {
        return Student::reconstitute(
            id: StudentId::fromString($model->id),
            name: $model->name,
            email: $model->email,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
