<?php

declare(strict_types=1);

namespace Tenant\Domain\Event;

use DateTimeImmutable;
use Shared\Domain\Event\DomainEvent;

/**
 * Domain Event: Tenant Created
 *
 * Emitted when a new tenant is created in the system
 */
final readonly class TenantCreated implements DomainEvent
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $slug,
        public DateTimeImmutable $occurredAt,
    ) {
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function eventName(): string
    {
        return 'tenant.created';
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
