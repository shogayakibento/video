@extends('layouts.tweet')

@section('title', '新着AV作品 - FanzaGate')
@section('description', '最近リリースされた話題のFANZA作品を新しい順に表示。')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">新着AV作品</h1>
        <p class="text-gray-400 text-sm">最近リリースされた作品を新しい順に表示</p>
    </div>

    @if($videos->isEmpty())
        <div class="text-center py-20 text-gray-500">
            <p class="text-lg mb-2">まだデータがありません</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($videos as $video)
                @include('components.tweet-video-card', ['video' => $video])
            @endforeach
        </div>
        <div class="mt-8">{{ $videos->links() }}</div>
    @endif
@endsection
