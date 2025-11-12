<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

use Closure;

/**
 * Domain Event Bus Interface
 *
 * Contract for publishing domain events to the event bus.
 * Supports publishing single events, multiple events, and events from entities.
 */
interface IDomainEventBus
{
    /**
     * Publishes a single event (callable or object).
     *
     * @param DomainEvent|Closure $event The event to publish
     * @param array<string, mixed> $paramsMap Additional parameters to pass to event handlers
     */
    public function publish(DomainEvent|Closure $event, array $paramsMap = []): void;

    /**
     * Publishes a list of events (can be callables or objects).
     *
     * @param array<DomainEvent|Closure> $events List of events to publish
     */
    public function publishAll(array $events): void;

    /**
     * Publishes domain events registered in a single entity.
     * The entity must have a releaseEvents() method that returns an array of events.
     *
     * @param object $entity The entity containing domain events
     * @param array<string, mixed> $paramsMap Additional parameters to pass to event handlers
     */
    public function publishEntity(object $entity, array $paramsMap = []): void;

    /**
     * Publishes all domain events from a list of entities.
     * Each entity must have a releaseEvents() method that returns an array of events.
     *
     * @param array<object> $entities List of entities containing domain events
     * @param array<string, mixed> $paramsMap Additional parameters to pass to event handlers
     */
    public function publishEntities(array $entities, array $paramsMap = []): void;
}
