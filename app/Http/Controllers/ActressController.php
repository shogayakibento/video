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

    public function index(Request $request, FanzaApiService $api)
    {
        $keyword = $request->input('keyword', '');
        $initial = $request->input('initial', '');
        $page = max(1, (int) $request->input('page', 1));
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
        $totalPages = ceil($totalCount / $hits);

        return view('actress.index', [
            'actresses' => $actresses,
            'keyword' => $keyword,
            'initial' => $initial,
            'initials' => self::INITIALS,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
        ]);
    }

    public function show(string $id, Request $request, FanzaApiService $api)
    {
        // Get actress info
        $actressResult = $api->getActresses(['actress_id' => $id]);
        $actress = $actressResult['result']['actress'][0] ?? null;

        if (!$actress) {
            abort(404);
        }

        // Get her works
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
        $totalPages = ceil($totalCount / $hits);

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
