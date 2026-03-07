<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;

class ShortsController extends Controller
{
    public function __invoke(FanzaApiService $api)
    {
        $ranking = $api->getRanking('digital', 'videoa', 30);
        $all = $ranking['result']['items'] ?? [];

        $items = array_values(array_filter($all, fn($item) => !empty($item['sampleMovieURL']) && !empty($item['content_id'])));

        return view('shorts.index', [
            'items' => $items,
        ]);
    }
}
