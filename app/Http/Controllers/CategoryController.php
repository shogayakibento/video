<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(string $slug, Request $request, FanzaApiService $api)
    {
        $categories = config('fanza.categories');

        if (!isset($categories[$slug])) {
            abort(404);
        }

        $category = $categories[$slug];
        $page = max(1, (int) $request->input('page', 1));
        $sort = $request->input('sort', 'rank');
        $hits = 20;
        $offset = (($page - 1) * $hits) + 1;

        if ($api->isConfigured()) {
            $result = $api->getItems([
                'service' => $category['service'],
                'floor' => $category['floor'],
                'hits' => $hits,
                'offset' => $offset,
                'sort' => $sort,
            ]);
        } else {
            $result = $api->getSampleItems($slug, $hits);
        }

        $items = $result['result']['items'] ?? [];
        $totalCount = $result['result']['total_count'] ?? 0;
        $totalPages = ceil($totalCount / $hits);

        return view('category.show', [
            'category' => $category,
            'slug' => $slug,
            'categories' => $categories,
            'items' => $items,
            'currentPage' => $page,
            'totalPages' => min($totalPages, 50),
            'totalCount' => $totalCount,
            'sort' => $sort,
            'isConfigured' => $api->isConfigured(),
        ]);
    }
}
