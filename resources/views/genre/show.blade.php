@extends('layouts.app')

@section('title', $genreName . ' - FanzaGate')
@section('description', $genreName . 'の人気作品一覧。FANZAの' . $genreName . 'ジャンルの作品をランキング・新着順でチェック。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>{{ $genreName }}</h1>
            <p>{{ $category['label'] }} &gt; {{ $genreName }}の作品一覧</p>
        </div>
    </div>

    <div class="container">
        {{-- Sort --}}
        <div class="filter-bar">
            <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort=rank" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
            <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort=date" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort=review" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>

            <span class="filter-count">{{ number_format($totalCount) }}件</span>
        </div>

        {{-- Items Grid --}}
        <div class="items-grid content-grid">
            @forelse($items as $index => $item)
                @include('partials.item-card', ['item' => $item, 'rank' => $sort === 'rank' ? (($currentPage - 1) * 20) + $index + 1 : null])
            @empty
                <div class="empty-state">
                    <p>作品が見つかりませんでした。</p>
                </div>
            @endforelse
        </div>

        @include('partials.ad-inline', ['bannerId' => '1829_300_250'])

        {{-- Pagination --}}
        @if($totalPages > 1)
            <div class="pagination">
                @if($currentPage > 1)
                    <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort={{ $sort }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('genre.show', ['genreId' => $genreId, 'category' => $categorySlug, 'name' => $genreName]) }}&sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif

        {{-- Back to genre list --}}
        <div class="back-link">
            <a href="{{ route('genre.index', ['category' => $categorySlug]) }}">&larr; ジャンル一覧に戻る</a>
        </div>
    </div>
@endsection
