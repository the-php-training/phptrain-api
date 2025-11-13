<?php

declare(strict_types=1);

namespace Tenant\Application\Command;

use InvalidArgumentException;
use Shared\Domain\Event\IDomainEventBus;
use Tenant\Application\DTO\TenantDTO;
use Tenant\Domain\Entity\Tenant;
use Tenant\Domain\Repository\ITenantRepository;
use Tenant\Domain\ValueObject\ContactEmail;
use Tenant\Domain\ValueObject\TenantId;
use Tenant\Domain\ValueObject\TenantSlug;

/**
 * Handler for CreateTenantCommand
 *
 * Orchestrates the creation of a new Tenant, including validation and persistence
 */
final readonly class CreateTenantHandler
{
    public function __construct(
        private ITenantRepository $tenantRepository,
        private IDomainEventBus   $eventBus,
    ) {
    }

    public function handle(CreateTenantCommand $command): TenantDTO
    {
        // Validate slug uniqueness
        $slug = TenantSlug::fromString($command->slug);
        if ($this->tenantRepository->slugExists($slug)) {
            throw new InvalidArgumentException("Tenant with slug '{$command->slug}' already exists");
        }

        // Create tenant
        $tenant = Tenant::create(
            id: TenantId::generate(),
            name: $command->name,
            slug: $slug,
            contactEmail: ContactEmail::fromString($command->contactEmail),
            contactPhone: $command->contactPhone,
        );

        // Persist tenant
        $this->tenantRepository->save($tenant);

        // Publish domain events
        $this->eventBus->publishEntity($tenant);

        return TenantDTO::fromEntity($tenant);
    }
}
