@extends('layouts.app')

@section('title', ($keyword ? $keyword . 'の検索結果' : '検索') . ' - FanzaGate')
@section('description', $keyword ? '「' . $keyword . '」のFANZA作品検索結果。動画・VR・DVD・コミックから' . $keyword . '関連作品を一覧表示。' : 'FANZAの作品を検索。動画、VR、DVD、コミックなど幅広いカテゴリから作品を探せます。')
@section('robots', 'noindex, follow')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>作品検索</h1>
            <form action="{{ route('search') }}" method="GET" class="search-box search-box-page">
                <input type="text" name="q" class="search-input" placeholder="作品名・キーワードで検索..." value="{{ $keyword }}">
                <button type="submit" class="search-btn">検索</button>
            </form>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => $keyword ? '「' . $keyword . '」の検索結果' : '検索'],
        ]])

        @if($keyword)
            <div class="filter-bar">
                <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort=rank" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
                <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort=date" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
                <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort=review" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>
                <span class="filter-count">{{ number_format($totalCount) }}件</span>
            </div>

            <div class="items-grid content-grid">
                @forelse($items as $item)
                    @include('partials.item-card', ['item' => $item, 'rank' => null])
                @empty
                    <div class="empty-state">
                        <p>「{{ $keyword }}」に一致する作品が見つかりませんでした。</p>
                        <p>別のキーワードで検索してみてください。</p>
                    </div>
                @endforelse
            </div>

            @include('partials.ad-inline', ['bannerId' => '1829_300_250'])

            @if($totalPages > 1)
                <div class="pagination">
                    @if($currentPage > 2)
                        <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort={{ $sort }}&page=1" class="page-btn">最初へ</a>
                    @endif
                    @if($currentPage > 1)
                        <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">前へ</a>
                    @endif

                    @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                        <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort={{ $sort }}&page={{ $i }}"
                           class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                    @endfor

                    @if($currentPage < $totalPages)
                        <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ</a>
                        @if($currentPage < $totalPages - 1)
                            <a href="{{ route('search') }}?q={{ urlencode($keyword) }}&sort={{ $sort }}&page={{ $totalPages }}" class="page-btn">最後へ</a>
                        @endif
                    @endif
                </div>
            @endif
        @else
            <div class="search-suggestions">
                <h2>人気のカテゴリ</h2>
                <div class="categories-grid">
                    @foreach($categories as $slug => $cat)
                        <a href="{{ route('category.show', $slug) }}" class="category-card">
                            <div class="category-icon">
                                @include('partials.icon', ['icon' => $cat['icon']])
                            </div>
                            <h3>{{ $cat['label'] }}</h3>
                            <p>{{ $cat['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
