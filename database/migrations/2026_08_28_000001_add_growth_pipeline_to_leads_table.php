<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'source')) {
                $table->string('source', 50)->default('contact')->after('project_type');
            }
            if (!Schema::hasColumn('leads', 'region')) {
                $table->string('region', 20)->nullable()->after('source');
            }
            if (!Schema::hasColumn('leads', 'stage')) {
                $table->string('stage', 50)->default('new')->after('status');
            }
            if (!Schema::hasColumn('leads', 'estimated_value')) {
                $table->string('estimated_value', 100)->nullable()->after('score');
            }
            if (!Schema::hasColumn('leads', 'utm_source')) {
                $table->string('utm_source', 100)->nullable()->after('estimated_value');
                $table->string('utm_medium', 100)->nullable()->after('utm_source');
                $table->string('utm_campaign', 100)->nullable()->after('utm_medium');
                $table->string('utm_content', 100)->nullable()->after('utm_campaign');
                $table->string('utm_term', 100)->nullable()->after('utm_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columns = [
                'source',
                'region',
                'stage',
                'estimated_value',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
