<?php

declare(strict_types=1);

namespace Tenant\Application\Query;

use Tenant\Application\DTO\TenantDTO;
use Tenant\Domain\Repository\ITenantRepository;

/**
 * Handler for ListTenantsQuery
 *
 * Retrieves a paginated list of tenants
 */
final readonly class ListTenantsHandler
{
    public function __construct(
        private ITenantRepository $tenantRepository,
    ) {
    }

    /**
     * @return array{data: array<TenantDTO>, total: int, limit: int, offset: int}
     */
    public function handle(ListTenantsQuery $query): array
    {
        $tenants = $this->tenantRepository->findAll($query->limit, $query->offset);
        $total = $this->tenantRepository->count();

        return [
            'data' => array_map(
                fn($tenant) => TenantDTO::fromEntity($tenant),
                $tenants
            ),
            'total' => $total,
            'limit' => $query->limit,
            'offset' => $query->offset,
        ];
    }
}
