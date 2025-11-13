<?php

declare(strict_types=1);

namespace Tenant\Application\Query;

use Tenant\Application\DTO\TenantDTO;
use Tenant\Domain\Repository\ITenantRepository;
use Tenant\Domain\ValueObject\TenantId;
use InvalidArgumentException;

/**
 * Handler for GetTenantQuery
 *
 * Retrieves a tenant by its ID
 */
final readonly class GetTenantHandler
{
    public function __construct(
        private ITenantRepository $tenantRepository,
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
