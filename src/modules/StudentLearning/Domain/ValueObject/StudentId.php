<?php

declare(strict_types=1);

namespace StudentLearning\Domain\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Student ID Value Object
 *
 * Represents a unique identifier for a student
 */
final readonly class StudentId
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(StudentId $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(string $value): void
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID format for StudentId');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
