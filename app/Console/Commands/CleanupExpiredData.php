<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupExpiredData extends Command
{
    protected $signature = 'cleanup:expired';

    protected $description = '期限切れのキャッシュとセッションをDBから削除する';

    public function handle(): int
    {
        $cacheDeleted = DB::table('cache')->where('expiration', '<', time())->delete();
        $this->info("期限切れキャッシュ: {$cacheDeleted}件削除");

        $sessionLifetime = config('session.lifetime', 120);
        $sessionDeleted = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes($sessionLifetime)->timestamp)
            ->delete();
        $this->info("期限切れセッション: {$sessionDeleted}件削除");

        return self::SUCCESS;
    }
}
