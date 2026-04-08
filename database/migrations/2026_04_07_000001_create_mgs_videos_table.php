<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mgs_videos', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('title');
            $table->string('actress')->nullable();
            $table->string('thumbnail_url');
            $table->string('sample_video_url')->nullable();
            $table->string('affiliate_url');
            $table->string('genre')->nullable();
            $table->string('maker')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('review_score', 3, 1)->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('mgs_rank')->nullable();
            $table->timestamps();

            $table->index('release_date');
            $table->index('mgs_rank');
            $table->index('review_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mgs_videos');
    }
};
