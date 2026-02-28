<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->string('tweet_id')->unique();
            $table->string('tweet_url');
            $table->text('tweet_text')->nullable();
            $table->string('author_username')->nullable();
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('retweet_count')->default(0);
            $table->timestamp('tweeted_at')->nullable();
            $table->timestamps();

            $table->index('like_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweets');
    }
};
