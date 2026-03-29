@extends('layouts.app')

@section('title', $genre['label'] . 'の動画一覧' . ($currentPage > 1 ? ' - ' . $currentPage . 'ページ目' : '') . ' - FanzaGate')
@section('description', $genre['label'] . 'の人気FANZA動画一覧。最新作・ランキング・レビュー高評価作品を随時更新。お気に入りの作品を人気順・新着順・レビュー順で探せます。')
@if($sort !== 'rank')
@section('robots', 'noindex, follow')
@endif

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
                @include('partials.item-card', ['item' => $item, 'rank' => $sort === 'rank' ? (($currentPage - 1) * 20) + $index + 1 : null, 'eager' => $index === 0 && $currentPage === 1])
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
                @if($currentPage > 2)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page=1" class="page-btn">最初へ</a>
                @endif
                @if($currentPage > 1)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ</a>
                    @if($currentPage < $totalPages - 1)
                        <a href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $totalPages }}" class="page-btn">最後へ</a>
                    @endif
                @endif
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('genre.index') }}" class="text-gray-400 hover:text-accent text-sm transition">&larr; ジャンル一覧に戻る</a>
        </div>
    </div>
@endsection

@push('head_links')
@if($currentPage > 1)
<link rel="prev" href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage - 1 }}">
@endif
@if($currentPage < $totalPages)
<link rel="next" href="{{ route('genre.show', $slug) }}?sort={{ $sort }}&page={{ $currentPage + 1 }}">
@endif
@endpush

@push('scripts')
@if(!empty($items) && count($items) > 0)
@php
    $genreSchema = [
        '@context'     => 'https://schema.org',
        '@type'        => 'ItemList',
        'name'         => $genre['label'] . ' 動画一覧',
        'description'  => 'FANZAの' . $genre['label'] . 'ジャンル人気作品一覧',
        'numberOfItems'=> count($items),
        'itemListElement' => array_map(fn($item, $index) => [
            '@type'   => 'ListItem',
            'position'=> ($currentPage - 1) * 20 + $index + 1,
            'name'    => $item['title'] ?? '',
            'url'     => $item['URL'] ?? '',
        ], $items, array_keys($items)),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($genreSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush
