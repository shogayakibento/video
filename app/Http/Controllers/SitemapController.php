<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $categories = config('fanza.categories');

        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('ranking'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('search'), 'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        foreach ($categories as $slug => $cat) {
            $urls[] = [
                'loc' => route('category.show', $slug),
                'priority' => '0.8',
                'changefreq' => 'daily',
            ];
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
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
