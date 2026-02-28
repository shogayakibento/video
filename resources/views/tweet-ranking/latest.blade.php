@extends('layouts.app')

@section('title', '新着AV作品 - FanzaGate')
@section('description', '最近リリースされた話題のFANZA作品を新しい順に表示。')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="tweet-page">
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-3 page-title">新着AV作品</h1>
        <p class="text-sm" style="color: #6a6a8a;">最近リリースされた作品を新しい順に表示</p>
    </div>

    @if($videos->isEmpty())
        <div class="text-center py-24" style="color: #4a4a6a;">
            <p class="text-lg mb-2">まだデータがありません</p>
            <p class="text-sm opacity-70">データが取り込まれるとここに作品が表示されます</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($videos as $video)
                @include('components.tweet-video-card', ['video' => $video])
            @endforeach
        </div>
        <div class="mt-10">{{ $videos->links() }}</div>
    @endif
</div>
@endsection
