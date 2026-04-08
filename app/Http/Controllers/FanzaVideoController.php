<?php

namespace App\Http\Controllers;

use App\Models\FanzaViewLog;
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

        // 閲覧ログを記録（同一セッションの重複は無視）
        FanzaViewLog::firstOrCreate([
            'content_id' => $contentId,
            'session_id' => session()->getId(),
        ]);

        // サイドバー: 同じ女優の他作品（女優なしの場合はジャンルで代替）
        $primaryActressId = $item['iteminfo']['actress'][0]['id'] ?? null;
        $actressItems     = [];
        $sidebarByGenre   = false;

        if ($primaryActressId) {
            $actressResult = $api->getItems([
                'service'    => 'digital',
                'floor'      => 'videoa',
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

        if (empty($actressItems)) {
            $sidebarByGenre = true;
            $popularResult = $api->getItems([
                'service' => $itemService,
                'floor'   => $itemFloor,
                'hits'    => 7,
                'sort'    => 'rank',
            ]);
            $actressItems = array_values(array_filter(
                $popularResult['result']['items'] ?? [],
                fn($i) => ($i['content_id'] ?? '') !== $contentId
            ));
            $actressItems = array_slice($actressItems, 0, 6);
        }

        // この作品を見た人はこちらも視聴しています（協調フィルタリング）
        $alsoWatched = $this->getAlsoWatched($contentId, $itemService, $itemFloor, $item, $api);

        return view('video.show', compact('item', 'actressItems', 'alsoWatched', 'sidebarByGenre'));
    }

    private function getAlsoWatched(string $contentId, string $service, string $floor, array $item, FanzaApiService $api): array
    {
        $alsoWatched = [];

        // 同じセッションで一緒に見られた content_id を共起回数順で取得
        $sessionIds = FanzaViewLog::where('content_id', $contentId)
            ->pluck('session_id');

        if ($sessionIds->isNotEmpty()) {
            $coViewedIds = FanzaViewLog::whereIn('session_id', $sessionIds)
                ->where('content_id', '!=', $contentId)
                ->selectRaw('content_id, COUNT(*) as cnt')
                ->groupBy('content_id')
                ->orderByDesc('cnt')
                ->limit(12)
                ->pluck('content_id');

            foreach ($coViewedIds as $cid) {
                $result = $api->getItems(['service' => $service, 'floor' => $floor, 'cid' => $cid, 'hits' => 1]);
                $found  = $result['result']['items'][0] ?? null;
                if ($found) {
                    $alsoWatched[] = $found;
                }
                if (count($alsoWatched) >= 6) break;
            }
        }

        // 足りない場合はジャンルベースで補完
        if (count($alsoWatched) < 6) {
            $primaryGenreId  = $item['iteminfo']['genre'][0]['id'] ?? null;
            $existingIds     = array_merge([$contentId], array_column(array_column($alsoWatched, null), 'content_id'));

            if ($primaryGenreId) {
                $genreResult = $api->getItems([
                    'service'    => $service,
                    'floor'      => $floor,
                    'article'    => 'genre',
                    'article_id' => $primaryGenreId,
                    'hits'       => 12,
                    'sort'       => 'rank',
                ]);
                $genreItems = array_values(array_filter(
                    $genreResult['result']['items'] ?? [],
                    fn($i) => !in_array($i['content_id'] ?? '', $existingIds)
                ));
                $alsoWatched = array_slice(
                    array_merge($alsoWatched, $genreItems),
                    0,
                    6
                );
            }
        }

        return $alsoWatched;
    }
}
