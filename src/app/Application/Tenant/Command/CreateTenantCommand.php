<?php

declare(strict_types=1);

namespace App\Application\Tenant\Command;

/**
 * Command to create a new Tenant
 *
 * Represents the intent to create a tenant in the system
 */
final readonly class CreateTenantCommand
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $contactEmail,
        public ?string $contactPhone = null,
    ) {
    }
}
