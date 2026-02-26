@extends('layouts.app')

@section('title', 'ジャンルから探す - FanzaGate')
@section('description', 'FANZAの作品をジャンルから検索。女子校生・熟女・巨乳・人妻など人気ジャンルからお好みの作品を探せます。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>ジャンルから探す</h1>
            <p>人気ジャンルから作品を探そう</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'ジャンル'],
        ]])

        <div class="genre-grid">
            @foreach($genres as $slug => $genre)
                <a href="{{ route('genre.show', $slug) }}" class="genre-card animate-on-scroll">
                    <span class="genre-card-name">{{ $genre['label'] }}</span>
                </a>
            @endforeach
        </div>

        @include('partials.ad-inline', ['bannerId' => '1844_728_90'])
    </div>
@endsection
