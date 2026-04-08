<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mgs_videos', function (Blueprint $table) {
            $table->dropIndex(['mgs_rank']);
            $table->dropColumn('mgs_rank');
        });
    }

    public function down(): void
    {
        Schema::table('mgs_videos', function (Blueprint $table) {
            $table->unsignedInteger('mgs_rank')->nullable()->after('review_count');
            $table->index('mgs_rank');
        });
    }
};
