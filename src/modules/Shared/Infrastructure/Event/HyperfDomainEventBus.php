<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Event;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shared\Domain\Event\DomainEvent;
use Shared\Domain\Event\IDomainEventBus;
use InvalidArgumentException;

/**
 * Hyperf Domain Event Bus Implementation
 *
 * Implements the domain event bus using Hyperf's PSR-14 event dispatcher.
 * This bridges domain events to the framework's event system.
 */
class HyperfDomainEventBus implements IDomainEventBus
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Publishes a single event (callable or object).
     */
    public function publish(DomainEvent|Closure $event, array $paramsMap = []): void
    {
        if ($event instanceof Closure) {
            $event = $event($paramsMap);
        }

        if (!$event instanceof DomainEvent) {
            throw new InvalidArgumentException('Event must implement DomainEvent interface');
        }

        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Publishes a list of events (can be callables or objects).
     */
    public function publishAll(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }

    /**
     * Publishes domain events registered in a single entity.
     */
    public function publishEntity(object $entity, array $paramsMap = []): void
    {
        if (!method_exists($entity, 'releaseEvents')) {
            throw new InvalidArgumentException(
                sprintf(
                    'Entity %s must have a releaseEvents() method',
                    get_class($entity)
                )
            );
        }

        $events = $entity->releaseEvents();

        if (!is_array($events)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Entity %s releaseEvents() must return an array',
                    get_class($entity)
                )
            );
        }

        foreach ($events as $event) {
            $this->publish($event, $paramsMap);
        }
    }

    /**
     * Publishes all domain events from a list of entities.
     */
    public function publishEntities(array $entities, array $paramsMap = []): void
    {
        foreach ($entities as $entity) {
            $this->publishEntity($entity, $paramsMap);
        }
    }
}
