<?php

declare(strict_types=1);

namespace CourseManagement\Infrastructure\Persistence\Model;

use Shared\Infrastructure\Persistence\Model;

/**
 * Enrollment Eloquent Model (CourseManagement context)
 *
 * @property string $id
 * @property string $course_id
 * @property string $student_id
 * @property string $student_name
 * @property string $student_email
 * @property string $status
 * @property \Carbon\Carbon $enrolled_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $dropped_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EnrollmentModel extends Model
{
    protected ?string $table = 'enrollments';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'id',
        'course_id',
        'student_id',
        'student_name',
        'student_email',
        'status',
        'enrolled_at',
        'completed_at',
        'dropped_at',
    ];

    protected array $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'dropped_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the course this enrollment belongs to
     */
    public function course()
    {
        return $this->belongsTo(CourseModel::class, 'course_id', 'id');
    }
}
