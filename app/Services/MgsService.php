<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class MgsService
{
    private Client $client;
    private string $affiliateId;

    public function __construct()
    {
        $this->affiliateId = config('mgs.affiliate_id', '');
        $this->client = new Client([
            'timeout'         => 15,
            'allow_redirects' => true,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language' => 'ja,en-US;q=0.9,en;q=0.8',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);
    }

    /**
     * 品番を解析してメーカーコードと番号を返す
     * 例: abf-301 → ['maker_code' => 'abf', 'number' => '301']
     */
    public function parseProductCode(string $productCode): array
    {
        preg_match('/^([a-zA-Z]+)-?(\d+)$/i', trim($productCode), $m);
        return [
            'maker_code' => strtolower($m[1] ?? ''),
            'number'     => $m[2] ?? '',
        ];
    }

    /**
     * MGS商品ページから情報を取得する
     */
    public function fetchProductInfo(string $productCode): ?array
    {
        $productCode = strtolower(trim($productCode));
        $url = "https://www.mgstage.com/product/product_detail/{$productCode}/";

        // 年齢確認Cookie (adc=1)
        $jar = CookieJar::fromArray(['adc' => '1'], 'www.mgstage.com');

        try {
            $response = $this->client->get($url, ['cookies' => $jar]);
            $html = (string) $response->getBody();
        } catch (\Exception $e) {
            return null;
        }

        // --- pid（サンプル動画ID）を抽出 ---
        $pid = null;
        $pidPatterns = [
            '/data-sample_id=["\']([0-9a-f\-]{36})["\']/',
            '/data-pid=["\']([0-9a-f\-]{36})["\']/',
            '/sampleplayer\.php\?pid=([0-9a-f\-]{36})/',
            '/["\']pid["\']\s*:\s*["\']([0-9a-f\-]{36})["\']/',
            '/var\s+pid\s*=\s*["\']([0-9a-f\-]{36})["\']/',
        ];
        foreach ($pidPatterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $pid = $m[1];
                break;
            }
        }

        // --- タイトルを抽出 ---
        $title = '';
        if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            // "| MGS動画" などのサフィックスを除去
            $title = preg_replace('/\s*[|｜]\s*MGS.*$/u', '', $title);
        }
        if (!$title && preg_match('/<h1[^>]*class="[^"]*tag_title[^"]*"[^>]*>(.*?)<\/h1>/s', $html, $m)) {
            $title = html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8');
        }

        // --- 女優名を抽出 ---
        $actress = '';
        if (preg_match_all('/<span class="tag_star">(.*?)<\/span>/s', $html, $m)) {
            $names = array_map(fn($s) => html_entity_decode(strip_tags($s), ENT_QUOTES, 'UTF-8'), $m[1]);
            $actress = implode(', ', array_filter($names));
        }
        // タグ形式 <a>女優名</a> のパターンも試す
        if (!$actress && preg_match('/<dt>出演：<\/dt>.*?<dd>(.*?)<\/dd>/s', $html, $m)) {
            preg_match_all('/>([^<]+)<\/a>/', $m[1], $am);
            $actress = implode(', ', array_filter(array_map('trim', $am[1] ?? [])));
        }

        // --- メーカー名 ---
        $maker = '';
        if (preg_match('/<dt>メーカー：<\/dt>.*?<dd[^>]*>(.*?)<\/dd>/s', $html, $m)) {
            $maker = html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8');
            $maker = trim($maker);
        }

        // --- ジャンル ---
        $genre = '';
        if (preg_match('/<dt>ジャンル：<\/dt>.*?<dd[^>]*>(.*?)<\/dd>/s', $html, $m)) {
            preg_match_all('/>([^<\s][^<]*)<\/a>/', $m[1], $gm);
            $genre = implode(', ', array_filter(array_map('trim', $gm[1] ?? [])));
        }

        // --- og:image からサムネURL・メーカーグループを取得 ---
        $thumbnailUrl = '';
        $makerGroup   = '';
        if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $m)) {
            $thumbnailUrl = $m[1];
            // https://image.mgstage.com/images/{makerGroup}/{makerCode}/{number}/...
            if (preg_match('#image\.mgstage\.com/images/([^/]+)/#', $thumbnailUrl, $mg)) {
                $makerGroup = $mg[1];
            }
        }

        // サムネが取れなかった場合は品番から生成を試みる
        if (!$thumbnailUrl && $makerGroup) {
            $thumbnailUrl = $this->buildThumbnailUrl($productCode, $makerGroup);
        }

        // --- 発売日 ---
        $releaseDate = null;
        if (preg_match('/配信日：.*?(\d{4}\/\d{2}\/\d{2})/', $html, $m)) {
            $releaseDate = date('Y-m-d', strtotime($m[1]));
        }

        return [
            'product_code' => $productCode,
            'pid'          => $pid,
            'title'        => $title ?: $productCode,
            'actress'      => $actress,
            'maker'        => $maker,
            'genre'        => $genre,
            'thumbnail_url' => $thumbnailUrl,
            'maker_group'  => $makerGroup,
            'release_date' => $releaseDate,
            'affiliate_url' => $this->buildAffiliateUrl($productCode),
        ];
    }

    /**
     * pidからサンプル動画のMP4 URLを取得する
     */
    public function fetchSampleVideoUrl(string $pid): ?string
    {
        $jar = CookieJar::fromArray(['adc' => '1'], 'www.mgstage.com');

        try {
            $response = $this->client->get(
                "https://www.mgstage.com/sampleRespons.php?pid={$pid}",
                ['cookies' => $jar]
            );
            $data = json_decode((string) $response->getBody(), true);
            $ismUrl = $data['url'] ?? null;

            if (!$ismUrl) {
                return null;
            }

            // .ism/request?... → .mp4 に変換
            // 例: /abf-301_20251202T130002.ism/request?uid=...&pid=...
            //   → /abf-301_20251202T130002.mp4
            $mp4Url = preg_replace('/\.ism\/request.*$/', '.mp4', $ismUrl);

            return $mp4Url ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 品番とメーカーグループからサムネURLを生成する
     */
    public function buildThumbnailUrl(string $productCode, string $makerGroup): string
    {
        $parsed = $this->parseProductCode($productCode);
        return sprintf(
            'https://image.mgstage.com/images/%s/%s/%s/pf_o1_%s.jpg',
            $makerGroup,
            $parsed['maker_code'],
            $parsed['number'],
            $productCode
        );
    }

    /**
     * アフィリエイトURLを生成する
     */
    public function buildAffiliateUrl(string $productCode): string
    {
        $base = "https://www.mgstage.com/product/product_detail/{$productCode}/";
        if ($this->affiliateId) {
            return $base . "?aff={$this->affiliateId}";
        }
        return $base;
    }

    /**
     * 品番を登録して必要な情報を全取得する（admin用）
     */
    public function register(string $productCode): array
    {
        $info = $this->fetchProductInfo($productCode);

        if (!$info) {
            return ['success' => false, 'error' => 'MGSページの取得に失敗しました。'];
        }

        $sampleVideoUrl = null;
        if ($info['pid']) {
            $sampleVideoUrl = $this->fetchSampleVideoUrl($info['pid']);
        }

        return [
            'success'          => true,
            'product_code'     => $info['product_code'],
            'title'            => $info['title'],
            'actress'          => $info['actress'],
            'maker'            => $info['maker'],
            'genre'            => $info['genre'],
            'thumbnail_url'    => $info['thumbnail_url'],
            'sample_video_url' => $sampleVideoUrl,
            'affiliate_url'    => $info['affiliate_url'],
            'release_date'     => $info['release_date'],
            'maker_group'      => $info['maker_group'],
        ];
    }
}
