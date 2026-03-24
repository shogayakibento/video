<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Models\Video;
use App\Services\DmmApiService;
use Illuminate\Console\Command;

class ImportTweetsFromJson extends Command
{
    protected $signature = 'tweet:import-json {file : JSONファイルのパス}';

    protected $description = 'JSONファイルからツイートをインポートしてDBに保存';

    public function handle(DmmApiService $dmmApi): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("ファイルが見つかりません: {$file}");
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);

        if (!is_array($data)) {
            $this->error('JSONのパースに失敗しました');
            return self::FAILURE;
        }

        if (isset($data['error'])) {
            $this->error($data['error']);
            return self::FAILURE;
        }

        $this->info(count($data) . '件のツイートが見つかりました');

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
                    'tweet_url'       => $item['tweet_url'] ?? null,
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
