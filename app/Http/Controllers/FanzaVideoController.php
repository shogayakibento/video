<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;

class FanzaVideoController extends Controller
{
    public function show(string $contentId, FanzaApiService $api)
    {
        $result = $api->getItems(['cid' => $contentId, 'hits' => 1]);
        $item   = $result['result']['items'][0] ?? null;

        if (!$item) {
            abort(404);
        }

        // サイドバー: 同じ女優の他作品
        $primaryActressId = $item['iteminfo']['actress'][0]['id'] ?? null;
        $actressItems     = [];
        if ($primaryActressId) {
            $actressResult = $api->getItems([
                'article'    => 'actress',
                'article_id' => $primaryActressId,
                'hits'       => 7,
                'sort'       => 'rank',
            ]);
            $actressItems = array_values(array_filter(
                $actressResult['result']['items'] ?? [],
                fn($i) => ($i['content_id'] ?? '') !== $contentId
            ));
            $actressItems = array_slice($actressItems, 0, 6);
        }

        // メイン下部: 同じジャンルで人気の作品（「見た人はこちらも」の代替）
        $primaryGenreId = $item['iteminfo']['genre'][0]['id'] ?? null;
        $alsoWatched    = [];
        if ($primaryGenreId) {
            $genreResult = $api->getItems([
                'article'    => 'genre',
                'article_id' => $primaryGenreId,
                'hits'       => 7,
                'sort'       => 'rank',
            ]);
            $alsoWatched = array_values(array_filter(
                $genreResult['result']['items'] ?? [],
                fn($i) => ($i['content_id'] ?? '') !== $contentId
            ));
            $alsoWatched = array_slice($alsoWatched, 0, 6);
        }

        return view('video.show', compact('item', 'actressItems', 'alsoWatched'));
    }
}
