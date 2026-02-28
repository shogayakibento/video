<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(FanzaApiService $api): Response
    {
        $categories = config('fanza.categories');
        $genres = config('fanza.genres');

        $urls = [
            ['loc' => route('home'),          'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('ranking'),        'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('actress.index'),  'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('genre.index'),    'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('search'),         'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        // カテゴリページ
        foreach ($categories as $slug => $cat) {
            $urls[] = [
                'loc'        => route('category.show', $slug),
                'priority'   => '0.8',
                'changefreq' => 'daily',
            ];
        }

        // ジャンル詳細ページ（config から静的に取得）
        foreach ($genres as $slug => $genre) {
            $urls[] = [
                'loc'        => route('genre.show', $slug),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
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
            ];
        }

        $content  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $content .= "  <url>\n";
            $content .= "    <loc>{$url['loc']}</loc>\n";
            $content .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $content .= "    <priority>{$url['priority']}</priority>\n";
            $content .= "  </url>\n";
        }

        $content .= '</urlset>';

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
