<?php

declare(strict_types=1);

namespace StudentLearning\Infrastructure\Persistence\Model;

use Shared\Infrastructure\Persistence\Model;

/**
 * Student Eloquent Model (StudentLearning context)
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StudentModel extends Model
{
    protected ?string $table = 'students';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'id',
        'name',
        'email',
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
