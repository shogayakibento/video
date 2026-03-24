<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\FanzaApiService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $content = Cache::remember('sitemap_index_xml', 3600, function () {
            $today = now()->toAtomString();

            $sitemaps = [
                ['loc' => route('sitemap.pages'),    'lastmod' => $today],
                ['loc' => route('sitemap.actresses'), 'lastmod' => $today],
                ['loc' => route('sitemap.videos'),   'lastmod' => $today],
            ];

            $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($sitemaps as $s) {
                $loc = htmlspecialchars($s['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= "  <sitemap>\n";
                $xml .= "    <loc>{$loc}</loc>\n";
                $xml .= "    <lastmod>{$s['lastmod']}</lastmod>\n";
                $xml .= "  </sitemap>\n";
            }
            $xml .= '</sitemapindex>';

            return $xml;
        });

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function pages(): Response
    {
        $content = Cache::remember('sitemap_pages_xml', 3600, function () {
            $categories    = config('fanza.categories');
            $genres        = config('fanza.genres');
            $today         = now()->toAtomString();
            $weeklyLastmod = now()->startOfWeek()->toAtomString();

            $urls = [
                ['loc' => route('home'),                'priority' => '1.0', 'changefreq' => 'daily',  'lastmod' => $today],
                ['loc' => route('ranking'),             'priority' => '0.9', 'changefreq' => 'daily',  'lastmod' => $today],
                ['loc' => route('tweet.ranking.index'), 'priority' => '0.9', 'changefreq' => 'daily',  'lastmod' => $today],
                ['loc' => route('shorts'),              'priority' => '0.8', 'changefreq' => 'daily',  'lastmod' => $today],
                ['loc' => route('actress.index'),       'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $weeklyLastmod],
                ['loc' => route('genre.index'),         'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $weeklyLastmod],
                ['loc' => route('privacy'),             'priority' => '0.3', 'changefreq' => 'yearly', 'lastmod' => $weeklyLastmod],
            ];

            foreach ($categories as $slug => $cat) {
                $urls[] = [
                    'loc'        => route('category.show', $slug),
                    'priority'   => '0.8',
                    'changefreq' => 'daily',
                    'lastmod'    => $today,
                ];
            }

            // /ranking?category=X （dougaはデフォルトと重複するため除外）
            foreach ($categories as $slug => $cat) {
                if ($slug === 'douga') continue;
                $urls[] = [
                    'loc'        => route('ranking', ['category' => $slug]),
                    'priority'   => '0.8',
                    'changefreq' => 'daily',
                    'lastmod'    => $today,
                ];
            }

            foreach ($genres as $slug => $genre) {
                $urls[] = [
                    'loc'        => route('genre.show', $slug),
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod'    => $weeklyLastmod,
                ];
            }

            return $this->buildUrlset($urls);
        });

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function actresses(FanzaApiService $api): Response
    {
        $content = Cache::remember('sitemap_actresses_xml', 86400, function () use ($api) {
            $weeklyLastmod = now()->startOfWeek()->toAtomString();

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

            $urls = [];
            foreach ($actressIds as $id) {
                $urls[] = [
                    'loc'        => route('actress.show', $id),
                    'priority'   => '0.6',
                    'changefreq' => 'weekly',
                    'lastmod'    => $weeklyLastmod,
                ];
            }

            return $this->buildUrlset($urls);
        });

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function videos(): Response
    {
        $content = Cache::remember('sitemap_videos_xml', 3600, function () {
            $urls = [];
            Video::orderByDesc('total_likes')->limit(200)->get(['id', 'updated_at'])
                ->each(function ($video) use (&$urls) {
                    $urls[] = [
                        'loc'        => route('tweet.video.show', $video->id),
                        'priority'   => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod'    => $video->updated_at->toAtomString(),
                    ];
                });

            return $this->buildUrlset($urls);
        });

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    private function buildUrlset(array $urls): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $loc = htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
