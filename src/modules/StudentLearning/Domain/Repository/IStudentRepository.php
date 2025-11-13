<?php

declare(strict_types=1);

namespace StudentLearning\Domain\Repository;

use StudentLearning\Domain\Entity\Student;
use StudentLearning\Domain\ValueObject\StudentId;

/**
 * Student Repository Interface
 *
 * Defines the contract for Student persistence
 */
interface IStudentRepository
{
    /**
     * Save a student (create or update)
     */
    public function save(Student $student): void;

    /**
     * Find a student by their ID
     */
    public function findById(StudentId $id): ?Student;

    /**
     * Check if a student exists by ID
     */
    public function exists(StudentId $id): bool;

    /**
     * Find student by email
     */
    public function findByEmail(string $email): ?Student;

    /**
     * Find all students
     *
     * @return Student[]
     */
    public function findAll(): array;
}
