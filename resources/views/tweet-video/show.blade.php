@extends('layouts.app')

@section('title', $video->title . ' - FanzaGate')
@section('description', $video->title . 'のサンプル動画とレビュー。' . ($video->actress ? '出演: ' . $video->actress : ''))
@section('og_type', 'video.movie')
@section('og_image', $video->thumbnail_url)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="tweet-page">
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-accent text-sm transition">&larr; 戻る</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- メインコンテンツ --}}
        <div class="lg:col-span-2">
            {{-- サンプル動画 or サムネイル --}}
            <div class="bg-black rounded-lg overflow-hidden mb-4">
                @if($video->dmm_content_id)
                    <div class="relative" style="padding-top: 65%;">
                        <iframe
                            src="https://www.dmm.co.jp/litevideo/-/part/=/affi_id={{ config('dmm.affiliate_id') }}/cid={{ $video->dmm_content_id }}/size=1280_720/"
                            class="absolute left-0 right-0 w-full"
                            style="top: -15%; height: 125%;"
                            frameborder="0"
                            allowfullscreen
                            scrolling="no"
                        ></iframe>
                    </div>
                @else
                    <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full aspect-video object-cover">
                @endif
            </div>

            <h1 class="text-xl font-bold mb-3">{{ $video->title }}</h1>

            <div class="flex flex-wrap items-center gap-4 mb-4 text-sm text-gray-400">
                @if($video->actress)<span>出演: {{ $video->actress }}</span>@endif
                @if($video->maker)<span>メーカー: {{ $video->maker }}</span>@endif
                @if($video->release_date)<span>発売日: {{ $video->release_date->format('Y/m/d') }}</span>@endif
            </div>

            {{-- エンゲージメント --}}
            <div class="flex items-center gap-6 mb-6 py-3 border-t border-b border-gray-700">
                <div class="flex items-center gap-2 text-accent">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    <span class="font-bold">{{ number_format($video->total_likes) }}</span>
                    <span class="text-gray-400 text-sm">いいね</span>
                </div>
                <div class="flex items-center gap-2 text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                    <span class="font-bold">{{ number_format($video->total_retweets) }}</span>
                    <span class="text-gray-400 text-sm">RT</span>
                </div>
            </div>

            {{-- FANZAリンク --}}
            <a href="{{ route('tweet.video.redirect', $video) }}" target="_blank" rel="nofollow noopener"
               class="inline-block bg-accent hover:bg-red-600 text-white font-bold px-8 py-3 rounded-lg transition text-center">
                FANZAで詳細を見る
            </a>

            {{-- ジャンルタグ --}}
            @if($video->genre)
                <div class="mt-2 mb-6">
                    <h3 class="text-sm text-gray-400 mb-2">ジャンル</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(', ', $video->genre) as $g)
                            <a href="{{ route('tweet.ranking.index', ['genre' => $g]) }}"
                               class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-1 rounded-full transition">{{ $g }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 関連ツイート --}}
            @if($video->tweets->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-lg font-bold mb-3">関連ツイート</h3>
                    <div class="space-y-3">
                        @foreach($video->tweets->sortByDesc('like_count')->take(5) as $tweet)
                            <div class="bg-primary rounded-lg p-4">
                                <div class="mb-2">
                                    <span class="text-sm text-gray-400">&#64;{{ $tweet->author_username }}</span>
                                </div>
                                @if($tweet->tweet_text)
                                    <p class="text-sm text-gray-300 mb-2">{{ $tweet->tweet_text }}</p>
                                @endif
                                <div class="flex gap-4 text-xs text-gray-500">
                                    <span>{{ number_format($tweet->like_count) }} いいね</span>
                                    <span>{{ number_format($tweet->retweet_count) }} RT</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- サイドバー: 関連動画 --}}
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold mb-3">関連動画</h3>
            @if($relatedVideos->isEmpty())
                <p class="text-gray-500 text-sm">関連動画はありません</p>
            @else
                <div class="space-y-4">
                    @foreach($relatedVideos as $related)
                        <div class="flex gap-3 group">
                            <a href="{{ route('tweet.video.show', $related) }}" class="flex-shrink-0 w-32">
                                <img src="{{ $related->thumbnail_url }}" alt="{{ $related->title }}"
                                     class="w-full aspect-video object-cover rounded group-hover:opacity-80 transition" loading="lazy">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('tweet.video.show', $related) }}" class="text-sm line-clamp-2 hover:text-accent transition">
                                    {{ $related->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-1">{{ $related->actress }}</p>
                                <span class="text-xs text-accent">{{ number_format($related->total_likes) }} いいね</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "VideoObject",
    "name": "{{ addslashes($video->title) }}",
    "description": "{{ addslashes($video->title) }}のサンプル動画とレビュー。{{ $video->actress ? '出演: ' . addslashes($video->actress) : '' }}",
    "thumbnailUrl": "{{ $video->thumbnail_url }}",
    "uploadDate": "{{ $video->release_date ? $video->release_date->format('c') : $video->created_at->format('c') }}"@if($video->actress),
    "actor": {
        "@@type": "Person",
        "name": "{{ addslashes(explode(',', $video->actress)[0]) }}"
    }@endif
}
</script>
@endpush
