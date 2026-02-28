<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;

class RecalculateRanking extends Command
{
    protected $signature = 'ranking:recalculate';

    protected $description = '全動画のいいね数・RT数を再集計';

    public function handle(): int
    {
        $videos = Video::has('tweets')->get();

        $bar = $this->output->createProgressBar($videos->count());
        $bar->start();

        foreach ($videos as $video) {
            $video->recalculateEngagement();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$videos->count()}件の動画のエンゲージメントを再集計しました。");

        return self::SUCCESS;
    }
}
