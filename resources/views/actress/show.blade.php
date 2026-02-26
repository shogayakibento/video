@extends('layouts.app')

@php
    $name = $actress['name'] ?? '不明';
    $ruby = $actress['ruby'] ?? '';
    $imageUrl = $actress['imageURL']['large'] ?? $actress['imageURL']['small'] ?? '';
    $bust = $actress['bust'] ?? null;
    $cup = $actress['cup'] ?? null;
    $waist = $actress['waist'] ?? null;
    $hip = $actress['hip'] ?? null;
    $height = $actress['height'] ?? null;
    $birthday = $actress['birthday'] ?? null;
    $actressId = $actress['id'] ?? '';
@endphp

@section('title', $name . 'の動画一覧 - FanzaGate')
@section('description', $name . 'の出演動画一覧。人気順・新着順で作品をチェック。')

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

        {{-- Sort --}}
        <div class="filter-bar">
            <a href="{{ route('actress.show', $actressId) }}?sort=rank" class="tab-btn {{ $sort === 'rank' ? 'active' : '' }}">人気順</a>
            <a href="{{ route('actress.show', $actressId) }}?sort=date" class="tab-btn {{ $sort === 'date' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('actress.show', $actressId) }}?sort=review" class="tab-btn {{ $sort === 'review' ? 'active' : '' }}">レビュー順</a>
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
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&page={{ $currentPage - 1 }}" class="page-btn">&laquo; 前へ</a>
                @endif

                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&page={{ $i }}"
                       class="page-btn {{ $i === $currentPage ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                @if($currentPage < $totalPages)
                    <a href="{{ route('actress.show', $actressId) }}?sort={{ $sort }}&page={{ $currentPage + 1 }}" class="page-btn">次へ &raquo;</a>
                @endif
            </div>
        @endif

        <div class="back-link">
            <a href="{{ route('actress.index') }}">&larr; 女優一覧に戻る</a>
        </div>
    </div>
@endsection
