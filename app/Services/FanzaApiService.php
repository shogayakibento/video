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

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->request('ItemList', $query);
        if ($result !== null) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result ?? [];
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

    /**
     * Build and cache an ordered list of popular actress IDs derived from ranked products.
     * Returns [{id, name, ruby}] only — photo enrichment is done per page in the controller
     * to avoid bulk API calls that may be rate-limited or time out.
     * Cached for 2 hours (only 3 ItemList calls needed to build).
     */
    public function getRankingPool(): array
    {
        return Cache::remember('actress_ranking_pool_v2', 7200, function () {
            $seen = [];
            $pool = [];

            foreach ([1, 101, 201] as $offset) {
                $result = $this->getItems(['hits' => 100, 'offset' => $offset, 'sort' => 'rank']);
                foreach ($result['result']['items'] ?? [] as $item) {
                    foreach ($item['iteminfo']['actress'] ?? [] as $a) {
                        $id = $a['id'] ?? null;
                        if ($id && !isset($seen[$id])) {
                            $seen[$id] = true;
                            $pool[] = ['id' => $id, 'name' => $a['name'] ?? '', 'ruby' => $a['ruby'] ?? ''];
                        }
                    }
                }
            }

            return $pool;
        });
    }

    public function getActresses(array $overrides = []): ?array
    {
        $defaults = [
            'api_id' => $this->apiId,
            'affiliate_id' => $this->affiliateId,
            'hits' => 30,
            'offset' => 1,
            'output' => 'json',
        ];

        $params = array_merge($defaults, $overrides);
        $cacheKey = 'fanza_actresses_' . md5(json_encode($params));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->request('ActressSearch', $params);
        if ($result !== null) {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        }

        return $result;
    }

    private function request(string $endpoint, array $params): ?array
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

            return null;
        } catch (\Exception $e) {
            Log::error("FANZA API request exception", [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return null;
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
