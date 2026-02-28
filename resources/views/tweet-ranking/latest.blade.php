@extends('layouts.app')

@section('title', '新着AV作品 - FanzaGate')
@section('description', '最近リリースされた話題のFANZA作品を新しい順に表示。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>新着AV作品</h1>
            <p>最近リリースされた作品を新しい順に表示</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'Xランキング', 'url' => route('tweet.ranking.index')],
            ['label' => '新着'],
        ]])

        <div class="filter-bar">
            <a href="{{ route('tweet.ranking.index') }}" class="tab-btn">ランキング</a>
            <a href="{{ route('tweet.ranking.popular-tweets') }}" class="tab-btn">話題のツイート</a>
            <a href="{{ route('tweet.ranking.latest') }}" class="tab-btn active">新着</a>
        </div>

        @if($videos->isEmpty())
            <div class="empty-state">
                <p>まだデータがありません</p>
            </div>
        @else
            <div class="grid-4col">
                @foreach($videos as $video)
                    @include('components.tweet-video-card', ['video' => $video])
                @endforeach
            </div>

            <div style="margin-top: 32px;">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
@endsection
