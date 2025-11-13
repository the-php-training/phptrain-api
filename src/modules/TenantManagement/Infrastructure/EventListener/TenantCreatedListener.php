<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\EventListener;

use Tenant\Domain\Event\TenantCreated;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * Listener for TenantCreated domain event
 *
 * Handles side effects when a new tenant is created
 */
#[Listener]
class TenantCreatedListener implements ListenerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function listen(): array
    {
        return [
            TenantCreated::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TenantCreated) {
            return;
        }

        // Log the event
        $this->logger->info('Tenant created', [
            'tenant_id' => $event->tenantId,
            'tenant_name' => $event->name,
            'tenant_slug' => $event->slug,
            'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // TODO: Future implementations could include:
        // - Send welcome email to tenant contact
        // - Initialize default tenant configuration
        // - Create initial admin user for the tenant
        // - Notify teachers (as per event storming: TeacherMustReceiveNotification)
        // - Create audit log entry
        // - Trigger async queue jobs for onboarding tasks
    }
}
