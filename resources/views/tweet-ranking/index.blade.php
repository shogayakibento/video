@extends('layouts.app')

@section('title', 'X(Twitter)バズりFANZA動画ランキング - FanzaGate')
@section('description', 'X(Twitter)でいいね数が多くバズったFANZA動画を毎日更新でランキング。今SNSで話題の人気AV作品をチェック！')
@if($period !== 'all' || !empty($genre) || request()->integer('page', 1) > 1)
@section('robots', 'noindex, follow')
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="tweet-page">
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-3 page-title">X(Twitter)バズりFANZA動画ランキング</h1>
        <p class="text-sm mb-6" style="color: #6a6a8a;">X(Twitter)でいいね数が多くバズったFANZAサンプル動画を毎日更新でランキング</p>

        {{-- 期間フィルター --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('tweet.ranking.index', ['period' => 'all', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm font-medium transition {{ $period === 'all' ? 'filter-pill-active' : 'filter-pill' }}">
                全期間
            </a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'monthly', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm font-medium transition {{ $period === 'monthly' ? 'filter-pill-active' : 'filter-pill' }}">
                月間
            </a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'weekly', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm font-medium transition {{ $period === 'weekly' ? 'filter-pill-active' : 'filter-pill' }}">
                週間
            </a>
        </div>

        {{-- ジャンルフィルター --}}
        @if($genres->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('tweet.ranking.index', ['period' => $period]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium transition {{ empty($genre) ? 'filter-pill-active' : 'filter-pill' }}">
                    すべて
                </a>
                @foreach($genres->take(15) as $g)
                    <a href="{{ route('tweet.ranking.index', ['period' => $period, 'genre' => $g]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-medium transition {{ $genre === $g ? 'filter-pill-active' : 'filter-pill' }}">
                        {{ $g }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($videos->isEmpty())
        <div class="text-center py-24" style="color: #4a4a6a;">
            <p class="text-lg mb-2">まだランキングデータがありません</p>
            <p class="text-sm opacity-70">データが取り込まれるとここにランキングが表示されます</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($videos as $video)
                @include('components.tweet-video-card', [
                    'video' => $video,
                    'rank' => $videos->firstItem() + $loop->index,
                ])
            @endforeach
        </div>

        <div class="mt-10">
            {{ $videos->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($videos->isNotEmpty())
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'X(Twitter)バズりFANZA動画ランキング',
        'description' => 'X(Twitter)でいいね数が多くバズったFANZA動画のランキング',
        'numberOfItems' => $videos->count(),
        'itemListElement' => $videos->values()->map(fn($video, $index) => [
            '@type' => 'ListItem',
            'position' => $videos->firstItem() + $index,
            'name' => addslashes($video->title),
            'url' => route('tweet.video.show', $video->id),
        ])->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
@endpush
