@extends('layouts.app')

@section('title', 'Xで話題のAVランキング - FanzaGate')
@section('description', 'X(Twitter)でいいねが多い話題のFANZA動画をランキング形式で紹介。今バズっているAV作品をチェック！')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>Xで話題のAVランキング</h1>
            <p>X(Twitter)でいいねが多かったFANZAサンプル動画をランキングで紹介</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'Xランキング'],
        ]])

        {{-- 期間フィルター --}}
        <div class="filter-bar">
            <a href="{{ route('tweet.ranking.index', ['period' => 'all', 'genre' => $genre]) }}"
               class="tab-btn {{ $period === 'all' ? 'active' : '' }}">全期間</a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'monthly', 'genre' => $genre]) }}"
               class="tab-btn {{ $period === 'monthly' ? 'active' : '' }}">月間</a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'weekly', 'genre' => $genre]) }}"
               class="tab-btn {{ $period === 'weekly' ? 'active' : '' }}">週間</a>
            <a href="{{ route('tweet.ranking.popular-tweets') }}"
               class="tab-btn {{ request()->routeIs('tweet.ranking.popular-tweets') ? 'active' : '' }}">話題のツイート</a>
            <a href="{{ route('tweet.ranking.latest') }}"
               class="tab-btn {{ request()->routeIs('tweet.ranking.latest') ? 'active' : '' }}">新着</a>
        </div>

        {{-- ジャンルフィルター --}}
        @if($genres->isNotEmpty())
            <div class="filter-bar" style="flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
                <a href="{{ route('tweet.ranking.index', ['period' => $period]) }}"
                   class="tab-btn {{ empty($genre) ? 'active' : '' }}">すべて</a>
                @foreach($genres->take(15) as $g)
                    <a href="{{ route('tweet.ranking.index', ['period' => $period, 'genre' => $g]) }}"
                       class="tab-btn {{ $genre === $g ? 'active' : '' }}" style="font-size: 12px; padding: 4px 12px;">
                        {{ $g }}
                    </a>
                @endforeach
            </div>
        @endif

        @if($videos->isEmpty())
            <div class="empty-state">
                <p>まだランキングデータがありません</p>
                <p style="font-size: 14px; margin-top: 8px;">管理画面からデータを登録すると表示されます</p>
            </div>
        @else
            <div class="grid-4col">
                @foreach($videos as $video)
                    @include('components.tweet-video-card', [
                        'video' => $video,
                        'rank' => $videos->firstItem() + $loop->index,
                    ])
                @endforeach
            </div>

            <div style="margin-top: 32px;">
                {{ $videos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
