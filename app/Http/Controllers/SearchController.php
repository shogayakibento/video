<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, FanzaApiService $api)
    {
        $keyword = $request->input('q', '');
        $service = $request->input('service', 'digital');
        $floor = $request->input('floor', 'videoa');
        $sort = in_array($request->input('sort'), ['rank', 'date', 'review']) ? $request->input('sort') : 'rank';
        $page = max(1, (int) $request->input('page', 1));
        $hits = 20;
        $offset = (($page - 1) * $hits) + 1;

        $items = [];
        $totalCount = 0;

        if ($keyword) {
            $result = $api->search($keyword, $service, $floor, $hits, $offset, $sort);
            $items = $result['result']['items'] ?? [];
            $totalCount = $result['result']['total_count'] ?? 0;
        }

        $totalPages = ceil($totalCount / $hits);

        return view('search.index', [
            'keyword' => $keyword,
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'service' => $service,
            'floor' => $floor,
            'sort' => $sort,
            'categories' => config('fanza.categories'),
        ]);
    }
}
