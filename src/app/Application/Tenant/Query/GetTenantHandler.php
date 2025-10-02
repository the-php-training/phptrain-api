<?php

declare(strict_types=1);

namespace App\Application\Tenant\Query;

use App\Application\Tenant\DTO\TenantDTO;
use App\Domain\Tenant\Repository\TenantRepositoryInterface;
use App\Domain\Tenant\ValueObject\TenantId;
use InvalidArgumentException;

/**
 * Handler for GetTenantQuery
 *
 * Retrieves a tenant by its ID
 */
final readonly class GetTenantHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function handle(GetTenantQuery $query): TenantDTO
    {
        $tenantId = TenantId::fromString($query->tenantId);
        $tenant = $this->tenantRepository->findById($tenantId);

        if ($tenant === null) {
            throw new InvalidArgumentException("Tenant with ID '{$query->tenantId}' not found");
        }

        return TenantDTO::fromEntity($tenant);
    }
}
