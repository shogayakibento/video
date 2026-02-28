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

        $relatedVideos = Video::where('id', '!=', $video->id)
            ->where(function ($query) use ($video) {
                if ($video->actress) {
                    $actresses = explode(',', $video->actress);
                    $query->where('actress', 'like', '%' . trim($actresses[0]) . '%');
                }
            })
            ->orderByDesc('total_likes')
            ->limit(6)
            ->get();

        return view('tweet-video.show', compact('video', 'relatedVideos'));
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
