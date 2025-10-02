<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Event;

use DateTimeImmutable;

/**
 * Domain Event: Tenant Created
 *
 * Emitted when a new tenant is created in the system
 */
final readonly class TenantCreated
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $slug,
        public DateTimeImmutable $occurredAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'slug' => $this->slug,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
