<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update devices table for multi-rack architecture:
     * - Rename device_token → device_id (consistent naming)
     * - Make user_id nullable (device can exist without owner)
     * - Add label_rak for user-customizable rack labeling
     * - Add index on device_id for fast polling
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Make user_id nullable (drop old FK, re-add as nullable)
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Rename device_token → device_id
            $table->renameColumn('device_token', 'device_id');
        });

        Schema::table('devices', function (Blueprint $table) {
            // Add label_rak column
            $table->string('label_rak')->nullable()->after('device_name');

            // Index for fast lookups
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropColumn('label_rak');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->renameColumn('device_id', 'device_token');

            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->change();
        });
    }
};
