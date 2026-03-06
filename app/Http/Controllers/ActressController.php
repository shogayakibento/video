<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

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
        $hits = 20;
        $offset = (($page - 1) * $hits) + 1;

        $itemResult = $api->getItems([
            'service' => 'digital',
            'floor' => 'videoa',
            'hits' => $hits,
            'offset' => $offset,
            'sort' => $sort,
            'article' => 'actress',
            'article_id' => $id,
        ]);

        $items = $itemResult['result']['items'] ?? [];
        $totalCount = $itemResult['result']['total_count'] ?? 0;
        $totalPages = (int) ceil($totalCount / $hits);

        return view('actress.show', [
            'actress' => $actress,
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'sort' => $sort,
        ]);
    }
}
