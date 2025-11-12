<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Shared\Domain\Event\IDomainEventBus;
use Shared\Infrastructure\Event\HyperfDomainEventBus;

return [
    // Domain Event Bus
    IDomainEventBus::class => HyperfDomainEventBus::class,
];
