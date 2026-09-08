<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'company')) {
                $table->string('company', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('leads', 'role_title')) {
                $table->string('role_title', 100)->nullable()->after('company');
            }
            if (!Schema::hasColumn('leads', 'segment')) {
                $table->string('segment', 50)->default('general')->index()->after('project_type');
            }
            if (!Schema::hasColumn('leads', 'touchpoint_count')) {
                $table->unsignedTinyInteger('touchpoint_count')->default(0)->after('notes_count');
            }
            if (!Schema::hasColumn('leads', 'last_contact_date')) {
                $table->dateTime('last_contact_date')->nullable()->after('touchpoint_count');
            }
            if (!Schema::hasColumn('leads', 'next_action_date')) {
                $table->dateTime('next_action_date')->nullable()->index()->after('last_contact_date');
            }
            if (!Schema::hasColumn('leads', 'next_action_note')) {
                $table->string('next_action_note', 255)->nullable()->after('next_action_date');
            }
            if (!Schema::hasColumn('leads', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('next_action_note');
            }
            if (!Schema::hasColumn('leads', 'objection_flag')) {
                $table->string('objection_flag', 100)->nullable()->after('ai_summary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columns = [
                'organization_id',
                'company',
                'role_title',
                'segment',
                'touchpoint_count',
                'last_contact_date',
                'next_action_date',
                'next_action_note',
                'ai_summary',
                'objection_flag',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    if ($column === 'organization_id') {
                        $table->dropForeign(['organization_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
