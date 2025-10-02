<?php

declare(strict_types=1);

namespace App\Domain\Tenant\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a unique identifier for a Tenant
 *
 * Ensures that every tenant has a valid, non-empty unique identifier
 */
final readonly class TenantId
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function generate(): self
    {
        return new self((string) \Hyperf\Stringable\Str::uuid());
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TenantId $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Tenant ID cannot be empty');
        }

        if (strlen($this->value) > 36) {
            throw new InvalidArgumentException('Tenant ID cannot exceed 36 characters');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
