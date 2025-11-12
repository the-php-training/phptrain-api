<?php

declare(strict_types=1);

namespace Tenant\Application\Query;

/**
 * Query to retrieve a Tenant by ID
 */
final readonly class GetTenantQuery
{
    public function __construct(
        public string $tenantId,
    ) {
    }
}
