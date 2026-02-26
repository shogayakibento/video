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

    private function ranking(int $page, FanzaApiService $api)
    {
        $hits = 30;
        $offset = (($page - 1) * $hits) + 1;

        $itemResult = $api->getItems([
            'service' => 'digital',
            'floor' => 'videoa',
            'hits' => $hits,
            'offset' => $offset,
            'sort' => 'rank',
        ]);

        $items = $itemResult['result']['items'] ?? [];

        // Extract unique actresses and fetch their face photos
        $seen = [];
        $actresses = [];
        foreach ($items as $item) {
            $actressInfo = $item['iteminfo']['actress'] ?? [];
            foreach ($actressInfo as $a) {
                $id = $a['id'] ?? null;
                if ($id && !isset($seen[$id])) {
                    $seen[$id] = true;

                    // Fetch actress face photo from ActressSearch API
                    $actressResult = $api->getActresses(['actress_id' => $id, 'hits' => 1]);
                    $actressData = $actressResult['result']['actress'][0] ?? null;

                    $actresses[] = [
                        'id' => $id,
                        'name' => $a['name'] ?? '',
                        'ruby' => $a['ruby'] ?? '',
                        'imageURL' => $actressData['imageURL'] ?? [],
                        'top_item_image' => $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? '',
                        'top_item_title' => $item['title'] ?? '',
                    ];
                }
            }
        }

        return view('actress.index', [
            'tab' => 'ranking',
            'actresses' => $actresses,
            'keyword' => '',
            'initial' => '',
            'initials' => self::INITIALS,
            'currentPage' => $page,
            'totalPages' => 5,
            'totalCount' => count($actresses),
            'filters' => [],
        ]);
    }

    private function filter(Request $request, int $page, FanzaApiService $api)
    {
        $cup = $request->input('cup', '');
        $heightMin = $request->input('height_min', '');
        $heightMax = $request->input('height_max', '');
        $ageMin = $request->input('age_min', '');
        $ageMax = $request->input('age_max', '');

        $hits = 30;
        $offset = (($page - 1) * $hits) + 1;

        $params = [
            'hits' => $hits,
            'offset' => $offset,
        ];

        if ($cup && isset(self::CUP_BUST_MAP[$cup])) {
            [$bustMin, $bustMax] = self::CUP_BUST_MAP[$cup];
            $params['bust_gte'] = $bustMin;
            $params['bust_lte'] = $bustMax;
        }

        if ($heightMin) {
            $params['height_gte'] = (int) $heightMin;
        }
        if ($heightMax) {
            $params['height_lte'] = (int) $heightMax;
        }

        if ($ageMax) {
            $params['birthday_gte'] = date('Y-m-d', strtotime("-{$ageMax} years"));
        }
        if ($ageMin) {
            $params['birthday_lte'] = date('Y-m-d', strtotime("-{$ageMin} years"));
        }

        $result = $api->getActresses($params);
        $actresses = $result['result']['actress'] ?? [];
        $totalCount = $result['result']['total_count'] ?? 0;
        $totalPages = (int) ceil($totalCount / $hits);

        $filters = compact('cup', 'heightMin', 'heightMax', 'ageMin', 'ageMax');

        return view('actress.index', [
            'tab' => 'filter',
            'actresses' => $actresses,
            'keyword' => '',
            'initial' => '',
            'initials' => self::INITIALS,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'filters' => $filters,
        ]);
    }

    private function search(Request $request, int $page, FanzaApiService $api)
    {
        $keyword = $request->input('keyword', '');
        $initial = $request->input('initial', '');
        $hits = 30;
        $offset = (($page - 1) * $hits) + 1;

        $params = [
            'hits' => $hits,
            'offset' => $offset,
        ];

        if ($keyword) {
            $params['keyword'] = $keyword;
        } elseif ($initial) {
            $params['initial'] = $initial;
        }

        $result = $api->getActresses($params);
        $actresses = $result['result']['actress'] ?? [];
        $totalCount = $result['result']['total_count'] ?? 0;
        $totalPages = (int) ceil($totalCount / $hits);

        return view('actress.index', [
            'tab' => 'search',
            'actresses' => $actresses,
            'keyword' => $keyword,
            'initial' => $initial,
            'initials' => self::INITIALS,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'filters' => [],
        ]);
    }

    public function show(string $id, Request $request, FanzaApiService $api)
    {
        $actressResult = $api->getActresses(['actress_id' => $id]);
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
