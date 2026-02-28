<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('dmm_content_id')->unique();
            $table->string('title');
            $table->string('actress')->nullable();
            $table->string('thumbnail_url');
            $table->string('sample_video_url')->nullable();
            $table->string('affiliate_url');
            $table->string('genre')->nullable();
            $table->string('maker')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedInteger('total_likes')->default(0);
            $table->unsignedInteger('total_retweets')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();

            $table->index('total_likes');
            $table->index('release_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
