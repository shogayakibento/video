<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActressController extends Controller
{
    private const INITIALS = [
        'あ' => 'あ行', 'か' => 'か行', 'さ' => 'さ行', 'た' => 'た行',
        'な' => 'な行', 'は' => 'は行', 'ま' => 'ま行', 'や' => 'や行',
        'ら' => 'ら行', 'わ' => 'わ行',
    ];

    // Cup size → approximate bust (cm) range used for API gte_bust/lte_bust params
    private const CUP_BUST_MAP = [
        'A' => [72, 80],
        'B' => [75, 83],
        'C' => [78, 86],
        'D' => [81, 89],
        'E' => [84, 92],
        'F' => [87, 95],
        'G' => [90, 100],
        'H' => [93, 110],
        'I' => [96, 120],
    ];

public function index(Request $request, FanzaApiService $api)
    {
        $tab = $request->input('tab', 'ranking');
        $page = max(1, (int) $request->input('page', 1));

        if ($tab === 'ranking') {
            return $this->ranking($page, $api);
        }

        if ($tab === 'filter') {
            return $this->filter($request, $page, $api);
        }

        // search / initial
        return $this->search($request, $page, $api);
    }

    /**
     * Paginate and photo-enrich a pre-built pool for the current page.
     * Only 24 ActressSearch calls are made per page; each result is individually
     * cached in the service, so repeat page loads are instant.
     */
    private function paginatePool(array $pool, int $page, int $hits, FanzaApiService $api, int $maxPages = 10): array
    {
        $totalCount = count($pool);
        $totalPages = min((int) ceil($totalCount / $hits), $maxPages);
        $slice      = array_slice($pool, ($page - 1) * $hits, $hits);

        $actresses = array_map(function ($a) use ($api) {
            $detail = $api->getActresses(['actress_id' => $a['id']]);
            $info   = $detail['result']['actress'][0] ?? null;
            return array_merge($a, ['imageURL' => $info['imageURL'] ?? []]);
        }, $slice);

        return compact('actresses', 'totalCount', 'totalPages');
    }

    private function ranking(int $page, FanzaApiService $api)
    {
        $hits = 24;
        $pool = $api->getRankingPool();

        ['actresses' => $actresses, 'totalCount' => $totalCount, 'totalPages' => $totalPages]
            = $this->paginatePool($pool, $page, $hits, $api);

        return view('actress.index', [
            'tab'         => 'ranking',
            'actresses'   => $actresses,
            'keyword'     => '',
            'initial'     => '',
            'initials'    => self::INITIALS,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalCount'  => $totalCount,
            'rankOffset'  => ($page - 1) * $hits,
            'filters'     => [],
        ]);
    }

    private function filter(Request $request, int $page, FanzaApiService $api)
    {
        $cup       = (string) ($request->input('cup') ?? '');
        $heightMin = (string) ($request->input('height_min') ?? '');
        $heightMax = (string) ($request->input('height_max') ?? '');
        $ageMin    = (string) ($request->input('age_min') ?? '');
        $ageMax    = (string) ($request->input('age_max') ?? '');

        $filters = compact('cup', 'heightMin', 'heightMax', 'ageMin', 'ageMax');
        $hits    = 24;

        // No active filters → show popular ranking pool
        if (!array_filter($filters)) {
            $pool = $api->getRankingPool();
            ['actresses' => $actresses, 'totalCount' => $totalCount, 'totalPages' => $totalPages]
                = $this->paginatePool($pool, $page, $hits, $api);
        } else {
            $offset = (($page - 1) * $hits) + 1;
            $params = ['hits' => $hits, 'offset' => $offset];

            if ($cup && isset(self::CUP_BUST_MAP[$cup])) {
                [$bustMin, $bustMax] = self::CUP_BUST_MAP[$cup];
                $params['gte_bust'] = $bustMin;
                $params['lte_bust'] = $bustMax;
            }
            if ($heightMin) $params['gte_height'] = $heightMin;
            if ($heightMax) $params['lte_height'] = $heightMax;

            $today = new \DateTime();
            if ($ageMin) $params['lte_birthday'] = (clone $today)->modify("-{$ageMin} years")->format('Y-m-d');
            if ($ageMax) $params['gte_birthday'] = (clone $today)->modify('-' . ($ageMax + 1) . ' years + 1 day')->format('Y-m-d');

            $result     = $api->getActresses($params) ?? [];
            $actresses  = $result['result']['actress'] ?? [];
            $totalCount = (int) ($result['result']['total_count'] ?? 0);
            $totalPages = min((int) ceil($totalCount / $hits), 50);
        }

        return view('actress.index', [
            'tab'         => 'filter',
            'actresses'   => $actresses,
            'keyword'     => '',
            'initial'     => '',
            'initials'    => self::INITIALS,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalCount'  => $totalCount,
            'rankOffset'  => 0,
            'filters'     => $filters,
        ]);
    }

    private function search(Request $request, int $page, FanzaApiService $api)
    {
        $keyword = $request->input('keyword', '');
        $initial = $request->input('initial', '');
        $hits    = 24;

        // No keyword/initial → show popular ranking pool
        if (!$keyword && !$initial) {
            $pool = $api->getRankingPool();
            ['actresses' => $actresses, 'totalCount' => $totalCount, 'totalPages' => $totalPages]
                = $this->paginatePool($pool, $page, $hits, $api);
        } else {
            $params = ['hits' => $hits, 'offset' => (($page - 1) * $hits) + 1];
            if ($keyword) {
                $params['keyword'] = $keyword;
            } else {
                $params['initial'] = $initial;
            }
            $result     = $api->getActresses($params) ?? [];
            $actresses  = $result['result']['actress'] ?? [];
            $totalCount = $result['result']['total_count'] ?? 0;
            $totalPages = min((int) ceil($totalCount / $hits), 50);
        }

        return view('actress.index', [
            'tab'         => 'search',
            'actresses'   => $actresses,
            'keyword'     => $keyword,
            'initial'     => $initial,
            'initials'    => self::INITIALS,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'totalCount'  => $totalCount,
            'rankOffset'  => 0,
            'filters'     => [],
        ]);
    }

    public function showByName(string $name, FanzaApiService $api)
    {
        $actressId = \Illuminate\Support\Facades\Cache::remember(
            'actress_id_by_name_' . md5($name),
            86400 * 30,
            function () use ($name, $api) {
                $result = $api->getActresses(['keyword' => $name, 'hits' => 1]);
                return $result['result']['actress'][0]['id'] ?? null;
            }
        );

        if ($actressId) {
            return redirect()->route('actress.show', $actressId);
        }

        return redirect()->route('actress.index');
    }

    public function show(string $id, Request $request, FanzaApiService $api)
    {
        $actressResult = $api->getActresses(['actress_id' => $id]);

        // null = API通信エラー → 503でGoogleに再クロールを促す
        if ($actressResult === null) {
            abort(503, 'Service temporarily unavailable. Please try again later.');
        }

        $actress = $actressResult['result']['actress'][0] ?? null;

        if (!$actress) {
            abort(404);
        }

        $page = max(1, (int) $request->input('page', 1));
        $sort = $request->input('sort', 'rank');
        $cast = $request->input('cast', 'all'); // all / solo / multi
        $hits = 20;

        $baseParams = [
            'service'    => 'digital',
            'floor'      => 'videoa',
            'sort'       => $sort,
            'article'    => 'actress',
            'article_id' => $id,
        ];

        if ($cast === 'all') {
            $offset     = (($page - 1) * $hits) + 1;
            $itemResult = $api->getItems(array_merge($baseParams, [
                'hits'   => $hits,
                'offset' => $offset,
            ]));
            $items      = $itemResult['result']['items'] ?? [];
            $totalCount = (int) ($itemResult['result']['total_count'] ?? 0);
            $totalPages = min((int) ceil($totalCount / $hits), 50);
        } else {
            // 最大100件取得してPHP側で絞り込み・ページネーション
            $itemResult = $api->getItems(array_merge($baseParams, [
                'hits'   => 100,
                'offset' => 1,
            ]));
            $allItems = $itemResult['result']['items'] ?? [];
            $filtered = array_values(array_filter($allItems, function ($item) use ($cast) {
                $count = count($item['iteminfo']['actress'] ?? []);
                return $cast === 'solo' ? $count <= 1 : $count > 1;
            }));
            $totalCount = count($filtered);
            $items      = array_slice($filtered, ($page - 1) * $hits, $hits);
            $totalPages = max(1, (int) ceil($totalCount / $hits));
        }

        // --- Similar Actresses ---
        $cacheKey = 'similar_actresses_v7_' . $id;
        $similarActresses = Cache::get($cacheKey);
        if ($similarActresses === null) {
            $similarActresses = $this->findSimilarByMeasurements($api, $id, $actress);
            if (empty($similarActresses)) {
                // Fallback: co-star frequency from existing video items
                $similarActresses = $this->findSimilarByCoStars($api, $id, $baseParams);
            }
            if (!empty($similarActresses)) {
                Cache::put($cacheKey, $similarActresses, 86400);
            }
        }

        return view('actress.show', [
            'actress'          => $actress,
            'items'            => $items,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'totalCount'       => $totalCount,
            'sort'             => $sort,
            'cast'             => $cast,
            'similarActresses' => $similarActresses,
        ]);
    }

    /**
     * Find actresses with similar body measurements using weighted Euclidean distance.
     * Uses FANZA ActressSearch API filtered by bust/height range, then scores by
     * all available dimensions: height, bust, cup, waist, hip, age.
     */
    private function findSimilarByMeasurements(FanzaApiService $api, string $id, array $actress): array
    {
        $cupOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9];

        $height = ($actress['height']   ?? '') !== '' ? (int) $actress['height'] : null;
        $bust   = ($actress['bust']     ?? '') !== '' ? (int) $actress['bust']   : null;
        $waist  = ($actress['waist']    ?? '') !== '' ? (int) $actress['waist']  : null;
        $hip    = ($actress['hip']      ?? '') !== '' ? (int) $actress['hip']    : null;
        $cup    = ($actress['cup']      ?? '') !== '' ? ($cupOrder[strtoupper($actress['cup'])] ?? null) : null;
        $age    = ($actress['birthday'] ?? '') !== '' ? (int) date('Y') - (int) substr($actress['birthday'], 0, 4) : null;

        if ($bust === null && $height === null) {
            return [];
        }

        $params = ['hits' => 100, 'offset' => 1];
        if ($bust !== null) {
            $params['gte_bust'] = $bust - 10;
            $params['lte_bust'] = $bust + 10;
        }
        if ($height !== null) {
            $params['gte_height'] = $height - 8;
            $params['lte_height'] = $height + 8;
        }

        $candidates = $api->getActresses($params)['result']['actress'] ?? [];

        $poolIds = array_flip(array_column($api->getRankingPool(), 'id'));

        $scored = [];
        foreach ($candidates as $a) {
            $aid = (string) ($a['id'] ?? '');
            if (!$aid || $aid === (string) $id) {
                continue;
            }
            if (empty($a['imageURL']['large']) && empty($a['imageURL']['small'])) {
                continue;
            }
            // プール内の女優を優先（スコアにボーナス）
            $poolBonus = isset($poolIds[$aid]) ? 0 : 0.5;

            $score = 0.0;
            $dims  = 0;

            if ($height !== null && ($a['height'] ?? '') !== '') {
                $score += (($height - (int) $a['height']) / 10) ** 2;
                $dims++;
            }
            if ($bust !== null && ($a['bust'] ?? '') !== '') {
                $score += (($bust - (int) $a['bust']) / 8) ** 2;
                $dims++;
            }
            if ($waist !== null && ($a['waist'] ?? '') !== '') {
                $score += (($waist - (int) $a['waist']) / 6) ** 2;
                $dims++;
            }
            if ($hip !== null && ($a['hip'] ?? '') !== '') {
                $score += (($hip - (int) $a['hip']) / 6) ** 2;
                $dims++;
            }
            if ($cup !== null && ($a['cup'] ?? '') !== '') {
                $aCup = $cupOrder[strtoupper($a['cup'])] ?? null;
                if ($aCup !== null) {
                    $score += (($cup - $aCup) / 2) ** 2;
                    $dims++;
                }
            }
            if ($age !== null && ($a['birthday'] ?? '') !== '') {
                $aAge = (int) date('Y') - (int) substr($a['birthday'], 0, 4);
                $score += (($age - $aAge) / 5) ** 2;
                $dims++;
            }

            if ($dims === 0) {
                continue;
            }

            $scored[] = ['actress' => $a, 'score' => sqrt($score) + $poolBonus];
        }

        usort($scored, fn($x, $y) => $x['score'] <=> $y['score']);

        return array_map(fn($s) => $s['actress'], array_slice($scored, 0, 6));
    }

    /**
     * Fallback: find actresses who frequently co-star with the target actress.
     * Fetches top 50 videos and counts co-star appearances.
     */
    private function findSimilarByCoStars(FanzaApiService $api, string $id, array $baseParams): array
    {
        $videos = $api->getItems(array_merge($baseParams, ['hits' => 50, 'offset' => 1, 'sort' => 'rank']))['result']['items'] ?? [];

        $countMap = [];
        foreach ($videos as $item) {
            foreach ($item['iteminfo']['actress'] ?? [] as $a) {
                $aid = (string) ($a['id'] ?? '');
                if ($aid && $aid !== (string) $id) {
                    $countMap[$aid] = ($countMap[$aid] ?? 0) + 1;
                }
            }
        }

        if (empty($countMap)) {
            return [];
        }

        arsort($countMap);
        $topIds = array_slice(array_keys($countMap), 0, 6);

        $result = [];
        foreach ($topIds as $aid) {
            $info = $api->getActresses(['actress_id' => $aid])['result']['actress'][0] ?? null;
            if ($info) {
                $result[] = $info;
            }
        }
        return $result;
    }
}
