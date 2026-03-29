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

        $base = Video::where('id', '!=', $video->id);

        $relatedVideos = (clone $base)
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
            ->limit(6)
            ->get();

        if ($relatedVideos->isEmpty()) {
            $relatedVideos = (clone $base)
                ->orderByDesc('total_likes')
                ->limit(6)
                ->get();
        }

        // 「〇〇好きな人におすすめ」: ジャンル共通度スコアで他の女優の動画をレコメンド
        $recommendedVideos = collect();
        $actressLabel = null;
        if ($video->actress && $video->genre) {
            $firstActress = trim(explode(',', $video->actress)[0]);
            $actressLabel = $firstActress;
            $genres = array_map('trim', explode(',', $video->genre));

            // 同女優を除き、ジャンルが2つ以上一致する動画を人気順で取得
            $candidates = (clone $base)
                ->where('actress', 'not like', '%' . $firstActress . '%')
                ->whereNotNull('genre')
                ->orderByDesc('total_likes')
                ->limit(200)
                ->get();

            $recommendedVideos = $candidates
                ->map(function ($v) use ($genres) {
                    $vGenres = array_map('trim', explode(',', $v->genre ?? ''));
                    $overlap = count(array_intersect($genres, $vGenres));
                    $v->_overlap = $overlap;
                    return $v;
                })
                ->filter(fn($v) => $v->_overlap >= 2)
                ->sortByDesc('_overlap')
                ->take(6)
                ->values();

            // 2件未満なら1ジャンル一致に緩和
            if ($recommendedVideos->count() < 3) {
                $recommendedVideos = $candidates
                    ->map(function ($v) use ($genres) {
                        $vGenres = array_map('trim', explode(',', $v->genre ?? ''));
                        $v->_overlap = count(array_intersect($genres, $vGenres));
                        return $v;
                    })
                    ->filter(fn($v) => $v->_overlap >= 1)
                    ->sortByDesc('_overlap')
                    ->take(6)
                    ->values();
            }
        }

        return view('tweet-video.show', compact('video', 'relatedVideos', 'recommendedVideos', 'actressLabel'));
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
