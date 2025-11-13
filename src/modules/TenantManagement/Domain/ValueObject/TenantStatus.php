<?php

declare(strict_types=1);

namespace Tenant\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing the operational status of a Tenant
 */
enum TenantStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canAccessPlatform(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function fromString(string $status): self
    {
        return self::tryFrom(strtolower($status))
            ?? throw new InvalidArgumentException("Invalid tenant status: {$status}");
    }
}
