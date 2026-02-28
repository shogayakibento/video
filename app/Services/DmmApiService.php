<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DmmApiService
{
    private string $apiId;
    private string $affiliateId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiId = config('dmm.api_id');
        $this->affiliateId = config('dmm.affiliate_id');
        $this->baseUrl = config('dmm.base_url');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiId) && !empty($this->affiliateId);
    }

    public function fetchByContentId(string $contentId): ?array
    {
        $siteFloorCombos = [
            ['site' => 'FANZA', 'service' => 'digital', 'floor' => 'videoa'],
            ['site' => 'FANZA', 'service' => 'digital', 'floor' => 'videoc'],
            ['site' => 'FANZA', 'service' => 'mono', 'floor' => 'dvd'],
        ];

        foreach ($siteFloorCombos as $combo) {
            $params = array_merge($combo, [
                'api_id' => $this->apiId,
                'affiliate_id' => $this->affiliateId,
                'cid' => $contentId,
                'hits' => 1,
                'output' => 'json',
            ]);

            try {
                $response = Http::get($this->baseUrl, $params);

                if ($response->successful()) {
                    $items = $response->json('result.items');
                    if (!empty($items)) {
                        return $items[0];
                    }
                }
            } catch (\Exception $e) {
                Log::error('DMM API error: ' . $e->getMessage());
            }
        }

        return null;
    }

    public function searchVideos(string $keyword = '', int $hits = 20, int $offset = 1, string $sort = 'date'): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('DMM API credentials not configured');
            return null;
        }

        $params = [
            'api_id' => $this->apiId,
            'affiliate_id' => $this->affiliateId,
            'site' => 'FANZA',
            'service' => 'digital',
            'floor' => 'videoa',
            'hits' => $hits,
            'offset' => $offset,
            'sort' => $sort,
            'output' => 'json',
        ];

        if (!empty($keyword)) {
            $params['keyword'] = $keyword;
        }

        try {
            $response = Http::get($this->baseUrl, $params);

            if ($response->successful()) {
                return $response->json('result');
            }

            Log::error('DMM API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('DMM API error: ' . $e->getMessage());
        }

        return null;
    }

    public function importVideos(string $keyword = '', int $hits = 20): int
    {
        $result = $this->searchVideos($keyword, $hits);

        if (!$result || !isset($result['items'])) {
            return 0;
        }

        $imported = 0;

        foreach ($result['items'] as $item) {
            $contentId = $item['content_id'] ?? null;
            if (!$contentId) {
                continue;
            }

            $actresses = '';
            if (isset($item['iteminfo']['actress'])) {
                $actresses = collect($item['iteminfo']['actress'])
                    ->pluck('name')
                    ->implode(', ');
            }

            $genres = '';
            if (isset($item['iteminfo']['genre'])) {
                $genres = collect($item['iteminfo']['genre'])
                    ->pluck('name')
                    ->implode(', ');
            }

            $maker = '';
            if (isset($item['iteminfo']['maker'])) {
                $maker = collect($item['iteminfo']['maker'])
                    ->pluck('name')
                    ->first() ?? '';
            }

            $thumbnailUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? '';

            $sampleVideoUrl = '';
            if (isset($item['sampleMovieURL']['size_720_480'])) {
                $sampleVideoUrl = $item['sampleMovieURL']['size_720_480'];
            } elseif (isset($item['sampleMovieURL']['size_476_306'])) {
                $sampleVideoUrl = $item['sampleMovieURL']['size_476_306'];
            }

            $affiliateUrl = $item['affiliateURL'] ?? $item['URL'] ?? '';

            Video::updateOrCreate(
                ['dmm_content_id' => $contentId],
                [
                    'title' => $item['title'] ?? '',
                    'actress' => $actresses,
                    'thumbnail_url' => $thumbnailUrl,
                    'sample_video_url' => $sampleVideoUrl,
                    'affiliate_url' => $affiliateUrl,
                    'genre' => $genres,
                    'maker' => $maker,
                    'release_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : null,
                ]
            );

            $imported++;
        }

        return $imported;
    }
}
