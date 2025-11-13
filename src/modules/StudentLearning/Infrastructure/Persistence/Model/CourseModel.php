<?php

declare(strict_types=1);

namespace StudentLearning\Infrastructure\Persistence\Model;

use Shared\Infrastructure\Persistence\Model;

/**
 * Course Eloquent Model (StudentLearning context)
 *
 * @property string $id
 * @property string $title
 * @property int $max_students
 * @property array $enrolled_students
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CourseModel extends Model
{
    protected ?string $table = 'courses';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'id',
        'title',
        'max_students',
        'enrolled_students',
    ];

    protected array $casts = [
        'max_students' => 'integer',
        'enrolled_students' => 'json', // Store as JSON
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
