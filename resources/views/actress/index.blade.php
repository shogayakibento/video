@extends('layouts.app')

@section('title', '女優から探す - FanzaGate')
@section('description', 'FANZA女優一覧。名前検索や五十音順でお気に入りの女優を見つけて、出演作品をチェックしよう。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>女優から探す</h1>
            <p>お気に入りの女優を見つけよう</p>
        </div>
    </div>

    <div class="container">
        {{-- Search --}}
        <form action="{{ route('actress.index') }}" method="GET" class="actress-search-form">
            <div class="search-box">
                <input type="text" name="keyword" value="{{ $keyword }}" placeholder="女優名で検索..." class="search-input">
                <button type="submit" class="search-submit-btn">検索</button>
            </div>
        </form>

        {{-- Initial Filter --}}
        <div class="filter-bar filter-bar-center filter-bar-wrap">
            <a href="{{ route('actress.index') }}" class="tab-btn {{ !$initial && !$keyword ? 'active' : '' }}">すべて</a>
            @foreach($initials as $kana => $label)
                <a href="{{ route('actress.index', ['initial' => $kana]) }}" class="tab-btn {{ $initial === $kana ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($keyword)
            <p class="search-result-info">「{{ $keyword }}」の検索結果: {{ number_format($totalCount) }}件</p>
        @endif

        {{-- Actress Grid --}}
        <div class="actress-grid">
            @forelse($actresses as $actress)
                @php
                    $imageUrl = $actress['imageURL']['large'] ?? $actress['imageURL']['small'] ?? '';
                    $actressId = $actress['id'] ?? '';
                    $name = $actress['name'] ?? '不明';
                    $ruby = $actress['ruby'] ?? '';
                @endphp
                <a href="{{ route('actress.show', $actressId) }}" class="actress-card animate-on-scroll">
                    <div class="actress-thumb">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy">
                        @else
                            <div class="actress-thumb-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="actress-info">
                        <span class="actress-name">{{ $name }}</span>
                        @if($ruby)
                            <span class="actress-ruby">{{ $ruby }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <p>女優が見つかりませんでした。</p>
                </div>
            @endforelse
        </div>

        @include('partials.ad-inline', ['bannerId' => '1701_300_250'])

        {{-- Pagination --}}
        @if($totalPages > 1)
            @php
                $paginationParams = [];
                if($keyword) $paginationParams['keyword'] = $keyword;
                if($initial) $paginationParams['initial'] = $initial;
            @endphp
            <div class="pagination">
                @if($currentPage > 1)
                    <a href="{{ route('actress.index', array_merge($paginationParams, ['page' => $currentPage - 1])) }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('actress.index', array_merge($paginationParams, ['page' => $i])) }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('actress.index', array_merge($paginationParams, ['page' => $currentPage + 1])) }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif
    </div>
@endsection
