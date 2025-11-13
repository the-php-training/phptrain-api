<?php

declare(strict_types=1);

namespace Tenant\Application\Query;

/**
 * Query to list all tenants with pagination
 */
final readonly class ListTenantsQuery
{
    public function __construct(
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
