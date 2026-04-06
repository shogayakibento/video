<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class MgsService
{
    private string $affiliateId;

    public function __construct()
    {
        $this->affiliateId = config('mgs.affiliate_id', '');
    }

    private function makeClient(CookieJar $jar): Client
    {
        return new Client([
            'timeout'         => 15,
            'allow_redirects' => true,
            'cookies'         => $jar,
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
     * ※ URLは大文字品番を使用する必要がある（例: ABF-301）
     */
    public function fetchProductInfo(string $productCode): ?array
    {
        // MGSは大文字品番でアクセスする必要がある
        $upperCode = strtoupper(trim($productCode));
        $lowerCode = strtolower(trim($productCode));
        $url = "https://www.mgstage.com/product/product_detail/{$upperCode}/";

        // セッションを確立してからadc=1クッキーを付与する
        $jar = new CookieJar();
        $client = $this->makeClient($jar);

        try {
            // まずトップページでセッションを確立
            $client->get('https://www.mgstage.com/');

            // 年齢確認クッキーを設定
            $jar->setCookie(new SetCookie([
                'Name'   => 'adc',
                'Value'  => '1',
                'Domain' => '.mgstage.com',
                'Path'   => '/',
            ]));

            // 商品ページを取得
            $response = $client->get($url, [
                'headers' => ['Referer' => 'https://www.mgstage.com/'],
            ]);
            $html = (string) $response->getBody();
        } catch (\Exception $e) {
            return null;
        }

        // ページが正しく取得できたか確認（og:titleの有無）
        if (!str_contains($html, 'og:title')) {
            return null;
        }

        // --- タイトルを抽出 ---
        $title = '';
        if (preg_match('/property="og:title" content="([^"]+)"/', $html, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            // 「タイトル」：MGS動画... の形式から本文だけ抽出
            $title = preg_replace('/^「(.+)」：.+$/u', '$1', $title);
            // それ以外のサフィックスも除去
            $title = preg_replace('/\s*[：:｜|]\s*MGS.*$/u', '', $title);
            $title = trim($title);
        }

        // --- og:image からサムネURL・メーカーグループを取得 ---
        $thumbnailUrl = '';
        $makerGroup   = '';
        if (preg_match('/property="og:image" content="([^"]+)"/', $html, $m)) {
            $thumbnailUrl = $m[1];
            // https://image.mgstage.com/images/{makerGroup}/{makerCode}/{number}/...
            if (preg_match('#image\.mgstage\.com/images/([^/]+)/#', $thumbnailUrl, $mg)) {
                $makerGroup = $mg[1];
            }
        }

        // --- 女優名を抽出（<th>出演 → <td>内のリンクテキスト）---
        $actress = '';
        if (preg_match('/<th>出演：<\/th>\s*<td>(.*?)<\/td>/s', $html, $am)) {
            preg_match_all('/<a[^>]+>[\s\n]*([^\s<][^<]*)[\s\n]*<\/a>/u', $am[1], $names);
            $actress = implode(', ', array_unique(array_filter(array_map('trim', $names[1] ?? []))));
        }

        // --- メーカー名（<th>メーカー → <td>内のリンクテキスト）---
        $maker = '';
        if (preg_match('/<th>メーカー：<\/th>\s*<td>(.*?)<\/td>/s', $html, $mm)) {
            preg_match('/<a[^>]+>[\s\n]*([^\s<][^<]*)[\s\n]*<\/a>/u', $mm[1], $mk);
            $maker = trim($mk[1] ?? '');
        }

        // --- ジャンル（<th>ジャンル → <td>内のリンクテキスト）---
        $genre = '';
        if (preg_match('/<th>ジャンル：<\/th>\s*<td>(.*?)<\/td>/s', $html, $gm)) {
            preg_match_all('/<a[^>]+>[\s\n]*([^\s<][^<]*)[\s\n]*<\/a>/u', $gm[1], $genres);
            $genre = implode(', ', array_filter(array_map('trim', $genres[1] ?? [])));
        }

        // --- 発売日 ---
        $releaseDate = null;
        if (preg_match('/配信日：.*?(\d{4}\/\d{2}\/\d{2})/', $html, $m)) {
            $releaseDate = date('Y-m-d', strtotime($m[1]));
        }

        return [
            'product_code'  => $lowerCode,
            'title'         => $title ?: $lowerCode,
            'actress'       => $actress,
            'maker'         => $maker,
            'genre'         => $genre,
            'thumbnail_url' => $thumbnailUrl,
            'maker_group'   => $makerGroup,
            'release_date'  => $releaseDate,
            'affiliate_url' => $this->buildAffiliateUrl($lowerCode),
        ];
    }

    /**
     * 品番とメーカーグループからサムネURLを生成する（フォールバック用）
     */
    public function buildThumbnailUrl(string $productCode, string $makerGroup): string
    {
        $parsed = $this->parseProductCode($productCode);
        return sprintf(
            'https://image.mgstage.com/images/%s/%s/%s/pf_o1_%s.jpg',
            $makerGroup,
            $parsed['maker_code'],
            $parsed['number'],
            strtolower($productCode)
        );
    }

    /**
     * アフィリエイトURLを生成する
     */
    public function buildAffiliateUrl(string $productCode): string
    {
        $upper = strtoupper($productCode);
        $base  = "https://www.mgstage.com/product/product_detail/{$upper}/";
        if ($this->affiliateId) {
            return $base . "?aff={$this->affiliateId}";
        }
        return $base;
    }

    /**
     * 品番を登録して必要な情報を全取得する（admin用）
     * ※ サンプル動画URLはJSが必要なためサーバーサイドでは取得不可
     */
    public function register(string $productCode): array
    {
        $info = $this->fetchProductInfo($productCode);

        if (!$info) {
            return ['success' => false, 'error' => 'MGSページの取得に失敗しました。品番が正しいか確認してください。'];
        }

        return [
            'success'          => true,
            'product_code'     => $info['product_code'],
            'title'            => $info['title'],
            'actress'          => $info['actress'],
            'maker'            => $info['maker'],
            'genre'            => $info['genre'],
            'thumbnail_url'    => $info['thumbnail_url'],
            'sample_video_url' => null, // サンプル動画はJSが必要なため取得不可
            'affiliate_url'    => $info['affiliate_url'],
            'release_date'     => $info['release_date'],
            'maker_group'      => $info['maker_group'],
        ];
    }
}
