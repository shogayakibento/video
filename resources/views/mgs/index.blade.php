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
                @foreach($videos as $video)
                    @include('partials.mgs-card', ['video' => $video])
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
.mgs-grid {
    margin-top: 1.5rem;
}
.mgs-card {
    cursor: default;
    display: flex;
    flex-direction: column;
}
.mgs-thumb {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/9;
    background: #111;
    border-radius: 8px 8px 0 0;
}
.mgs-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.2s;
}
.mgs-thumbnail-clickable {
    cursor: pointer;
}
.mgs-thumbnail-clickable:hover {
    opacity: 0.85;
}
.mgs-play-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
}
.mgs-thumb:hover .mgs-play-overlay {
    opacity: 1;
}
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
}
.mgs-video-player {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
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
.mgs-btn:hover {
    background: #c2185b;
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.mgs-card').forEach(function(card) {
    var videoUrl = card.dataset.videoUrl;
    if (!videoUrl) return;

    var thumb = card.querySelector('.mgs-thumb');
    var img   = card.querySelector('.mgs-thumbnail');

    img.addEventListener('click', function() {
        // 既に再生中なら何もしない
        if (thumb.querySelector('video')) return;

        var video = document.createElement('video');
        video.src = videoUrl;
        video.controls = true;
        video.autoplay = true;
        video.playsinline = true;
        video.className = 'mgs-video-player';

        // サムネ・オーバーレイを非表示にして動画を挿入
        img.style.display = 'none';
        var overlay = thumb.querySelector('.mgs-play-overlay');
        if (overlay) overlay.style.display = 'none';
        thumb.appendChild(video);
    });
});
</script>
@endpush
