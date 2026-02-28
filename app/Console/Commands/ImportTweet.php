<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Models\Video;
use Illuminate\Console\Command;

class ImportTweet extends Command
{
    protected $signature = 'tweet:import
        {content_id : FANZA品番(例: abc00123)}
        {--likes=0 : いいね数}
        {--retweets=0 : リツイート数}
        {--author= : ツイート投稿者}
        {--text= : ツイート本文}';

    protected $description = 'ツイート情報を手動で登録してランキングに反映';

    public function handle(): int
    {
        $video = Video::where('dmm_content_id', $this->argument('content_id'))->first();

        if (!$video) {
            $this->error("品番 {$this->argument('content_id')} の動画が見つかりません。先にvideos:fetchで動画を取得してください。");
            return self::FAILURE;
        }

        $tweetId = 'manual_' . $video->id . '_' . time();

        $tweet = Tweet::updateOrCreate(
            ['tweet_id' => $tweetId],
            [
                'video_id' => $video->id,
                'tweet_text' => $this->option('text'),
                'author_username' => $this->option('author'),
                'like_count' => (int) $this->option('likes'),
                'retweet_count' => (int) $this->option('retweets'),
                'tweeted_at' => now(),
            ]
        );

        $video->recalculateEngagement();

        $this->info("ツイートを登録しました。動画「{$video->title}」の合計いいね数: {$video->fresh()->total_likes}");

        return self::SUCCESS;
    }
}
