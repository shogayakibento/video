@extends('layouts.app')

@section('title', ($categories[$activeCategory]['label'] ?? 'ランキング') . 'ランキング - FanzaGate')
@section('description', 'FANZAの' . ($categories[$activeCategory]['label'] ?? '人気作品') . 'ランキング。' . ($categories[$activeCategory]['label'] ?? '動画・VR・DVD・コミック') . 'の人気作品をリアルタイムで更新。')
{{-- ?category=douga は /ranking と同一コンテンツのため重複を防ぐ --}}
@if($activeCategory === 'douga' && request()->has('category'))
@section('robots', 'noindex, follow')
@endif

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>{{ $categories[$activeCategory]['label'] ?? '人気作品' }}ランキング</h1>
            <p>FANZAの人気作品をランキング形式でお届け</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'ランキング'],
        ]])

        {{-- Category Tabs --}}
        <div class="filter-bar filter-bar-center">
            @foreach($categories as $slug => $cat)
                <a href="{{ route('ranking', ['category' => $slug]) }}"
                   class="tab-btn {{ $activeCategory === $slug ? 'active' : '' }}">
                    {{ $cat['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Ranking Grid --}}
        @if(count($items) > 0)
            <div class="items-grid">
                @foreach($items as $index => $item)
                    @include('partials.item-card', ['item' => $item, 'rank' => $index + 1, 'eager' => $index === 0])
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>ランキングデータを取得できませんでした。</p>
            </div>
        @endif

        @include('partials.ad-inline', ['bannerId' => '1782_300_250', 'adDomain' => 'dmm.com'])
    </div>
@endsection

@push('scripts')
@if(!empty($items))
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'FANZA ' . ($categories[$activeCategory]['label'] ?? 'ランキング'),
        'description' => 'FANZAの人気' . ($categories[$activeCategory]['label'] ?? '作品') . 'ランキング一覧',
        'numberOfItems' => count($items),
        'itemListElement' => array_map(fn($item, $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => addslashes($item['title'] ?? ''),
            'url' => $item['URL'] ?? '',
        ], $items, array_keys($items)),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush
