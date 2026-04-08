@extends('layouts.app')

@section('title', 'MGS動画 おすすめ作品一覧 | FanzaGate')
@section('description', 'FANZAにはない女優もここにいる。MGS動画の人気・新着作品を一覧でチェック。サンプル動画あり。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>MGS動画</h1>
            <p>FANZAにはない作品・女優もここにいる</p>
        </div>
    </div>

    <div class="container">
        @include('partials.breadcrumb', ['items' => [
            ['label' => 'ホーム', 'url' => route('home')],
            ['label' => 'MGS動画'],
        ]])

        {{-- Sort tabs --}}
        <div class="filter-bar">
            <a href="{{ route('mgs.index') }}?sort=new"
               class="tab-btn {{ $sort === 'new' ? 'active' : '' }}">新着順</a>
            <a href="{{ route('mgs.index') }}?sort=popular"
               class="tab-btn {{ $sort === 'popular' ? 'active' : '' }}">人気順</a>
            <span class="filter-count">{{ number_format($totalCount) }}件</span>
        </div>

        {{-- Grid --}}
        @if($videos->count())
            <div class="items-grid content-grid mgs-grid">
                @foreach($videos as $index => $video)
                    @include('partials.mgs-card', ['video' => $video, 'eager' => $index < 3])
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>まだMGS作品が登録されていません。</p>
                <p>管理画面から品番を登録してください。</p>
            </div>
        @endif

        {{-- Pagination --}}
        @if($videos->lastPage() > 1)
            <div class="pagination">
                @if($videos->currentPage() > 1)
                    <a href="{{ $videos->previousPageUrl() }}" class="page-btn">前へ</a>
                @endif
                @for($i = max(1, $videos->currentPage() - 2); $i <= min($videos->lastPage(), $videos->currentPage() + 2); $i++)
                    <a href="{{ $videos->url($i) }}"
                       class="page-btn {{ $i === $videos->currentPage() ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($videos->hasMorePages())
                    <a href="{{ $videos->nextPageUrl() }}" class="page-btn">次へ</a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
.mgs-grid { margin-top: 1.5rem; }

/* MGS固有: バッジ */
.mgs-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #e91e8c;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.05em;
    pointer-events: none;
    z-index: 3;
}

/* MGS固有: アフィリエイトボタン */
.mgs-btn {
    display: inline-block;
    background: #e91e8c;
    color: #fff !important;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
}
.mgs-btn:hover { background: #c2185b; }
</style>
@endpush
