<?php

declare(strict_types=1);

namespace Tenant\Application\DTO;

use Tenant\Domain\Entity\Tenant;

/**
 * Data Transfer Object for Tenant representation
 */
final readonly class TenantDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $contactEmail,
        public ?string $contactPhone,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Tenant $tenant): self
    {
        return new self(
            id: $tenant->getId()->toString(),
            name: $tenant->getName(),
            slug: $tenant->getSlug()->toString(),
            contactEmail: $tenant->getContactEmail()->toString(),
            contactPhone: $tenant->getContactPhone(),
            status: $tenant->getStatus()->value,
            createdAt: $tenant->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $tenant->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
