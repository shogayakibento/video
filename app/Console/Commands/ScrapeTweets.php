<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Models\Video;
use App\Services\DmmApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ScrapeTweets extends Command
{
    protected $signature = 'tweet:scrape
        {--min-likes=1000 : いいね数の閾値}
        {--dry-run : DBに保存せず結果だけ表示}';

    protected $description = 'TwitterアカウントリストからFANZAツイートをスクレイピングしてDB保存';

    public function handle(DmmApiService $dmmApi): int
    {
        $scriptPath = base_path('scripts/scrape_tweets.py');

        if (!file_exists($scriptPath)) {
            $this->error("スクリプトが見つかりません: {$scriptPath}");
            return self::FAILURE;
        }

        $accountsFile = storage_path('app/private/twitter_accounts.txt');
        if (!file_exists($accountsFile)) {
            $this->error("アカウントリストが見つかりません: {$accountsFile}");
            return self::FAILURE;
        }

        $this->info('Twitterスクレイピング開始...');

        $result = Process::env([
            'TWITTER_USERNAME' => config('services.twitter.username', ''),
            'TWITTER_EMAIL'    => config('services.twitter.email', ''),
            'TWITTER_PASSWORD' => config('services.twitter.password', ''),
            'ACCOUNTS_FILE'    => $accountsFile,
            'TWSCRAPE_DB'      => storage_path('app/private/twscrape.db'),
            'MIN_LIKES'        => $this->option('min-likes'),
        ])->timeout(300)->run("python3 {$scriptPath}");

        // stderrの進捗ログを表示
        if ($result->errorOutput()) {
            foreach (explode("\n", trim($result->errorOutput())) as $line) {
                if ($line) {
                    $this->line("  <fg=gray>{$line}</>");
                }
            }
        }

        if (!$result->successful()) {
            $this->error('スクレイピング失敗');
            return self::FAILURE;
        }

        $data = json_decode($result->output(), true);

        if (!is_array($data)) {
            $this->error('JSONのパースに失敗しました');
            $this->error($result->output());
            return self::FAILURE;
        }

        if (isset($data['error'])) {
            $this->error($data['error']);
            return self::FAILURE;
        }

        $this->info(count($data) . '件のツイートが見つかりました');

        if ($this->option('dry-run')) {
            foreach ($data as $item) {
                $this->line("  {$item['author_username']}: {$item['dmm_content_id']} ({$item['like_count']}いいね)");
            }
            return self::SUCCESS;
        }

        $saved   = 0;
        $skipped = 0;

        foreach ($data as $item) {
            $video = Video::where('dmm_content_id', $item['dmm_content_id'])->first();

            if (!$video && $dmmApi->isConfigured()) {
                $this->line("  品番 {$item['dmm_content_id']} をDMM APIから取得中...");
                $dmmApi->importVideos($item['dmm_content_id'], 1);
                $video = Video::where('dmm_content_id', $item['dmm_content_id'])->first();
            }

            if (!$video) {
                $this->warn("  品番 {$item['dmm_content_id']} が見つかりません。スキップ");
                $skipped++;
                continue;
            }

            Tweet::updateOrCreate(
                ['tweet_id' => $item['tweet_id']],
                [
                    'video_id'        => $video->id,
                    'tweet_url'       => $item['tweet_url'],
                    'tweet_text'      => $item['tweet_text'],
                    'author_username' => $item['author_username'],
                    'like_count'      => $item['like_count'],
                    'retweet_count'   => $item['retweet_count'],
                    'tweeted_at'      => $item['tweeted_at'],
                ]
            );

            $video->recalculateEngagement();
            $saved++;
        }

        $this->info("完了 - 保存: {$saved}件、スキップ: {$skipped}件");

        return self::SUCCESS;
    }
}
