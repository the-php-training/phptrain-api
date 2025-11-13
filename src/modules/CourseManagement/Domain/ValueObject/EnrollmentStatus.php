<?php

declare(strict_types=1);

namespace CourseManagement\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Enrollment Status Value Object
 *
 * Represents the status of a student's enrollment in a course
 */
enum EnrollmentStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case DROPPED = 'dropped';
    case SUSPENDED = 'suspended';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'active' => self::ACTIVE,
            'completed' => self::COMPLETED,
            'dropped' => self::DROPPED,
            'suspended' => self::SUSPENDED,
            default => throw new InvalidArgumentException("Invalid enrollment status: {$value}")
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isDropped(): bool
    {
        return $this === self::DROPPED;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function canAttendClasses(): bool
    {
        return $this === self::ACTIVE;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
