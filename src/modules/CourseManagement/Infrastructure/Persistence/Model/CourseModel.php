<?php

declare(strict_types=1);

namespace CourseManagement\Infrastructure\Persistence\Model;

use Shared\Infrastructure\Persistence\Model;

/**
 * Course Eloquent Model (CourseManagement context)
 *
 * @property string $id
 * @property string $title
 * @property string $description
 * @property int $max_capacity
 * @property string|null $instructor_id
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
        'description',
        'max_capacity',
        'instructor_id',
    ];

    protected array $casts = [
        'max_capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get enrollments for this course
     */
    public function enrollments()
    {
        return $this->hasMany(EnrollmentModel::class, 'course_id', 'id');
    }
}
