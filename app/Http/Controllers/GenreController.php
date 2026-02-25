<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = config('fanza.genres');

        return view('genre.index', [
            'genres' => $genres,
        ]);
    }

    public function show(string $slug, Request $request, FanzaApiService $api)
    {
        $genres = config('fanza.genres');

        if (!isset($genres[$slug])) {
            abort(404);
        }

        $genre = $genres[$slug];
        $page = max(1, (int) $request->input('page', 1));
        $sort = $request->input('sort', 'rank');
        $hits = 20;
        $offset = (($page - 1) * $hits) + 1;

        $result = $api->getItems([
            'service' => 'digital',
            'floor' => 'videoa',
            'hits' => $hits,
            'offset' => $offset,
            'sort' => $sort,
            'keyword' => $genre['keyword'],
        ]);

        $items = $result['result']['items'] ?? [];
        $totalCount = $result['result']['total_count'] ?? 0;
        $totalPages = ceil($totalCount / $hits);

        return view('genre.show', [
            'slug' => $slug,
            'genre' => $genre,
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'sort' => $sort,
        ]);
    }
}
