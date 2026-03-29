<?php

namespace App\Http\Controllers;

use App\Models\ClickLog;
use App\Models\Video;
use Illuminate\Http\Request;

class TweetVideoController extends Controller
{
    public function show(Video $video)
    {
        $video->load('tweets');

        // 協調フィルタリング: この動画をクリックした人が他にクリックした動画
        $coViewedIds = ClickLog::whereIn('ip_address', function ($q) use ($video) {
                $q->select('ip_address')
                  ->from('click_logs')
                  ->where('video_id', $video->id)
                  ->distinct();
            })
            ->where('video_id', '!=', $video->id)
            ->selectRaw('video_id, COUNT(DISTINCT ip_address) as co_views')
            ->groupBy('video_id')
            ->orderByDesc('co_views')
            ->limit(6)
            ->pluck('video_id');

        $alsoWatched = $coViewedIds->isNotEmpty()
            ? Video::whereIn('id', $coViewedIds)
                ->orderByRaw('FIELD(id, ' . $coViewedIds->implode(',') . ')')
                ->get()
            : collect();

        // フォールバック: クリックデータ不足の場合は女優・ジャンル一致で補完
        $base = Video::where('id', '!=', $video->id);

        if ($alsoWatched->count() < 6) {
            $excludeIds = $alsoWatched->pluck('id')->push($video->id);
            $fallback = (clone $base)
                ->whereNotIn('id', $excludeIds)
                ->where(function ($query) use ($video) {
                    if ($video->actress) {
                        $actresses = explode(',', $video->actress);
                        $query->where('actress', 'like', '%' . trim($actresses[0]) . '%');
                    }
                    if ($video->genre) {
                        $genres = explode(',', $video->genre);
                        $query->orWhere('genre', 'like', '%' . trim($genres[0]) . '%');
                    }
                })
                ->orderByDesc('total_likes')
                ->limit(6 - $alsoWatched->count())
                ->get();

            $alsoWatched = $alsoWatched->merge($fallback);
        }

        if ($alsoWatched->isEmpty()) {
            $alsoWatched = (clone $base)->orderByDesc('total_likes')->limit(6)->get();
        }

        return view('tweet-video.show', compact('video', 'alsoWatched'));
    }

    public function redirect(Video $video, Request $request)
    {
        ClickLog::create([
            'video_id' => $video->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ]);

        $video->increment('click_count');

        return redirect()->away($video->affiliate_url);
    }
}
