@extends('layouts.app')

@section('title', 'Xで話題のツイート - FanzaGate')
@section('description', 'X(Twitter)でいいねが多かったFANZA関連ツイートを一覧表示。話題の投稿から気になる作品を見つけよう！')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Xで話題のツイート</h1>
            <p>いいねが多い順に表示しています</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'Xランキング', 'url' => route('tweet.ranking.index')],
            ['label' => '話題のツイート'],
        ]])

        <div class="filter-bar">
            <a href="{{ route('tweet.ranking.index') }}" class="tab-btn">ランキング</a>
            <a href="{{ route('tweet.ranking.popular-tweets') }}" class="tab-btn active">話題のツイート</a>
            <a href="{{ route('tweet.ranking.latest') }}" class="tab-btn">新着</a>
        </div>

        @if($tweets->isEmpty())
            <div class="empty-state">
                <p>まだツイートデータがありません</p>
                <p style="font-size: 14px; margin-top: 8px;">管理画面からデータを登録すると表示されます</p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($tweets as $tweet)
                    <div class="card" style="padding: 16px;">
                        <div style="display: flex; gap: 16px;">
                            {{-- 動画サムネイル --}}
                            @if($tweet->video)
                                <a href="{{ route('tweet.video.show', $tweet->video) }}" style="flex-shrink: 0;">
                                    <img src="{{ $tweet->video->thumbnail_url }}"
                                         alt="{{ $tweet->video->title }}"
                                         style="width: 112px; height: 80px; object-fit: cover; border-radius: 6px;"
                                         loading="lazy">
                                </a>
                            @endif

                            {{-- ツイート情報 --}}
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-size: 13px; color: var(--text-secondary);">
                                        @if($tweet->author_username)
                                            &#64;{{ $tweet->author_username }}
                                        @else
                                            匿名ユーザー
                                        @endif
                                    </span>
                                    @if($tweet->tweet_url)
                                        <a href="{{ $tweet->tweet_url }}" target="_blank" rel="noopener noreferrer"
                                           style="font-size: 12px; color: var(--accent); display: flex; align-items: center; gap: 4px;">
                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                            </svg>
                                            ツイートを見る
                                        </a>
                                    @endif
                                </div>

                                @if($tweet->tweet_text)
                                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $tweet->tweet_text }}</p>
                                @endif

                                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
                                    <span style="display: flex; align-items: center; gap: 4px; color: var(--accent); font-size: 14px; font-weight: bold;">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                        {{ number_format($tweet->like_count) }}
                                    </span>
                                    <span style="display: flex; align-items: center; gap: 4px; color: var(--text-secondary); font-size: 13px;">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                                        {{ number_format($tweet->retweet_count) }}
                                    </span>
                                </div>

                                @if($tweet->video)
                                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <a href="{{ route('tweet.video.show', $tweet->video) }}"
                                           style="font-size: 12px; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px;">
                                            {{ $tweet->video->title }}
                                        </a>
                                        <a href="{{ route('tweet.video.redirect', $tweet->video) }}" target="_blank" rel="nofollow noopener"
                                           class="btn-primary" style="font-size: 12px; padding: 4px 12px; flex-shrink: 0;">
                                            FANZAで見る
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 32px;">
                {{ $tweets->links() }}
            </div>
        @endif
    </div>
@endsection
