<?php

namespace App\Http\Controllers;

use App\Models\ClickLog;
use App\Models\Tweet;
use App\Models\Video;
use App\Services\DmmApiService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate(['password' => 'required']);

        $adminPassword = config('app.admin_password');

        if (!$adminPassword) {
            return back()->withErrors(['password' => 'ADMIN_PASSWORD が .env に設定されていません。']);
        }

        if ($request->password === $adminPassword) {
            $request->session()->put('admin_authenticated', true);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['password' => 'パスワードが違います。']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $totalVideos = Video::count();
        $totalTweets = Tweet::count();
        $totalClicks = ClickLog::count();
        $todayClicks = ClickLog::whereDate('created_at', today())->count();

        $topVideos = Video::orderByDesc('total_likes')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalVideos', 'totalTweets', 'totalClicks', 'todayClicks', 'topVideos'
        ));
    }

    public function videos(Request $request)
    {
        $query = Video::withCount('tweets');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('actress', 'like', "%{$search}%")
                  ->orWhere('dmm_content_id', 'like', "%{$search}%");
            });
        }

        $videos = $query->orderByDesc('total_likes')->paginate(20);

        return view('admin.videos', compact('videos'));
    }

    public function updateLikes(Request $request, Video $video)
    {
        $request->validate([
            'total_likes' => 'required|integer|min:0',
            'total_retweets' => 'nullable|integer|min:0',
        ]);

        $video->update([
            'total_likes' => (int) $request->input('total_likes'),
            'total_retweets' => (int) $request->input('total_retweets', 0),
        ]);

        return back()->with('success', "「{$video->title}」のいいね数を更新しました。");
    }

    public function tweetForm(Video $video)
    {
        $video->load('tweets');
        return view('admin.tweet-form', compact('video'));
    }

    public function storeTweet(Request $request, Video $video)
    {
        $request->validate([
            'like_count' => 'required|integer|min:0',
            'retweet_count' => 'nullable|integer|min:0',
            'tweet_url' => 'nullable|url',
            'author_username' => 'nullable|string|max:255',
            'tweet_text' => 'nullable|string|max:1000',
        ]);

        $tweetUrl = $request->input('tweet_url');
        $tweetId = null;

        if ($tweetUrl) {
            preg_match('/status\/(\d+)/', $tweetUrl, $matches);
            $tweetId = $matches[1] ?? md5($tweetUrl);
        } else {
            $tweetId = 'manual_' . $video->id . '_' . time();
            $tweetUrl = '';
        }

        Tweet::updateOrCreate(
            ['tweet_id' => $tweetId],
            [
                'video_id' => $video->id,
                'tweet_url' => $tweetUrl,
                'tweet_text' => $request->input('tweet_text'),
                'author_username' => $request->input('author_username'),
                'like_count' => (int) $request->input('like_count'),
                'retweet_count' => (int) $request->input('retweet_count', 0),
                'tweeted_at' => now(),
            ]
        );

        $video->recalculateEngagement();

        return redirect()->route('admin.tweet.form', $video)
            ->with('success', "登録しました！ 合計いいね: {$video->fresh()->total_likes}");
    }

    public function deleteTweet(Tweet $tweet)
    {
        $video = $tweet->video;
        $tweet->delete();
        $video->recalculateEngagement();

        return redirect()->route('admin.tweet.form', $video)
            ->with('success', '削除しました。');
    }

    public function quickAdd()
    {
        return view('admin.quick-add');
    }

    public function quickAddStore(Request $request, DmmApiService $dmmApi)
    {
        $request->validate([
            'dmm_content_id' => 'required|string',
            'like_count' => 'required|integer|min:0',
            'retweet_count' => 'nullable|integer|min:0',
            'tweet_url' => 'nullable|url',
        ]);

        $video = Video::where('dmm_content_id', $request->input('dmm_content_id'))->first();

        if (!$video && $dmmApi->isConfigured()) {
            $item = $dmmApi->fetchByContentId($request->input('dmm_content_id'));

            if ($item) {
                $actresses = collect($item['iteminfo']['actress'] ?? [])->pluck('name')->implode(', ');
                $genres = collect($item['iteminfo']['genre'] ?? [])->pluck('name')->implode(', ');
                $maker = collect($item['iteminfo']['maker'] ?? [])->pluck('name')->first() ?? '';
                $thumbnailUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? '';
                $sampleVideoUrl = $item['sampleMovieURL']['size_720_480'] ?? $item['sampleMovieURL']['size_476_306'] ?? '';

                $video = Video::create([
                    'dmm_content_id' => $item['content_id'],
                    'title' => $item['title'] ?? '',
                    'actress' => $actresses,
                    'thumbnail_url' => $thumbnailUrl,
                    'sample_video_url' => $sampleVideoUrl,
                    'affiliate_url' => $item['affiliateURL'] ?? $item['URL'] ?? '',
                    'genre' => $genres,
                    'maker' => $maker,
                    'release_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : null,
                ]);
            }
        }

        if (!$video) {
            return back()->withInput()->withErrors([
                'dmm_content_id' => "品番「{$request->input('dmm_content_id')}」の動画が見つかりません。先に動画を取得してください。"
            ]);
        }

        $tweetUrl = $request->input('tweet_url');

        if ($tweetUrl) {
            preg_match('/status\/(\d+)/', $tweetUrl, $matches);
            $tweetId = $matches[1] ?? md5($tweetUrl);

            Tweet::updateOrCreate(
                ['tweet_id' => $tweetId],
                [
                    'video_id' => $video->id,
                    'tweet_url' => $tweetUrl,
                    'like_count' => (int) $request->input('like_count'),
                    'retweet_count' => (int) $request->input('retweet_count', 0),
                    'tweeted_at' => now(),
                ]
            );
            $video->recalculateEngagement();
        } else {
            $video->update([
                'total_likes' => (int) $request->input('like_count'),
                'total_retweets' => (int) $request->input('retweet_count', 0),
                'weekly_likes' => (int) $request->input('like_count'),
            ]);
        }

        $video->refresh();

        return back()->with('success', "「{$video->title}」を更新しました！ (いいね: {$video->total_likes})");
    }
}
