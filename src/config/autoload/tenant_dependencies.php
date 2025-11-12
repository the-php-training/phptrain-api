<?php

declare(strict_types=1);

/**
 * Tenant Management Dependency Configuration
 *
 * Maps interfaces to concrete implementations for dependency injection
 */

use Tenant\Domain\Repository\TenantRepositoryInterface;
use Tenant\Infrastructure\Persistence\Repository\TenantRepository;

return [
    TenantRepositoryInterface::class => TenantRepository::class,
];
