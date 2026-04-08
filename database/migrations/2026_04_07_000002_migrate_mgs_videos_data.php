<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('videos')
            ->where('store', 'mgs')
            ->orderBy('id')
            ->each(function ($video) {
                DB::table('mgs_videos')->insertOrIgnore([
                    'product_code'     => $video->dmm_content_id,
                    'title'            => $video->title,
                    'actress'          => $video->actress,
                    'thumbnail_url'    => $video->thumbnail_url,
                    'sample_video_url' => $video->sample_video_url,
                    'affiliate_url'    => $video->affiliate_url,
                    'genre'            => $video->genre,
                    'maker'            => $video->maker,
                    'release_date'     => $video->release_date,
                    'click_count'      => $video->click_count ?? 0,
                    'created_at'       => $video->created_at,
                    'updated_at'       => $video->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('mgs_videos')->truncate();
    }
};
