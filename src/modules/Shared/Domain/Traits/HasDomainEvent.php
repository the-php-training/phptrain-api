<?php

declare(strict_types=1);

namespace Shared\Domain\Traits;

/**
 * Domain Event Trait
 *
 * Provides event recording and releasing functionality for aggregate roots.
 * This trait can be used by any entity that needs to record domain events.
 */
trait HasDomainEvent
{
    private array $domainEvents = [];

    /**
     * Record a domain event
     */
    protected function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Release all recorded domain events and clear the collection
     *
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    /**
     * Get all recorded domain events without clearing them
     *
     * @return array<object>
     */
    public function getEvents(): array
    {
        return $this->domainEvents;
    }

    /**
     * Clear all recorded domain events without returning them
     */
    public function clearEvents(): void
    {
        $this->domainEvents = [];
    }

    /**
     * Check if there are any recorded events
     */
    public function hasEvents(): bool
    {
        return !empty($this->domainEvents);
    }
}
