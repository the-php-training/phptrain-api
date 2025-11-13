<?php

declare(strict_types=1);

namespace Tenant\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a contact email address
 */
final readonly class ContactEmail
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $email): self
    {
        return new self(strtolower(trim($email)));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(ContactEmail $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Contact email cannot be empty');
        }

        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$this->value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
