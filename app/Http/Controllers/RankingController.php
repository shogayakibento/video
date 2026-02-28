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
        $period = $request->get('period', 'all');
        $genre = $request->get('genre', '');

        $query = Video::query()->where('total_likes', '>', 0);

        if ($period === 'weekly') {
            $query->where('updated_at', '>=', now()->subWeek());
        } elseif ($period === 'monthly') {
            $query->where('updated_at', '>=', now()->subMonth());
        }

        if (!empty($genre)) {
            $query->where('genre', 'like', "%{$genre}%");
        }

        $videos = $query->orderByDesc('weekly_likes')->orderByDesc('total_likes')->paginate(20);

        $genres = Video::whereNotNull('genre')
            ->where('genre', '!=', '')
            ->pluck('genre')
            ->flatMap(fn($g) => explode(', ', $g))
            ->unique()
            ->sort()
            ->values();

        return view('tweet-ranking.index', compact('videos', 'period', 'genre', 'genres'));
    }

    public function tweetLatest()
    {
        $videos = Video::orderByDesc('release_date')
            ->orderByDesc('total_likes')
            ->paginate(20);

        return view('tweet-ranking.latest', compact('videos'));
    }

    public function tweetPopularTweets()
    {
        $tweets = Tweet::with('video')
            ->whereNotNull('tweet_url')
            ->orderByDesc('like_count')
            ->paginate(20);

        return view('tweet-ranking.popular-tweets', compact('tweets'));
    }
}
