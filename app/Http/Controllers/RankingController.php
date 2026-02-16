<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request, FanzaApiService $api)
    {
        $categories = config('fanza.categories');
        $activeCategory = $request->input('category', 'douga');

        if (!isset($categories[$activeCategory])) {
            $activeCategory = 'douga';
        }

        $category = $categories[$activeCategory];

        if ($api->isConfigured()) {
            $result = $api->getRanking($category['service'], $category['floor'], 30);
        } else {
            $result = $api->getSampleItems($activeCategory, 30);
        }

        $items = $result['result']['items'] ?? [];

        return view('ranking.index', [
            'items' => $items,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'isConfigured' => $api->isConfigured(),
        ]);
    }
}
