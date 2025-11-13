<?php

declare(strict_types=1);

namespace CourseManagement\Domain\Repository;

use CourseManagement\Domain\Entity\Course;
use CourseManagement\Domain\ValueObject\CourseId;

/**
 * Course Repository Interface
 *
 * Defines the contract for Course persistence in the CourseManagement context
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
     * Delete a course
     */
    public function delete(CourseId $id): void;

    /**
     * Find all courses
     *
     * @return Course[]
     */
    public function findAll(): array;
}
