<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;

class FanzaVideoController extends Controller
{
    // 試行するサービス・フロアの組み合わせ（優先順）
    private const FLOORS = [
        ['service' => 'digital', 'floor' => 'videoa'],
        ['service' => 'digital', 'floor' => 'videoc'],
        ['service' => 'mono',    'floor' => 'dvd'],
        ['service' => 'ebook',   'floor' => 'comic'],
    ];

    public function show(string $contentId, FanzaApiService $api)
    {
        $item        = null;
        $itemService = 'digital';
        $itemFloor   = 'videoa';

        foreach (self::FLOORS as $floor) {
            $result = $api->getItems(array_merge($floor, ['cid' => $contentId, 'hits' => 1]));
            $found  = $result['result']['items'][0] ?? null;
            if ($found) {
                $item        = $found;
                $itemService = $floor['service'];
                $itemFloor   = $floor['floor'];
                break;
            }
        }

        if (!$item) {
            abort(404);
        }

        // サイドバー: 同じ女優の他作品（同じサービス・フロアで取得）
        $primaryActressId = $item['iteminfo']['actress'][0]['id'] ?? null;
        $actressItems     = [];
        if ($primaryActressId) {
            $actressResult = $api->getItems([
                'service'    => $itemService,
                'floor'      => $itemFloor,
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

        // メイン下部: 同じジャンルで人気（同じサービス・フロアで取得）
        $primaryGenreId = $item['iteminfo']['genre'][0]['id'] ?? null;
        $alsoWatched    = [];
        if ($primaryGenreId) {
            $genreResult = $api->getItems([
                'service'    => $itemService,
                'floor'      => $itemFloor,
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
