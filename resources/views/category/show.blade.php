@extends('layouts.app')

@section('title', $category['label'] . ' 人気ランキング・おすすめ作品一覧 - FanzaGate')
@section('description', $category['label'] . 'の人気FANZA作品一覧。' . $category['label'] . 'の人気ランキング・新着・レビュー高評価作品をまとめてチェック。毎日更新される最新' . $category['label'] . '情報をお届けします。FANZA公式の豊富な作品ラインナップから厳選。')

@section('content')
    {{-- Page Header --}}
    <div class="page-header">
        <div class="container">
            <h1>{{ $category['label'] }}</h1>
            <p>{{ $category['description'] }}</p>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => $category['label']],
        ]])
    </div>

    {{-- Sort & Filter --}}
    <div class="container">
        <div class="filter-bar">
            <a href="{{ route('category.show', $slug) }}?sort=rank" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
            <a href="{{ route('category.show', $slug) }}?sort=date" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('category.show', $slug) }}?sort=review" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>

            <span class="filter-count">{{ number_format($totalCount) }}件</span>
        </div>

        {{-- Items Grid --}}
        <div class="items-grid content-grid {{ $slug === 'vr' ? 'category-vr' : (in_array($slug, ['comic']) ? 'category-portrait' : ($slug === 'dvd' ? 'category-dvd' : '')) }}">
            @forelse($items as $index => $item)
                @include('partials.item-card', ['item' => $item, 'rank' => $sort === 'rank' ? (($currentPage - 1) * 20) + $index + 1 : null])
            @empty
                <div class="empty-state">
                    <p>作品が見つかりませんでした。</p>
                </div>
            @endforelse
        </div>

        @include('partials.ad-inline', ['bannerId' => '1844_728_90'])

        {{-- Pagination --}}
        @if($totalPages > 1)
            <div class="pagination">
                @if($currentPage > 1)
                    <a href="{{ route('category.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('category.show', $slug) }}?sort={{ $sort }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('category.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif
    </div>

    {{-- Other Categories --}}
    <section class="section categories-section">
        <div class="container">
            <h2 class="section-title">他のカテゴリ</h2>
            <div class="categories-grid">
                @foreach($categories as $catSlug => $cat)
                    @if($catSlug !== $slug)
                        <a href="{{ route('category.show', $catSlug) }}" class="category-card">
                            <div class="category-icon">
                                @include('partials.icon', ['icon' => $cat['icon']])
                            </div>
                            <h3>{{ $cat['label'] }}</h3>
                            <p>{{ $cat['description'] }}</p>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@if($items->isNotEmpty())
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "{{ addslashes($category['label']) }} 人気ランキング",
    "description": "FANZAの{{ addslashes($category['label']) }}人気作品一覧",
    "numberOfItems": {{ $items->count() }},
    "itemListElement": [
        @foreach($items as $index => $item)
        {
            "@@type": "ListItem",
            "position": {{ ($currentPage - 1) * 20 + $index + 1 }},
            "name": "{{ addslashes($item['title'] ?? '') }}",
            "url": "{{ $item['URL'] ?? '' }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
@endpush
