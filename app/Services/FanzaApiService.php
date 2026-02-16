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

    public function getItemUrl(array $item): string
    {
        return $item['affiliateURL'] ?? '#';
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
}
