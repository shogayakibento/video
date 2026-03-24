<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cleanup:expired')->daily();

// Twitterスクレイピング: 毎日深夜2時に実行（いいね数は毎回更新される）
Schedule::command('tweet:scrape --min-likes=1000')->dailyAt('02:00');
