<?php

declare(strict_types=1);

namespace StudentLearning\Domain\Repository;

use StudentLearning\Domain\Entity\Course;
use StudentLearning\Domain\ValueObject\CourseId;

/**
 * Course Repository Interface (StudentLearning context)
 *
 * Defines the contract for Course persistence from learning perspective
 */
interface ICourseRepository
{
    /**
     * Save a course (create or update)
     */
    public function save(Course $course): void;

    /**
     * Find a course by its ID
     */
    public function findById(CourseId $id): ?Course;

    /**
     * Check if a course exists by ID
     */
    public function exists(CourseId $id): bool;

    /**
     * Find all courses
     *
     * @return Course[]
     */
    public function findAll(): array;
}
