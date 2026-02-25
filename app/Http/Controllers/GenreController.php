<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(Request $request, FanzaApiService $api)
    {
        $categories = config('fanza.categories');
        $activeCategory = $request->input('category', 'douga');

        if (!isset($categories[$activeCategory])) {
            $activeCategory = 'douga';
        }

        $category = $categories[$activeCategory];
        $result = $api->getGenres($category['service'], $category['floor']);
        $genres = $result['result']['genre'] ?? [];

        return view('genre.index', [
            'genres' => $genres,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'category' => $category,
        ]);
    }

    public function show(string $genreId, Request $request, FanzaApiService $api)
    {
        $categories = config('fanza.categories');
        $categorySlug = $request->input('category', 'douga');

        if (!isset($categories[$categorySlug])) {
            $categorySlug = 'douga';
        }

        $category = $categories[$categorySlug];
        $page = max(1, (int) $request->input('page', 1));
        $sort = $request->input('sort', 'rank');
        $hits = 20;
        $offset = (($page - 1) * $hits) + 1;

        $result = $api->getItems([
            'service' => $category['service'],
            'floor' => $category['floor'],
            'hits' => $hits,
            'offset' => $offset,
            'sort' => $sort,
            'article' => 'genre',
            'article_id' => $genreId,
        ]);

        $items = $result['result']['items'] ?? [];
        $totalCount = $result['result']['total_count'] ?? 0;
        $totalPages = ceil($totalCount / $hits);

        $genreName = $request->input('name', 'ジャンル');

        return view('genre.show', [
            'genreId' => $genreId,
            'genreName' => $genreName,
            'categorySlug' => $categorySlug,
            'category' => $category,
            'categories' => $categories,
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'sort' => $sort,
        ]);
    }
}
