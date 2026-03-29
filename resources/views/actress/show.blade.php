@extends('layouts.app')

@php
    $name = $actress['name'] ?? '不明';
    $ruby = $actress['ruby'] ?? '';
    $imageUrl = str_replace('http://', 'https://', $actress['imageURL']['large'] ?? $actress['imageURL']['small'] ?? '');
    $bust = $actress['bust'] ?? null;
    $cup = $actress['cup'] ?? null;
    $waist = $actress['waist'] ?? null;
    $hip = $actress['hip'] ?? null;
    $height = $actress['height'] ?? null;
    $birthday = $actress['birthday'] ?? null;
    $actressId = $actress['id'] ?? '';
@endphp

@section('title', $name . 'の動画一覧' . ($currentPage > 1 ? ' - ' . $currentPage . 'ページ目' : '') . ' - FanzaGate')
@section('description', $name . ($ruby ? '（' . $ruby . '）' : '') . 'の出演FANZA動画一覧。' . ($cup ? $cup . 'カップ' : '') . ($height ? '身長' . $height . 'cm ' : '') . '人気順・新着順で作品をチェック。')
@section('og_type', 'profile')
@if($imageUrl)
@section('og_image', $imageUrl)
@endif
@if($sort !== 'rank' || $cast !== 'all' || $currentPage > 1)
@section('robots', 'noindex, follow')
@endif
@section('canonical', route('actress.show', $actressId))

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>{{ $name }}</h1>
            @if($ruby)
                <p>{{ $ruby }}</p>
            @endif
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => '女優', 'url' => route('actress.index')],
            ['label' => $name],
        ]])

        {{-- Actress Profile --}}
        <div class="actress-profile">
            @if($imageUrl)
                <div class="actress-profile-image">
                    <img src="{{ $imageUrl }}" alt="{{ $name }}">
                </div>
            @endif
            <div class="actress-profile-details">
                <h2>{{ $name }}</h2>
                @if($ruby)
                    <p class="actress-ruby">{{ $ruby }}</p>
                @endif
                <div class="actress-specs">
                    @if($height)
                        <span class="spec-item">身長: {{ $height }}cm</span>
                    @endif
                    @if($bust || $waist || $hip)
                        <span class="spec-item">
                            B{{ $bust ?? '-' }}{{ $cup ? "($cup)" : '' }}
                            / W{{ $waist ?? '-' }}
                            / H{{ $hip ?? '-' }}
                        </span>
                    @endif
                    @if($birthday)
                        <span class="spec-item">誕生日: {{ $birthday }}</span>
                    @endif
                </div>
                <p class="actress-work-count">出演作品: {{ number_format($totalCount) }}件</p>
            </div>
        </div>

        {{-- Sort & Cast Filter --}}
        <div class="filter-bar" style="flex-wrap: wrap; gap: 6px;">
            <a href="{{ route('actress.show', $actressId) }}?sort=rank&cast={{ $cast }}" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
            <a href="{{ route('actress.show', $actressId) }}?sort=date&cast={{ $cast }}" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('actress.show', $actressId) }}?sort=review&cast={{ $cast }}" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>
            <span style="margin: 0 4px; color: var(--text-muted); align-self: center;">|</span>
            <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast=all"   class="tab-btn {{ $cast === 'all'   ? 'active' : '' }}">全て</a>
            <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast=solo"  class="tab-btn {{ $cast === 'solo'  ? 'active' : '' }}">単体</a>
            <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast=multi" class="tab-btn {{ $cast === 'multi' ? 'active' : '' }}">複数出演</a>
            @if($cast !== 'all')
                <span class="filter-count">{{ number_format($totalCount) }}件</span>
            @endif
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
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page=1" class="page-btn">最初へ</a>
                @endif
                @if($currentPage > 1)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage - 1 }}" class="page-btn">前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage + 1 }}" class="page-btn">次へ</a>
                    @if($currentPage < $totalPages - 1)
                        <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $totalPages }}" class="page-btn">最後へ</a>
                    @endif
                @endif
            </div>
        @endif

        {{-- Similar Actresses --}}
        @if(!empty($similarActresses))
        <section style="margin-top: 48px;">
            <div class="section-header">
                <h2 class="section-title">{{ $name }}に似た女優</h2>
            </div>
            <div class="actress-grid" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
                @foreach($similarActresses as $sim)
                    @php
                        $simId   = $sim['id'] ?? '';
                        $simName = $sim['name'] ?? '';
                        $simImg  = str_replace('http://', 'https://', $sim['imageURL']['large'] ?? $sim['imageURL']['small'] ?? '');
                        $simRuby = $sim['ruby'] ?? '';
                    @endphp
                    @if($simId)
                    <a href="{{ route('actress.show', $simId) }}" class="actress-card">
                        <div class="actress-thumb">
                            @if($simImg)
                                <img src="{{ $simImg }}" alt="{{ $simName }}" loading="lazy">
                            @else
                                <div class="actress-thumb-placeholder">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="actress-info">
                            <span class="actress-name">{{ $simName }}</span>
                            @if($simRuby)
                                <span class="actress-ruby">{{ $simRuby }}</span>
                            @endif
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>
        @endif

        <div class="back-link" style="margin-top: 32px;">
            <a href="{{ route('actress.index') }}">&larr; 女優一覧に戻る</a>
        </div>
    </div>
@endsection

@push('head_links')
@if($currentPage > 1)
<link rel="prev" href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage - 1 }}">
@endif
@if($currentPage < $totalPages)
<link rel="next" href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage + 1 }}">
@endif
@endpush

@push('scripts')
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => $name,
        'url'      => route('actress.show', $actressId),
    ];
    if ($ruby)     $schema['alternateName'] = $ruby;
    if ($imageUrl) $schema['image']         = $imageUrl;
    if ($birthday) $schema['birthDate']     = $birthday;
    if ($height)   $schema['height'] = [
        '@type'    => 'QuantitativeValue',
        'value'    => (int) $height,
        'unitCode' => 'CMT',
    ];

    $itemListSchema = !empty($items) ? [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $name . ' 出演作品一覧',
        'description'     => $name . 'の出演FANZA動画一覧',
        'numberOfItems'   => count($items),
        'itemListElement' => array_map(fn($item, $index) => [
            '@type'    => 'ListItem',
            'position' => ($currentPage - 1) * 20 + $index + 1,
            'name'     => $item['title'] ?? '',
            'url'      => $item['URL'] ?? '',
        ], $items, array_keys($items)),
    ] : null;
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@if($itemListSchema)
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush
