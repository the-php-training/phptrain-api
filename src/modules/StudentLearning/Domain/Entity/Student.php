<?php

declare(strict_types=1);

namespace StudentLearning\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use StudentLearning\Domain\ValueObject\StudentId;

/**
 * Student Entity
 *
 * Represents a student in the learning context.
 * This is a simple entity, not an aggregate root.
 */
class Student
{
    private function __construct(
        private readonly StudentId $id,
        private string $name,
        private string $email,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->validateName($name);
        $this->validateEmail($email);
    }

    /**
     * Create a new Student (Factory method)
     */
    public static function create(
        StudentId $id,
        string $name,
        string $email,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            name: $name,
            email: $email,
            createdAt: $now,
            updatedAt: $now
        );
    }

    /**
     * Reconstitute Student from persistence (for Repository)
     */
    public static function reconstitute(
        StudentId $id,
        string $name,
        string $email,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    /**
     * Update student information
     */
    public function updateInfo(string $name, string $email): void
    {
        $this->validateName($name);
        $this->validateEmail($email);

        $this->name = $name;
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): StudentId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Validation
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Student name cannot be empty');
        }

        if (strlen($name) < 2) {
            throw new InvalidArgumentException('Student name must be at least 2 characters long');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Student name cannot exceed 255 characters');
        }
    }

    private function validateEmail(string $email): void
    {
        if (empty(trim($email))) {
            throw new InvalidArgumentException('Student email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }
}
