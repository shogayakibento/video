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

        $result = $api->getRanking($category['service'], $category['floor'], 30);
        $items = $result['result']['items'] ?? [];

        // Collect unique actress IDs from ranking items and fetch their photos
        $actressImageMap = [];
        $processedIds = [];
        foreach ($items as $item) {
            $actressInfo = $item['iteminfo']['actress'] ?? [];
            if (!empty($actressInfo)) {
                $id = $actressInfo[0]['id'] ?? null;
                if ($id && !isset($processedIds[$id])) {
                    $processedIds[$id] = true;
                    $actressResult = $api->getActresses(['actress_id' => $id, 'hits' => 1]);
                    $actressData = $actressResult['result']['actress'][0] ?? null;
                    if ($actressData) {
                        $actressImageMap[$id] = $actressData['imageURL']['large'] ?? $actressData['imageURL']['small'] ?? '';
                    }
                }
            }
        }

        return view('ranking.index', [
            'items' => $items,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'actressImageMap' => $actressImageMap,
        ]);
    }
}
