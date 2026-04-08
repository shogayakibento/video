<?php

namespace App\Console\Commands;

use App\Models\MgsVideo;
use Illuminate\Console\Command;

class MgsCrawl extends Command
{
    protected $signature = 'mgs:crawl
        {--mode=exclusive : exclusive（専属女優全員）/ actress（特定女優）}
        {--name= : 女優名（--mode=actress のとき使用）}
        {--limit=0 : 取得件数上限（0=無制限）}
        {--headless=true : ヘッドレスモード（false にするとブラウザが見える）}
        {--dry-run : DBに保存せず結果を表示するだけ}';

    protected $description = 'MGS動画をPuppeteerでクロールしてDBに保存する';

    public function handle(): int
    {
        $mode     = $this->option('mode');
        $name     = $this->option('name');
        $limit    = $this->option('limit');
        $headless = $this->option('headless');
        $dryRun   = $this->option('dry-run');
        $affId    = config('mgs.affiliate_id', '');

        $script = base_path('scripts/mgs-crawl.js');

        if (!file_exists($script)) {
            $this->error('scripts/mgs-crawl.js が見つかりません。');
            return 1;
        }

        // コマンド組み立て
        $args = [
            'node', escapeshellarg($script),
            "--mode={$mode}",
            "--limit={$limit}",
            "--headless={$headless}",
        ];
        if ($name)  $args[] = '--name=' . escapeshellarg($name);
        if ($affId) $args[] = '--aff=' . escapeshellarg($affId);

        $this->info("MGSクローラー起動 [mode={$mode}" . ($name ? " name={$name}" : '') . "]");
        $this->line('進捗はリアルタイムで表示されます...');
        $this->newLine();

        // stderrをリアルタイム表示、stdoutにJSONが出る
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => STDERR,
        ];

        $process = proc_open(implode(' ', $args), $descriptors, $pipes, base_path());

        if (!is_resource($process)) {
            $this->error('Puppeteerスクリプトの起動に失敗しました。');
            return 1;
        }

        fclose($pipes[0]);
        $json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);

        $videos = json_decode($json, true);
        if (!is_array($videos)) {
            $this->error('JSONの解析に失敗しました。');
            $this->error(substr($json, 0, 500));
            return 1;
        }

        $this->newLine();
        $this->info('取得件数: ' . count($videos) . '件');

        if ($dryRun) {
            foreach ($videos as $v) {
                $sample = $v['sample_video_url'] ? '動画あり' : '動画なし';
                $this->line("  [{$v['product_code']}] {$v['title']} / {$v['actress']} ({$sample})");
            }
            return 0;
        }

        $saved = $updated = 0;

        foreach ($videos as $i => $v) {
            if (empty($v['product_code']) || empty($v['title'])) continue;

            $data = [
                'title'         => $v['title'],
                'actress'       => $v['actress']       ?? '',
                'thumbnail_url' => $v['thumbnail_url'] ?? '',
                'affiliate_url' => $v['affiliate_url'] ?? '',
                'genre'         => $v['genre']         ?? '',
                'maker'         => $v['maker']         ?? '',
                'release_date'  => $v['release_date']  ?: null,
                'review_score'  => $v['review_score']  ?? null,
                'review_count'  => $v['review_count']  ?? 0,
                'mgs_rank'      => $i + 1,
            ];

            if (!empty($v['sample_video_url'])) {
                $data['sample_video_url'] = $v['sample_video_url'];
            }

            $existing = MgsVideo::where('product_code', $v['product_code'])->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                MgsVideo::create(array_merge($data, ['product_code' => $v['product_code']]));
                $saved++;
            }

            $this->line("  ✓ [{$v['product_code']}] {$v['title']}");
        }

        $this->newLine();
        $this->info("完了: 新規 {$saved}件 / 更新 {$updated}件");

        return 0;
    }
}
