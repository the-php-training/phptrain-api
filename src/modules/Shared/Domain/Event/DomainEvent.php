<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use DateTimeImmutable;

/**
 * Domain Event Interface
 *
 * Marker interface for all domain events in the system.
 * Domain events represent something that happened in the domain that domain experts care about.
 */
interface DomainEvent
{
    /**
     * Get the time when this event occurred
     */
    public function occurredAt(): DateTimeImmutable;

    /**
     * Get the event name/type (useful for serialization and routing)
     */
    public function eventName(): string;

    /**
     * Convert the event to an array representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
