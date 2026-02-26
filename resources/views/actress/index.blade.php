@extends('layouts.app')

@section('title', '女優から探す - FanzaGate')
@section('description', 'FANZA女優ランキング・検索。人気女優ランキング、カップ・身長・年齢での絞り込み、名前検索でお気に入りの女優を見つけよう。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>女優から探す</h1>
            <p>お気に入りの女優を見つけよう</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => '女優'],
        ]])

        {{-- Main Tabs --}}
        <div class="filter-bar filter-bar-center">
            <a href="{{ route('actress.index', ['tab' => 'ranking']) }}" class="tab-btn {{ $tab === 'ranking' ? 'active' : '' }}">人気ランキング</a>
            <a href="{{ route('actress.index', ['tab' => 'filter']) }}" class="tab-btn {{ $tab === 'filter' ? 'active' : '' }}">絞り込み</a>
            <a href="{{ route('actress.index', ['tab' => 'search']) }}" class="tab-btn {{ $tab === 'search' ? 'active' : '' }}">名前検索</a>
        </div>

        {{-- Search Tab --}}
        @if($tab === 'search')
            <form action="{{ route('actress.index') }}" method="GET" class="actress-search-form">
                <input type="hidden" name="tab" value="search">
                <div class="search-box">
                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="女優名で検索..." class="search-input">
                    <button type="submit" class="search-submit-btn">検索</button>
                </div>
            </form>

            <div class="filter-bar filter-bar-center filter-bar-wrap">
                <a href="{{ route('actress.index', ['tab' => 'search']) }}" class="tab-btn {{ !$initial && !$keyword ? 'active' : '' }}">すべて</a>
                @foreach($initials as $kana => $label)
                    <a href="{{ route('actress.index', ['tab' => 'search', 'initial' => $kana]) }}" class="tab-btn {{ $initial === $kana ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            @if($keyword)
                <p class="search-result-info">「{{ $keyword }}」の検索結果: {{ number_format($totalCount) }}件</p>
            @endif
        @endif

        {{-- Filter Tab --}}
        @if($tab === 'filter')
            <form action="{{ route('actress.index') }}" method="GET" class="filter-form">
                <input type="hidden" name="tab" value="filter">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label">カップ</label>
                        <select name="cup" class="filter-select">
                            <option value="">指定なし</option>
                            @foreach(['A','B','C','D','E','F','G','H','I'] as $c)
                                <option value="{{ $c }}" {{ ($filters['cup'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}カップ</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">身長</label>
                        <div class="filter-range">
                            <input type="number" name="height_min" value="{{ $filters['heightMin'] ?? '' }}" placeholder="140" class="filter-input-sm" min="140" max="190">
                            <span>〜</span>
                            <input type="number" name="height_max" value="{{ $filters['heightMax'] ?? '' }}" placeholder="190" class="filter-input-sm" min="140" max="190">
                            <span>cm</span>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">年齢</label>
                        <div class="filter-age-presets">
                            @php
                                $agePresets = [
                                    ['label' => '18~20', 'min' => '18', 'max' => '20'],
                                    ['label' => '20~25', 'min' => '20', 'max' => '25'],
                                    ['label' => '25~30', 'min' => '25', 'max' => '30'],
                                    ['label' => '30~35', 'min' => '30', 'max' => '35'],
                                    ['label' => '35~40', 'min' => '35', 'max' => '40'],
                                    ['label' => '40~50', 'min' => '40', 'max' => '50'],
                                    ['label' => '50~',   'min' => '50', 'max' => ''],
                                ];
                                $currentAgeMin = $filters['ageMin'] ?? '';
                                $currentAgeMax = $filters['ageMax'] ?? '';
                            @endphp
                            @foreach($agePresets as $preset)
                                <label class="filter-age-chip {{ $currentAgeMin === $preset['min'] && $currentAgeMax === $preset['max'] ? 'active' : '' }}">
                                    <input type="radio" name="age_preset" value="{{ $preset['min'] }}-{{ $preset['max'] }}"
                                        {{ $currentAgeMin === $preset['min'] && $currentAgeMax === $preset['max'] ? 'checked' : '' }}>
                                    {{ $preset['label'] }}歳
                                </label>
                            @endforeach
                            <input type="hidden" name="age_min" id="age_min" value="{{ $currentAgeMin }}">
                            <input type="hidden" name="age_max" id="age_max" value="{{ $currentAgeMax }}">
                        </div>
                    </div>
                    <div class="filter-group filter-group-btn">
                        <button type="submit" class="filter-submit-btn">検索する</button>
                    </div>
                </div>
            </form>

            @if(!empty(array_filter($filters)))
                <p class="search-result-info">絞り込み結果: {{ number_format($totalCount) }}件</p>
            @endif
        @endif

        {{-- Ranking Tab Header --}}
        @if($tab === 'ranking')
            <p class="search-result-info">いま人気作品に出演している注目の女優たち</p>
        @endif

        {{-- Actress Grid --}}
        <div class="actress-grid {{ $tab === 'ranking' ? 'actress-grid-ranking' : '' }}">
            @forelse($actresses as $index => $actress)
                @php
                    $actressId = $actress['id'] ?? '';
                    $name = $actress['name'] ?? '不明';
                    $ruby = $actress['ruby'] ?? '';
                    if ($tab === 'ranking') {
                        $imageUrl = $actress['top_item_image'] ?? '';
                    } else {
                        $imageUrl = $actress['imageURL']['large'] ?? $actress['imageURL']['small'] ?? '';
                    }
                @endphp
                <a href="{{ route('actress.show', $actressId) }}" class="actress-card animate-on-scroll {{ $tab === 'ranking' ? 'actress-card-ranking' : '' }}">
                    @if($tab === 'ranking')
                        <span class="actress-rank-badge">{{ ($currentPage - 1) * 100 + $index + 1 }}</span>
                    @endif
                    <div class="actress-thumb {{ $tab === 'ranking' ? 'actress-thumb-square' : '' }}">
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
                        @if($tab === 'filter' && (($actress['cup'] ?? null) || ($actress['height'] ?? null)))
                            <span class="actress-spec-badge">
                                {{ ($actress['cup'] ?? null) ? $actress['cup'] . 'カップ' : '' }}{{ ($actress['cup'] ?? null) && ($actress['height'] ?? null) ? ' / ' : '' }}{{ ($actress['height'] ?? null) ? $actress['height'] . 'cm' : '' }}
                            </span>
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
                $paginationParams = ['tab' => $tab];
                if ($tab === 'search') {
                    if ($keyword) $paginationParams['keyword'] = $keyword;
                    if ($initial) $paginationParams['initial'] = $initial;
                } elseif ($tab === 'filter') {
                    foreach ($filters as $k => $v) {
                        if ($v !== '') {
                            $paramKey = match($k) {
                                'heightMin' => 'height_min',
                                'heightMax' => 'height_max',
                                'ageMin' => 'age_min',
                                'ageMax' => 'age_max',
                                default => $k,
                            };
                            $paginationParams[$paramKey] = $v;
                        }
                    }
                }
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

    @if($tab === 'filter')
    <script>
        document.querySelectorAll('input[name="age_preset"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var parts = this.value.split('-');
                document.getElementById('age_min').value = parts[0];
                document.getElementById('age_max').value = parts[1] || '';
            });
        });
    </script>
    @endif
@endsection
