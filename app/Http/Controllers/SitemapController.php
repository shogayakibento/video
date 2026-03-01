<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\FanzaApiService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(FanzaApiService $api): Response
    {
        $content = Cache::remember('sitemap_xml', 3600, function () use ($api) {
            return $this->buildSitemap($api);
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function buildSitemap(FanzaApiService $api): string
    {
        $categories = config('fanza.categories');
        $genres = config('fanza.genres');

        $today = now()->toAtomString();

        $urls = [
            ['loc' => route('home'),                 'priority' => '1.0', 'changefreq' => 'daily',  'lastmod' => $today],
            ['loc' => route('ranking'),              'priority' => '0.9', 'changefreq' => 'daily',  'lastmod' => $today],
            ['loc' => route('tweet.ranking.index'),  'priority' => '0.9', 'changefreq' => 'daily',  'lastmod' => $today],
            ['loc' => route('actress.index'),        'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => route('genre.index'),          'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => route('search'),               'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $today],
        ];

        // カテゴリページ
        foreach ($categories as $slug => $cat) {
            $urls[] = [
                'loc'        => route('category.show', $slug),
                'priority'   => '0.8',
                'changefreq' => 'daily',
                'lastmod'    => $today,
            ];
        }

        // ジャンル詳細ページ（config から静的に取得）
        foreach ($genres as $slug => $genre) {
            $urls[] = [
                'loc'        => route('genre.show', $slug),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
                'lastmod'    => $today,
            ];
        }

        // 人気女優ページ（ランキング上位100作品の出演者、24時間キャッシュ）
        $actressIds = Cache::remember('sitemap_actress_ids', 86400, function () use ($api) {
            $result = $api->getItems([
                'service' => 'digital',
                'floor'   => 'videoa',
                'hits'    => 100,
                'offset'  => 1,
                'sort'    => 'rank',
            ]);

            $seen = [];
            foreach ($result['result']['items'] ?? [] as $item) {
                foreach ($item['iteminfo']['actress'] ?? [] as $a) {
                    $id = $a['id'] ?? null;
                    if ($id && !isset($seen[$id])) {
                        $seen[$id] = $id;
                    }
                }
            }

            return array_values($seen);
        });

        foreach ($actressIds as $id) {
            $urls[] = [
                'loc'        => route('actress.show', $id),
                'priority'   => '0.6',
                'changefreq' => 'weekly',
                'lastmod'    => $today,
            ];
        }

        // Xバズり動画詳細ページ
        Video::orderByDesc('total_likes')->limit(200)->get(['id', 'updated_at'])
            ->each(function ($video) use (&$urls) {
                $urls[] = [
                    'loc'        => route('tweet.video.show', $video->id),
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod'    => $video->updated_at->toAtomString(),
                ];
            });

        $content  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $content .= "  <url>\n";
            $content .= "    <loc>{$url['loc']}</loc>\n";
            if (!empty($url['lastmod'])) {
                $content .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $content .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $content .= "    <priority>{$url['priority']}</priority>\n";
            $content .= "  </url>\n";
        }

        $content .= '</urlset>';

        return $content;
    }
}
