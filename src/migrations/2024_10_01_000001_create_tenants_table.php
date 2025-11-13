<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

/**
 * Migration to create tenants table
 *
 * Stores tenant (organization/institution) data for the multi-tenant platform
 */
class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->char('id', 36)->primary()->comment('UUID of the tenant');
            $table->string('name', 255)->comment('Organization name');
            $table->string('slug', 50)->unique()->comment('URL-friendly identifier');
            $table->string('contact_email', 255)->comment('Primary contact email');
            $table->string('contact_phone', 20)->nullable()->comment('Primary contact phone');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])
                ->default('pending')
                ->comment('Operational status of the tenant');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
