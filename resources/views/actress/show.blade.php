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

        {{-- 似てる女優 --}}
        @if(!empty($similarActresses))
        <div class="similar-actresses mb-6">
            <h2 class="section-title">
                <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                {{ $name }}に似た女優
            </h2>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach(array_slice($similarActresses, 0, 6) as $sim)
                    @php
                        $simId    = $sim['id'] ?? '';
                        $simName  = $sim['name'] ?? '';
                        $simImg   = str_replace('http://', 'https://', $sim['imageURL']['small'] ?? '');
                        $simHeight = $sim['height'] ?? null;
                        $simBust   = $sim['bust'] ?? null;
                        $simCup    = $sim['cup'] ?? null;
                    @endphp
                    <a href="{{ route('actress.show', $simId) }}" class="text-center group">
                        <div class="overflow-hidden rounded-lg mb-1 bg-gray-800">
                            @if($simImg)
                                <img src="{{ $simImg }}" alt="{{ $simName }}"
                                     class="w-full object-cover aspect-[3/4] group-hover:opacity-80 transition" loading="lazy">
                            @else
                                <div class="w-full aspect-[3/4] flex items-center justify-center text-gray-600 text-4xl">👤</div>
                            @endif
                        </div>
                        <p class="text-xs font-medium group-hover:text-accent transition truncate">{{ $simName }}</p>
                        @if($simHeight || $simBust)
                            <p class="text-xs text-gray-500">
                                @if($simHeight){{ $simHeight }}cm@endif
                                @if($simBust && $simCup) / B{{ $simBust }}({{ $simCup }})@endif
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @endif

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
                @if($currentPage > 1)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage - 1 }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&cast={{ $cast }}&page={{ $currentPage + 1 }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif

        <div class="back-link">
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
