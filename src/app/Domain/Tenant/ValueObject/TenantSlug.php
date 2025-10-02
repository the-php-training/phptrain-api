<?php

declare(strict_types=1);

namespace App\Domain\Tenant\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a unique URL-friendly identifier for a Tenant
 *
 * Used for subdomain/path identification (e.g., acme-university.platform.com)
 */
final readonly class TenantSlug
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const MIN_LENGTH = 3;
    private const MAX_LENGTH = 50;

    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $slug): self
    {
        return new self(strtolower(trim($slug)));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TenantSlug $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Tenant slug cannot be empty');
        }

        if (strlen($this->value) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Tenant slug must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if (strlen($this->value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Tenant slug cannot exceed %d characters', self::MAX_LENGTH)
            );
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw new InvalidArgumentException(
                'Tenant slug must contain only lowercase letters, numbers, and hyphens'
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
