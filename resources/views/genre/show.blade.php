@extends('layouts.app')

@section('title', $genre['label'] . 'の動画一覧 - FanzaGate')
@section('description', $genre['label'] . 'の人気FANZA動画を一覧でご紹介。' . $genre['label'] . 'ジャンルの最新作・人気ランキング・レビュー高評価作品を随時更新。お気に入りの' . $genre['label'] . '作品をランキング・新着順で探せます。FANZAの豊富な' . $genre['label'] . '作品ラインナップ。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>{{ $genre['label'] }}</h1>
            <p>{{ $genre['label'] }}の作品一覧</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'ジャンル', 'url' => route('genre.index')],
            ['label' => $genre['label']],
        ]])

        {{-- Sort --}}
        <div class="filter-bar">
            <a href="{{ route('genre.show', $slug) }}?sort=rank" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
            <a href="{{ route('genre.show', $slug) }}?sort=date" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('genre.show', $slug) }}?sort=review" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>

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
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif

        {{-- Back to genre list --}}
        <div class="back-link">
            <a href="{{ route('genre.index') }}">&larr; ジャンル一覧に戻る</a>
        </div>
    </div>
@endsection

@push('scripts')
@if(!empty($items) && count($items) > 0)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "{{ addslashes($genre['label']) }} 動画一覧",
    "description": "FANZAの{{ addslashes($genre['label']) }}ジャンル人気作品一覧",
    "numberOfItems": {{ count($items) }},
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
