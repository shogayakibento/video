<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FanzaApiService
{
    private string $apiId;
    private string $affiliateId;
    private string $baseUrl;
    private string $site;
    private int $cacheTtl;

    public function __construct()
    {
        $this->apiId = config('fanza.api_id');
        $this->affiliateId = config('fanza.affiliate_id');
        $this->baseUrl = config('fanza.base_url');
        $this->site = config('fanza.site');
        $this->cacheTtl = config('fanza.cache_ttl', 3600);
    }

    public function getItems(array $params = []): array
    {
        $defaults = [
            'api_id' => $this->apiId,
            'affiliate_id' => $this->affiliateId,
            'site' => $this->site,
            'service' => 'digital',
            'floor' => 'videoa',
            'hits' => config('fanza.default_hits', 20),
            'sort' => 'rank',
            'output' => 'json',
        ];

        $query = array_merge($defaults, $params);
        $cacheKey = 'fanza_items_' . md5(json_encode($query));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query) {
            return $this->request('ItemList', $query);
        });
    }

    public function getRanking(string $service = 'digital', string $floor = 'videoa', int $hits = 10): array
    {
        return $this->getItems([
            'service' => $service,
            'floor' => $floor,
            'hits' => $hits,
            'sort' => 'rank',
        ]);
    }

    public function getNewReleases(string $service = 'digital', string $floor = 'videoa', int $hits = 20): array
    {
        return $this->getItems([
            'service' => $service,
            'floor' => $floor,
            'hits' => $hits,
            'sort' => 'date',
        ]);
    }

    public function search(string $keyword, string $service = 'digital', string $floor = 'videoa', int $hits = 20, int $offset = 1): array
    {
        return $this->getItems([
            'service' => $service,
            'floor' => $floor,
            'keyword' => $keyword,
            'hits' => $hits,
            'offset' => $offset,
        ]);
    }

    public function getGenres(string $service = 'digital', string $floor = 'videoa'): array
    {
        $cacheKey = "fanza_genres_{$service}_{$floor}";

        return Cache::remember($cacheKey, $this->cacheTtl * 24, function () use ($service, $floor) {
            return $this->request('GenreSearch', [
                'api_id' => $this->apiId,
                'affiliate_id' => $this->affiliateId,
                'floor_id' => $floor,
                'output' => 'json',
            ]);
        });
    }

    public function getActresses(string $keyword = '', int $hits = 20): array
    {
        $params = [
            'api_id' => $this->apiId,
            'affiliate_id' => $this->affiliateId,
            'hits' => $hits,
            'output' => 'json',
        ];

        if ($keyword) {
            $params['keyword'] = $keyword;
        }

        $cacheKey = 'fanza_actresses_' . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($params) {
            return $this->request('ActressSearch', $params);
        });
    }

    public function buildAffiliateUrl(string $url): string
    {
        if (!str_starts_with($url, 'http')) {
            $url = 'https://www.dmm.co.jp' . $url;
        }

        return 'https://al.dmm.co.jp/?lurl=' . urlencode($url)
            . '&af_id=' . urlencode($this->affiliateId)
            . '&ch=link_tool&ch_id=link';
    }

    public function getItemUrl(array $item): string
    {
        // APIのaffiliateURLはal.fanza.co.jp/al.dmm.com経由で無効なため
        // 商品の直接URL(item['URL'])からal.dmm.co.jp経由で自前で組み立てる
        $productUrl = $item['URL'] ?? '';

        if (empty($productUrl) || $productUrl === '#') {
            return '#';
        }

        return $this->buildAffiliateUrl($productUrl);
    }

    private function request(string $endpoint, array $params): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning("FANZA API request failed", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error("FANZA API request exception", [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiId)
            && $this->apiId !== 'your_api_id_here'
            && !empty($this->affiliateId)
            && $this->affiliateId !== 'your_affiliate_id_here';
    }

    public function getSampleItems(string $category = 'douga', int $count = 20): array
    {
        $sampleData = $this->generateSampleData($category, $count);
        return [
            'result' => [
                'status' => 200,
                'result_count' => $count,
                'total_count' => 1000,
                'first_position' => 1,
                'items' => $sampleData,
            ],
        ];
    }

    private function generateSampleData(string $category, int $count): array
    {
        $items = [];
        $titles = $this->getSampleTitles($category);
        $actresses = ['田中美咲', '佐藤花', '鈴木結衣', '高橋あかり', '山田麻衣', '伊藤さくら', '渡辺美月', '中村ひなた'];
        $makers = ['S1 NO.1 STYLE', 'MOODYZ', 'Prestige', 'IDEAPOCKET', 'kawaii*', 'Attackers', 'Madonna', 'FALENO'];
        $labels = ['エスワン', 'MOODYZ DIVA', 'PRESTIGE', 'ティッシュ', 'kawaii*', 'アタッカーズ', 'マドンナ', 'FALENO'];

        for ($i = 0; $i < $count; $i++) {
            $titleIdx = $i % count($titles);
            $actressIdx = $i % count($actresses);
            $makerIdx = $i % count($makers);
            $date = now()->subDays(rand(1, 60))->format('Y-m-d H:i:s');

            $items[] = [
                'content_id' => 'sample_' . ($i + 1),
                'product_id' => 'sample_' . ($i + 1),
                'title' => $titles[$titleIdx],
                'URL' => '#',
                'affiliateURL' => '#',
                'imageURL' => [
                    'list' => '',
                    'small' => '',
                    'large' => '',
                ],
                'sampleImageURL' => null,
                'sampleMovieURL' => null,
                'prices' => [
                    'price' => '¥' . (rand(5, 30) * 100),
                    'deliveries' => [
                        'delivery' => [
                            ['type' => 'stream', 'price' => '¥' . (rand(3, 20) * 100)],
                        ],
                    ],
                ],
                'date' => $date,
                'iteminfo' => [
                    'genre' => array_map(fn($g) => ['id' => rand(1000, 9999), 'name' => $g], $this->getSampleGenres($category)),
                    'actress' => [['id' => rand(10000, 99999), 'name' => $actresses[$actressIdx]]],
                    'maker' => [['id' => rand(100, 999), 'name' => $makers[$makerIdx]]],
                    'label' => [['id' => rand(100, 999), 'name' => $labels[$makerIdx]]],
                ],
                'review' => [
                    'count' => rand(5, 200),
                    'average' => number_format(rand(30, 50) / 10, 2),
                ],
            ];
        }

        return $items;
    }

    private function getSampleTitles(string $category): array
    {
        $baseTitles = [
            'douga' => [
                '新人 専属デビュー', '完全ガチ撮り プライベート映像',
                '最高峰ボディで魅せる', 'はじめての撮影体験',
                'ラグジュアリーな時間', '禁断の関係', 'ひみつの放課後',
                '美しすぎる瞬間', '甘い誘惑', '情熱のとき',
            ],
            'vr' => [
                '【VR】没入体験', '【VR】至近距離で感じる',
                '【VR】目の前に広がる世界', '【VR】あなただけの空間',
                '【VR】超リアル体験', '【VR】360度パノラマ',
            ],
            'dvd' => [
                'コンプリートBOX', 'ベストコレクション',
                'プレミアムエディション', '限定パッケージ',
                'スペシャルBOX', '完全保存版',
            ],
            'rental' => [
                '話題作レンタル開始', '大人気シリーズ最新作',
                'レンタル限定特典付き', '今月のおすすめ',
            ],
            'comic' => [
                '禁断のラブストーリー', '秘密の恋愛漫画',
                '大人のコミック', '人気連載最新巻',
                'オリジナルコミック新刊', '話題のコミカライズ',
            ],
        ];

        return $baseTitles[$category] ?? $baseTitles['douga'];
    }

    private function getSampleGenres(string $category): array
    {
        $genres = [
            'douga' => ['ハイビジョン', '独占配信', '単体作品', 'デジモ'],
            'vr' => ['VR専用', 'ハイクオリティVR', '60fps', '高画質'],
            'dvd' => ['DVD', 'ブルーレイ対応', '特典映像付き'],
            'rental' => ['レンタル', '新作', '準新作'],
            'comic' => ['フルカラー', 'オリジナル', '連載'],
        ];

        $categoryGenres = $genres[$category] ?? $genres['douga'];
        shuffle($categoryGenres);

        return array_slice($categoryGenres, 0, rand(2, 3));
    }
}
