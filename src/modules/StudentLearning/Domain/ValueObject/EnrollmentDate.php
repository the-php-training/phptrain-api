<?php

declare(strict_types=1);

namespace StudentLearning\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Enrollment Date Value Object
 *
 * Represents the date when a student enrolled in a course
 */
final readonly class EnrollmentDate
{
    private function __construct(
        private DateTimeImmutable $value
    ) {
        $this->validate($value);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    public static function fromDateTime(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        return new self(new DateTimeImmutable($value));
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function isBefore(EnrollmentDate $other): bool
    {
        return $this->value < $other->value;
    }

    public function isAfter(EnrollmentDate $other): bool
    {
        return $this->value > $other->value;
    }

    private function validate(DateTimeImmutable $value): void
    {
        $now = new DateTimeImmutable();
        if ($value > $now) {
            throw new InvalidArgumentException('Enrollment date cannot be in the future');
        }
    }
}
