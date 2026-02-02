<?php

use Illuminate\Database\Migrations\Migration;

/**
 * This migration is no longer needed as user_id was added to the create_employees_table migration.
 * Keeping for migration history consistency.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {}

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
