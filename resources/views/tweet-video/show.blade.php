@extends('layouts.app')

@section('title', $video->title . ' - FanzaGate Xランキング')
@section('description', $video->title . 'のサンプル動画とレビュー。' . ($video->actress ? '出演: ' . $video->actress : ''))

@section('content')
    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'Xランキング', 'url' => route('tweet.ranking.index')],
            ['label' => $video->title],
        ]])

        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
            @if(request()->has('back'))
                <div>
                    <a href="{{ url()->previous() }}" style="color: var(--text-secondary); font-size: 14px;">&larr; 戻る</a>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                {{-- メインコンテンツ --}}
                <div>
                    {{-- サンプル動画 or サムネイル --}}
                    <div style="background: #000; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
                        @if($video->sample_video_url)
                            <video
                                controls
                                preload="metadata"
                                poster="{{ $video->thumbnail_url }}"
                                style="width: 100%; aspect-ratio: 16/9; display: block;"
                            >
                                <source src="{{ $video->sample_video_url }}" type="video/mp4">
                                お使いのブラウザは動画再生に対応していません。
                            </video>
                        @else
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}"
                                 style="width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block;">
                        @endif
                    </div>

                    <h1 style="font-size: 20px; font-weight: bold; margin-bottom: 12px;">{{ $video->title }}</h1>

                    <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; font-size: 14px; color: var(--text-secondary);">
                        @if($video->actress)
                            <span>出演: {{ $video->actress }}</span>
                        @endif
                        @if($video->maker)
                            <span>メーカー: {{ $video->maker }}</span>
                        @endif
                        @if($video->release_date)
                            <span>発売日: {{ $video->release_date->format('Y/m/d') }}</span>
                        @endif
                    </div>

                    {{-- エンゲージメント --}}
                    <div style="display: flex; align-items: center; gap: 24px; padding: 12px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 8px; color: var(--accent);">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            <span style="font-weight: bold; font-size: 18px;">{{ number_format($video->total_likes) }}</span>
                            <span style="color: var(--text-secondary); font-size: 13px;">いいね</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary);">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                            <span style="font-weight: bold;">{{ number_format($video->total_retweets) }}</span>
                            <span style="font-size: 13px;">RT</span>
                        </div>
                    </div>

                    {{-- FANZAリンク --}}
                    <a href="{{ route('tweet.video.redirect', $video) }}" target="_blank" rel="nofollow noopener"
                       class="btn-primary" style="display: inline-block; padding: 12px 32px; font-size: 16px; border-radius: 8px; margin-bottom: 24px;">
                        FANZAで詳細を見る
                    </a>

                    {{-- ジャンルタグ --}}
                    @if($video->genre)
                        <div style="margin-bottom: 24px;">
                            <h3 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 8px;">ジャンル</h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach(explode(', ', $video->genre) as $g)
                                    <a href="{{ route('tweet.ranking.index', ['genre' => $g]) }}"
                                       class="genre-tag">{{ $g }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 関連ツイート --}}
                    @if($video->tweets->isNotEmpty())
                        <div style="margin-top: 24px;">
                            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px;">関連ツイート</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @foreach($video->tweets->sortByDesc('like_count')->take(5) as $tweet)
                                    <div class="card" style="padding: 16px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                            <span style="font-size: 13px; color: var(--text-secondary);">&#64;{{ $tweet->author_username }}</span>
                                            @if($tweet->tweet_url)
                                                <a href="{{ $tweet->tweet_url }}" target="_blank" rel="noopener"
                                                   style="color: var(--accent); font-size: 12px;">ツイートを見る</a>
                                            @endif
                                        </div>
                                        @if($tweet->tweet_text)
                                            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">{{ $tweet->tweet_text }}</p>
                                        @endif
                                        <div style="display: flex; gap: 16px; font-size: 12px; color: var(--text-secondary);">
                                            <span>{{ number_format($tweet->like_count) }} いいね</span>
                                            <span>{{ number_format($tweet->retweet_count) }} RT</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 関連動画 --}}
                @if($relatedVideos->isNotEmpty())
                    <div>
                        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px;">関連動画</h3>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            @foreach($relatedVideos as $related)
                                <div style="display: flex; gap: 12px;">
                                    <a href="{{ route('tweet.video.show', $related) }}" style="flex-shrink: 0; width: 120px;">
                                        <img src="{{ $related->thumbnail_url }}" alt="{{ $related->title }}"
                                             style="width: 100%; aspect-ratio: 16/10; object-fit: cover; border-radius: 6px;"
                                             loading="lazy">
                                    </a>
                                    <div style="flex: 1; min-width: 0;">
                                        <a href="{{ route('tweet.video.show', $related) }}"
                                           style="font-size: 13px; font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $related->title }}
                                        </a>
                                        <p style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">{{ $related->actress }}</p>
                                        <span style="font-size: 12px; color: var(--accent);">{{ number_format($related->total_likes) }} いいね</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
