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
use Tenant\Domain\Repository\ITenantRepository;
use Tenant\Infrastructure\Persistence\Repository\TenantRepository;
use CourseManagement\Domain\Repository\ICourseRepository as CourseManagementICourseRepository;
use CourseManagement\Infrastructure\Persistence\Repository\CourseRepository as CourseManagementCourseRepository;
use StudentLearning\Domain\Repository\IStudentRepository;
use StudentLearning\Domain\Repository\ICourseRepository as StudentLearningICourseRepository;
use StudentLearning\Infrastructure\Persistence\Repository\StudentRepository;
use StudentLearning\Infrastructure\Persistence\Repository\CourseRepository as StudentLearningCourseRepository;

return [
    // Shared
    IDomainEventBus::class => HyperfDomainEventBus::class,

    // TenantManagement
    ITenantRepository::class => TenantRepository::class,

    // CourseManagement
    CourseManagementICourseRepository::class => CourseManagementCourseRepository::class,

    // StudentLearning
    IStudentRepository::class => StudentRepository::class,
    StudentLearningICourseRepository::class => StudentLearningCourseRepository::class,
];
