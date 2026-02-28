@extends('layouts.tweet')

@section('title', 'Xで話題のツイート - FanzaGate')
@section('description', 'X(Twitter)でいいねが多かったFANZA関連ツイートを一覧表示。')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Xで話題のツイート</h1>
        <p class="text-gray-400 text-sm">いいねが多い順に表示しています</p>
    </div>

    @if($tweets->isEmpty())
        <div class="text-center py-20 text-gray-500">
            <p class="text-lg mb-2">まだツイートデータがありません</p>
            <p class="text-sm">管理画面からデータを登録すると表示されます</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($tweets as $tweet)
                <div class="bg-secondary rounded-lg p-4 border border-gray-700 hover:border-gray-500 transition">
                    <div class="flex gap-4">
                        @if($tweet->video)
                            <a href="{{ route('tweet.video.show', $tweet->video) }}" class="flex-shrink-0">
                                <img src="{{ $tweet->video->thumbnail_url }}"
                                     alt="{{ $tweet->video->title }}"
                                     class="w-28 h-20 object-cover rounded hover:opacity-80 transition"
                                     loading="lazy">
                            </a>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-300">
                                    @if($tweet->author_username)&#64;{{ $tweet->author_username }}@else匿名ユーザー@endif
                                </span>
                                @if($tweet->tweet_url)
                                    <a href="{{ $tweet->tweet_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-xs text-accent hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        ツイートを見る
                                    </a>
                                @endif
                            </div>

                            @if($tweet->tweet_text)
                                <p class="text-sm text-gray-300 mb-2 line-clamp-2">{{ $tweet->tweet_text }}</p>
                            @endif

                            <div class="flex items-center gap-4 mb-2">
                                <span class="flex items-center gap-1 text-accent text-sm font-bold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    {{ number_format($tweet->like_count) }}
                                </span>
                                <span class="flex items-center gap-1 text-gray-400 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                                    {{ number_format($tweet->retweet_count) }}
                                </span>
                            </div>

                            @if($tweet->video)
                                <div class="flex items-center gap-3 flex-wrap">
                                    <a href="{{ route('tweet.video.show', $tweet->video) }}"
                                       class="text-xs text-gray-400 hover:text-white transition line-clamp-1 max-w-xs">
                                        {{ $tweet->video->title }}
                                    </a>
                                    <a href="{{ route('tweet.video.redirect', $tweet->video) }}" target="_blank" rel="nofollow noopener"
                                       class="flex-shrink-0 bg-accent hover:bg-red-600 text-white text-xs font-bold px-3 py-1 rounded transition">
                                        FANZAで見る
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $tweets->links() }}</div>
    @endif
@endsection
