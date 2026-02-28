@extends('layouts.tweet')

@section('title', 'Xで話題のAVランキング - FanzaGate')
@section('description', 'X(Twitter)でいいねが多い話題のFANZA動画をランキング形式で紹介。今バズっているAV作品をチェック！')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Xで話題のAVランキング</h1>
        <p class="text-gray-400 text-sm mb-6">X(Twitter)でいいねが多かったFANZAサンプル動画をランキングで紹介</p>

        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('tweet.ranking.index', ['period' => 'all', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm transition {{ $period === 'all' ? 'bg-accent text-white' : 'bg-secondary text-gray-300 hover:bg-gray-600' }}">全期間</a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'monthly', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm transition {{ $period === 'monthly' ? 'bg-accent text-white' : 'bg-secondary text-gray-300 hover:bg-gray-600' }}">月間</a>
            <a href="{{ route('tweet.ranking.index', ['period' => 'weekly', 'genre' => $genre]) }}"
               class="px-4 py-2 rounded-full text-sm transition {{ $period === 'weekly' ? 'bg-accent text-white' : 'bg-secondary text-gray-300 hover:bg-gray-600' }}">週間</a>
        </div>

        @if($genres->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('tweet.ranking.index', ['period' => $period]) }}"
                   class="px-3 py-1 rounded-full text-xs transition {{ empty($genre) ? 'bg-accent text-white' : 'bg-secondary text-gray-300 hover:bg-gray-600' }}">すべて</a>
                @foreach($genres->take(15) as $g)
                    <a href="{{ route('tweet.ranking.index', ['period' => $period, 'genre' => $g]) }}"
                       class="px-3 py-1 rounded-full text-xs transition {{ $genre === $g ? 'bg-accent text-white' : 'bg-secondary text-gray-300 hover:bg-gray-600' }}">{{ $g }}</a>
                @endforeach
            </div>
        @endif
    </div>

    @if($videos->isEmpty())
        <div class="text-center py-20 text-gray-500">
            <p class="text-lg mb-2">まだランキングデータがありません</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($videos as $video)
                @include('components.tweet-video-card', ['video' => $video, 'rank' => $videos->firstItem() + $loop->index])
            @endforeach
        </div>
        <div class="mt-8">{{ $videos->appends(request()->query())->links() }}</div>
    @endif
@endsection
