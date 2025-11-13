<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

/**
 * Migration to create CourseManagement bounded context tables
 *
 * Creates tables for:
 * - course_management_courses: Courses from administrative perspective
 * - course_management_enrollments: Enrollment records with status tracking
 */
class CreateCourseManagementTables extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        // Enrollments table (CourseManagement context)
        Schema::create('enrollments', function (Blueprint $table) {
            $table->char('id', 36)->primary()->comment('UUID of the enrollment');
            $table->char('course_id', 36)->comment('UUID of the course');
            $table->char('student_id', 36)->comment('UUID of the student');
            $table->string('student_name', 255)->comment('Student name at enrollment time');
            $table->string('student_email', 255)->comment('Student email at enrollment time');
            $table->enum('status', ['active', 'completed', 'dropped', 'suspended'])
                ->default('active')
                ->comment('Current enrollment status');
            $table->timestamp('enrolled_at')->comment('When the student enrolled');
            $table->timestamp('completed_at')->nullable()->comment('When the course was completed');
            $table->timestamp('dropped_at')->nullable()->comment('When enrollment was dropped');
            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('student_id');
            $table->index('status');
            $table->index('enrolled_at');
            $table->unique(['course_id', 'student_id'], 'unique_course_student');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
}
