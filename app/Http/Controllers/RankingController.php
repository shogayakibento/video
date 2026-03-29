<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\Video;
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

    public function tweetIndex(Request $request)
    {
        if ($request->has('period')) {
            $period = $request->get('period');
            if (in_array($period, ['all', 'weekly', 'monthly'])) {
                session(['tweet_ranking_period' => $period]);
            } else {
                $period = 'all';
            }
        } else {
            $period = session('tweet_ranking_period', 'all');
        }
        if ($request->has('genre')) {
            $genre = $request->get('genre', '');
            session(['tweet_ranking_genre' => $genre]);
        } else {
            $genre = session('tweet_ranking_genre', '');
        }

        $query = Video::query()->where('total_likes', '>', 0);

        if ($period === 'weekly') {
            $query->whereHas('tweets', fn($q) => $q->where('tweeted_at', '>=', now()->subWeek()));
        } elseif ($period === 'monthly') {
            $query->whereHas('tweets', fn($q) => $q->where('tweeted_at', '>=', now()->subMonth()));
        }

        if (!empty($genre)) {
            $query->where('genre', 'like', "%{$genre}%");
        }

        if ($period === 'all') {
            $videos = $query->orderByDesc('total_likes')->paginate(20);
        } else {
            $videos = $query->orderByDesc('weekly_likes')->orderByDesc('total_likes')->paginate(20);
        }

        $excludeGenres = ['単体作品', 'ハイビジョン', '独占配信', '4K', 'デジモ', 'ギリモザ'];

        $genres = Video::whereNotNull('genre')
            ->where('genre', '!=', '')
            ->pluck('genre')
            ->flatMap(fn($g) => explode(', ', $g))
            ->filter(fn($g) => !in_array($g, $excludeGenres))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->values();

        return view('tweet-ranking.index', compact('videos', 'period', 'genre', 'genres'));
    }

}
