<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'proposal_content')) {
                $table->longText('proposal_content')->nullable()->after('scope_summary');
            }
            if (!Schema::hasColumn('deals', 'proposal_token')) {
                $table->string('proposal_token', 64)->nullable()->unique()->after('proposal_content');
            }
            if (!Schema::hasColumn('deals', 'proposal_sent_at')) {
                $table->dateTime('proposal_sent_at')->nullable()->after('proposal_token');
            }
            if (!Schema::hasColumn('deals', 'proposal_viewed_at')) {
                $table->dateTime('proposal_viewed_at')->nullable()->after('proposal_sent_at');
            }
            if (!Schema::hasColumn('deals', 'proposal_accepted_at')) {
                $table->dateTime('proposal_accepted_at')->nullable()->after('proposal_viewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_content',
                'proposal_token',
                'proposal_sent_at',
                'proposal_viewed_at',
                'proposal_accepted_at',
            ]);
        });
    }
};
