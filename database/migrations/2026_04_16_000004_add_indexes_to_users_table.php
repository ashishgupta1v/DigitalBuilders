<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes for faster authentication queries on PostgreSQL.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add index on email for faster login lookups
            $table->index('email');
        });
        
        // Add index on google_id for OAuth lookups if column exists
        if (Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('google_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
        
        if (Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['google_id']);
            });
        }
    }
};
