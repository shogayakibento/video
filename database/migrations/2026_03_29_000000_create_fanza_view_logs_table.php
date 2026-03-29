<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fanza_view_logs', function (Blueprint $table) {
            $table->id();
            $table->string('content_id', 50);
            $table->string('session_id', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->index('content_id');
            $table->index('session_id');
            $table->unique(['content_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fanza_view_logs');
    }
};
