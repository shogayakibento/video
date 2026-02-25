@extends('layouts.app')

@section('title', 'ジャンルから探す - FanzaGate')
@section('description', 'FANZAの作品をジャンルから検索。動画・VR・DVD・コミックのジャンル一覧からお好みの作品を探せます。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>ジャンルから探す</h1>
            <p>お好みのジャンルを選んで作品を探そう</p>
        </div>
    </div>

    <div class="container">
        {{-- Category Tabs --}}
        <div class="filter-bar filter-bar-center">
            @foreach($categories as $slug => $cat)
                <a href="{{ route('genre.index', ['category' => $slug]) }}"
                   class="tab-btn {{ $activeCategory === $slug ? 'active' : '' }}">
                    {{ $cat['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Genre Grid --}}
        <div class="genre-grid">
            @forelse($genres as $genre)
                <a href="{{ route('genre.show', ['genreId' => $genre['genre_id'], 'category' => $activeCategory, 'name' => $genre['name']]) }}"
                   class="genre-card animate-on-scroll">
                    <span class="genre-card-name">{{ $genre['name'] }}</span>
                </a>
            @empty
                <div class="empty-state">
                    <p>ジャンルデータを取得できませんでした。</p>
                </div>
            @endforelse
        </div>

        @include('partials.ad-inline', ['bannerId' => '1701_300_250'])
    </div>
@endsection
