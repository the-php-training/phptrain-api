<?php

declare(strict_types=1);

/**
 * Tenant Management Dependency Configuration
 *
 * Maps interfaces to concrete implementations for dependency injection
 */

use App\Domain\Tenant\Repository\TenantRepositoryInterface;
use App\Infrastructure\Persistence\Repository\TenantRepository;

return [
    TenantRepositoryInterface::class => TenantRepository::class,
];
