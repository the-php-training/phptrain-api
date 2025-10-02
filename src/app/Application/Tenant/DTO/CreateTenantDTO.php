<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTO;

/**
 * Data Transfer Object for creating a new Tenant
 */
final readonly class CreateTenantDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $contactEmail,
        public ?string $contactPhone = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            contactEmail: $data['contact_email'] ?? '',
            contactPhone: $data['contact_phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
        ];
    }
}
