<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Repository;

use Tenant\Domain\Entity\Tenant;
use Tenant\Domain\Repository\TenantRepositoryInterface;
use Tenant\Domain\ValueObject\ContactEmail;
use Tenant\Domain\ValueObject\TenantId;
use Tenant\Domain\ValueObject\TenantSlug;
use Tenant\Domain\ValueObject\TenantStatus;
use Tenant\Infrastructure\Persistence\Model\TenantModel;
use DateTimeImmutable;

/**
 * Tenant Repository Implementation using Hyperf ORM
 *
 * Translates between Domain Entities and Persistence Models
 */
final class TenantRepository implements TenantRepositoryInterface
{
    public function save(Tenant $tenant): void
    {
        $model = TenantModel::query()->find($tenant->getId()->toString());

        if ($model === null) {
            $model = new TenantModel();
            $model->id = $tenant->getId()->toString();
        }

        $model->name = $tenant->getName();
        $model->slug = $tenant->getSlug()->toString();
        $model->contact_email = $tenant->getContactEmail()->toString();
        $model->contact_phone = $tenant->getContactPhone();
        $model->status = $tenant->getStatus()->value;

        $model->save();
    }

    public function findById(TenantId $id): ?Tenant
    {
        $model = TenantModel::query()->find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        $model = TenantModel::query()
            ->where('slug', $slug->toString())
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function slugExists(TenantSlug $slug): bool
    {
        return TenantModel::query()
            ->where('slug', $slug->toString())
            ->exists();
    }

    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $models = TenantModel::query()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->all();
    }

    public function delete(TenantId $id): void
    {
        TenantModel::query()
            ->where('id', $id->toString())
            ->delete();
    }

    public function count(): int
    {
        return TenantModel::query()->count();
    }

    /**
     * Convert Eloquent Model to Domain Entity
     */
    private function toDomain(TenantModel $model): Tenant
    {
        return Tenant::reconstitute(
            id: TenantId::fromString($model->id),
            name: $model->name,
            slug: TenantSlug::fromString($model->slug),
            contactEmail: ContactEmail::fromString($model->contact_email),
            contactPhone: $model->contact_phone,
            status: TenantStatus::fromString($model->status),
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }
}
