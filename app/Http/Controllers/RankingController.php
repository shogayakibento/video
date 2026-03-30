<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\Video;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $categories = config('fanza.categories');
        $category = $request->input('category', 'douga');

        if (!isset($categories[$category])) {
            $category = 'douga';
        }

        return redirect()->to(route('category.show', $category) . '?sort=rank', 301);
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
            $query->whereHas('tweets', fn($q) => $q->where('tweeted_at', '>=', now()->subWeek()))
                  ->withSum(['tweets as period_likes' => fn($q) => $q->where('tweeted_at', '>=', now()->subWeek())], 'like_count');
        } elseif ($period === 'monthly') {
            $query->whereHas('tweets', fn($q) => $q->where('tweeted_at', '>=', now()->subMonth()))
                  ->withSum(['tweets as period_likes' => fn($q) => $q->where('tweeted_at', '>=', now()->subMonth())], 'like_count');
        }

        if (!empty($genre)) {
            $query->where('genre', 'like', "%{$genre}%");
        }

        if ($period === 'all') {
            $videos = $query->orderByDesc('total_likes')->paginate(20);
        } else {
            $videos = $query->orderByDesc('period_likes')->paginate(20);
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
