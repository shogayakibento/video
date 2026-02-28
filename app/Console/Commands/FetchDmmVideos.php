<?php

namespace App\Console\Commands;

use App\Services\DmmApiService;
use Illuminate\Console\Command;

class FetchDmmVideos extends Command
{
    protected $signature = 'videos:fetch {keyword?} {--hits=20 : 取得件数}';

    protected $description = 'DMM APIから動画情報を取得してDBに保存';

    public function handle(DmmApiService $dmmApi): int
    {
        if (!$dmmApi->isConfigured()) {
            $this->error('DMM API認証情報が設定されていません。.envファイルにDMM_API_IDとDMM_AFFILIATE_IDを設定してください。');
            return self::FAILURE;
        }

        $keyword = $this->argument('keyword') ?? '';
        $hits = (int) $this->option('hits');

        $this->info("DMM APIから動画情報を取得中...");

        $imported = $dmmApi->importVideos($keyword, $hits);

        $this->info("{$imported}件の動画を取得・更新しました。");

        return self::SUCCESS;
    }
}
