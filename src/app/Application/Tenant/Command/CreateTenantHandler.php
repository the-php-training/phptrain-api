<?php

declare(strict_types=1);

namespace App\Application\Tenant\Command;

use App\Application\Tenant\DTO\TenantDTO;
use App\Domain\Tenant\Entity\Tenant;
use App\Domain\Tenant\Repository\TenantRepositoryInterface;
use App\Domain\Tenant\ValueObject\ContactEmail;
use App\Domain\Tenant\ValueObject\TenantId;
use App\Domain\Tenant\ValueObject\TenantSlug;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Handler for CreateTenantCommand
 *
 * Orchestrates the creation of a new Tenant, including validation and persistence
 */
final readonly class CreateTenantHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private EventDispatcherInterface $eventDispatcher,
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

        // Dispatch domain events
        foreach ($tenant->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return TenantDTO::fromEntity($tenant);
    }
}
