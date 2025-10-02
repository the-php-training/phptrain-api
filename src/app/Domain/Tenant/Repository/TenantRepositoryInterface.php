<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repository;

use App\Domain\Tenant\Entity\Tenant;
use App\Domain\Tenant\ValueObject\TenantId;
use App\Domain\Tenant\ValueObject\TenantSlug;

/**
 * Repository interface for Tenant Aggregate
 *
 * Defines the contract for persisting and retrieving Tenant entities
 */
interface TenantRepositoryInterface
{
    /**
     * Save a tenant (create or update)
     */
    public function save(Tenant $tenant): void;

    /**
     * Find tenant by ID
     */
    public function findById(TenantId $id): ?Tenant;

    /**
     * Find tenant by slug
     */
    public function findBySlug(TenantSlug $slug): ?Tenant;

    /**
     * Check if a slug is already taken
     */
    public function slugExists(TenantSlug $slug): bool;

    /**
     * Find all tenants with pagination
     *
     * @return array<Tenant>
     */
    public function findAll(int $limit = 20, int $offset = 0): array;

    /**
     * Delete a tenant
     */
    public function delete(TenantId $id): void;

    /**
     * Get total count of tenants
     */
    public function count(): int;
}
