@extends('layouts.app')

@section('title', 'X(Twitter)バズりFANZA動画ランキング - FanzaGate')
@section('description', 'X(Twitter)でいいね数が多くバズったFANZA動画を毎日更新でランキング。今SNSで話題の人気AV作品をチェック！')
@if($period !== 'all' || !empty($genre))
@section('robots', 'noindex, follow')
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <h1>X(Twitter)バズり動画ランキング</h1>
        <p>いいね数が多くバズったFANZAサンプル動画を毎日更新</p>
    </div>
</div>

<div class="tweet-page" style="padding-top: 1.5rem;">
    <div class="mb-8">

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
                <a href="{{ route('tweet.ranking.index', ['period' => $period, 'genre' => '']) }}"
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
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "X(Twitter)バズりFANZA動画ランキング",
    "description": "X(Twitter)でいいね数が多くバズったFANZA動画のランキング",
    "numberOfItems": {{ $videos->count() }},
    "itemListElement": [
        @foreach($videos as $video)
        {
            "@@type": "ListItem",
            "position": {{ $videos->firstItem() + $loop->index }},
            "name": "{{ addslashes($video->title) }}",
            "url": "{{ route('tweet.video.show', $video->id) }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
@endpush
