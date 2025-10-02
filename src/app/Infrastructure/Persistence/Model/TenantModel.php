<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Model;

use App\Model\Model;

/**
 * Tenant Eloquent Model
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $contact_email
 * @property string|null $contact_phone
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TenantModel extends Model
{
    protected ?string $table = 'tenants';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'id',
        'name',
        'slug',
        'contact_email',
        'contact_phone',
        'status',
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
