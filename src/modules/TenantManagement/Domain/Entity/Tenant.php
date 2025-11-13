<?php

declare(strict_types=1);

namespace Tenant\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use Shared\Domain\Traits\HasDomainEvent;
use Tenant\Domain\Event\TenantCreated;
use Tenant\Domain\ValueObject\ContactEmail;
use Tenant\Domain\ValueObject\TenantId;
use Tenant\Domain\ValueObject\TenantSlug;
use Tenant\Domain\ValueObject\TenantStatus;

/**
 * Tenant Aggregate Root
 *
 * Represents an organization/institution that uses the platform to manage courses.
 * Each tenant has complete data isolation and independent configuration.
 */
class Tenant
{
    use HasDomainEvent;

    private function __construct(
        private readonly TenantId $id,
        private string $name,
        private readonly TenantSlug $slug,
        private ContactEmail $contactEmail,
        private ?string $contactPhone,
        private TenantStatus $status,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->validateName($name);
    }

    /**
     * Create a new Tenant (Factory method)
     */
    public static function create(
        TenantId $id,
        string $name,
        TenantSlug $slug,
        ContactEmail $contactEmail,
        ?string $contactPhone = null,
        ?TenantStatus $status = null
    ): self {
        $now = new DateTimeImmutable();
        $status = $status ?? TenantStatus::PENDING;

        $tenant = new self(
            id: $id,
            name: $name,
            slug: $slug,
            contactEmail: $contactEmail,
            contactPhone: $contactPhone,
            status: $status,
            createdAt: $now,
            updatedAt: $now
        );

        $tenant->recordEvent(new TenantCreated(
            tenantId: $id->toString(),
            name: $name,
            slug: $slug->toString(),
            occurredAt: $now
        ));

        return $tenant;
    }

    /**
     * Reconstitute Tenant from persistence (for Repository)
     */
    public static function reconstitute(
        TenantId $id,
        string $name,
        TenantSlug $slug,
        ContactEmail $contactEmail,
        ?string $contactPhone,
        TenantStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            contactEmail: $contactEmail,
            contactPhone: $contactPhone,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    /**
     * Activate the tenant
     */
    public function activate(): void
    {
        if ($this->status->isActive()) {
            throw new InvalidArgumentException('Tenant is already active');
        }

        $this->status = TenantStatus::ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Suspend the tenant
     */
    public function suspend(): void
    {
        if ($this->status === TenantStatus::SUSPENDED) {
            throw new InvalidArgumentException('Tenant is already suspended');
        }

        $this->status = TenantStatus::SUSPENDED;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Deactivate the tenant
     */
    public function deactivate(): void
    {
        $this->status = TenantStatus::INACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Update tenant information
     */
    public function updateInfo(string $name, ContactEmail $contactEmail, ?string $contactPhone = null): void
    {
        $this->validateName($name);

        $this->name = $name;
        $this->contactEmail = $contactEmail;
        $this->contactPhone = $contactPhone;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Check if tenant can access the platform
     */
    public function canAccessPlatform(): bool
    {
        return $this->status->canAccessPlatform();
    }

    // Getters
    public function getId(): TenantId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): TenantSlug
    {
        return $this->slug;
    }

    public function getContactEmail(): ContactEmail
    {
        return $this->contactEmail;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function getStatus(): TenantStatus
    {
        return $this->status;
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
            throw new InvalidArgumentException('Tenant name cannot be empty');
        }

        if (strlen($name) < 3) {
            throw new InvalidArgumentException('Tenant name must be at least 3 characters long');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Tenant name cannot exceed 255 characters');
        }
    }
}
