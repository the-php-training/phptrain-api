<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

/**
 * Migration to create StudentLearning bounded context tables
 *
 * Creates tables for:
 * - student_learning_students: Students who can enroll in courses
 * - student_learning_courses: Courses from a learning access perspective
 */
class CreateStudentLearningTables extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        // Students table (StudentLearning context)
        Schema::create('students', function (Blueprint $table) {
            $table->char('id', 36)->primary()->comment('UUID of the student');
            $table->string('name', 255)->comment('Student full name');
            $table->string('email', 255)->unique()->comment('Student email address');
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('created_at');
        });

        // Courses table (StudentLearning context - learning access control)
        Schema::create('courses', function (Blueprint $table) {
            $table->char('id', 36)->primary()->comment('UUID of the course');
            $table->string('title', 255)->comment('Course title');
            $table->text('description')->nullable()->comment('Course description');
            $table->integer('max_students')->default(0)->comment('Maximum number of students allowed');
            $table->json('enrolled_students')->nullable()->comment('Array of student IDs with learning access');
            $table->timestamps();

            // Indexes
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');
    }
}
