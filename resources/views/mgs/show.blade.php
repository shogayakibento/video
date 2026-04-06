@extends('layouts.app')

@php
    $title       = $video->title;
    $actress     = $video->actress;
    $maker       = $video->maker;
    $genre       = $video->genre;
    $releaseDate = $video->release_date;
    $thumbnail   = $video->thumbnail_url;
    $sampleVideo = $video->sample_video_url;
    $affUrl      = $video->affiliate_url;
    $productCode = $video->dmm_content_id;

    $firstActress = $actress ? trim(explode(',', $actress)[0]) : null;

    $seoTitle = $firstActress
        ? $title . '｜' . $firstActress . ' - FanzaGate'
        : $title . ' - FanzaGate';
    $seoDesc = $title . 'の無料サンプル動画。'
        . ($firstActress ? $firstActress . '出演。' : '')
        . ($maker ? $maker . '制作。' : '')
        . 'MGSで配信中。';
@endphp

@section('title', $seoTitle)
@section('description', $seoDesc)
@section('og_type', 'video.movie')
@if($thumbnail)
@section('og_image', $thumbnail)
@endif
@section('canonical', route('mgs.show', $productCode))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="tweet-page">
    @include('partials.breadcrumb', ['items' => [
        ['label' => 'ホーム', 'url' => route('home')],
        ['label' => 'MGS動画', 'url' => route('mgs.index')],
        ['label' => $title],
    ]])

    <div class="mb-4">
        <a href="javascript:history.back()" class="text-gray-400 hover:text-accent text-sm transition">&larr; 戻る</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- メインコンテンツ --}}
        <div class="lg:col-span-2">
            {{-- 動画プレイヤー --}}
            <div class="bg-black rounded-lg overflow-hidden mb-4">
                @if($sampleVideo)
                    <div class="relative">
                        <video id="mgsVideoPlayer"
                            src="{{ $sampleVideo }}"
                            playsinline
                            preload="metadata"
                            poster="{{ $thumbnail }}"
                            class="w-full aspect-video block"
                        ></video>
                        {{-- 再生ボタンオーバーレイ --}}
                        <button id="mgsPlayOverlay"
                            class="absolute inset-0 w-full flex items-center justify-center"
                            style="background: rgba(0,0,0,0.25); border: none; outline: none; cursor: pointer;"
                            aria-label="再生">
                            <span style="background:#e91e8c; border-radius:50%; width:72px; height:72px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 20px rgba(0,0,0,0.5);">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="white" style="margin-left:4px;">
                                    <polygon points="6,3 21,12 6,21"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                @elseif($thumbnail)
                    <div class="relative">
                        <img src="{{ $thumbnail }}" alt="{{ $title }}" class="w-full aspect-video object-cover block">
                        <a href="{{ $affUrl }}" target="_blank" rel="nofollow noopener"
                           class="absolute inset-0 flex items-center justify-center"
                           style="background: rgba(0,0,0,0.25);">
                            <span style="background:#e91e8c; border-radius:50%; width:72px; height:72px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 20px rgba(0,0,0,0.5);">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="white" style="margin-left:4px;">
                                    <polygon points="6,3 21,12 6,21"/>
                                </svg>
                            </span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- タイトル --}}
            <div class="flex items-start gap-2 mb-3">
                <span style="background:#e91e8c;" class="flex-shrink-0 text-xs font-bold text-white px-2 py-0.5 rounded mt-1">MGS</span>
                <h1 class="text-xl font-bold leading-tight">{{ $title }}</h1>
            </div>

            {{-- メタ情報 --}}
            <div class="flex flex-wrap items-center gap-4 mb-4 text-sm text-gray-400">
                @if($actress)
                    <span>出演:
                        @foreach(array_filter(array_map('trim', explode(',', $actress))) as $a)
                            <span class="text-gray-200">{{ $a }}</span>@if(!$loop->last), @endif
                        @endforeach
                    </span>
                @endif
                @if($maker)<span>メーカー: {{ $maker }}</span>@endif
                @if($releaseDate)<span>配信日: {{ $releaseDate }}</span>@endif
            </div>

            {{-- MGStageリンク --}}
            <a href="{{ $affUrl }}" target="_blank" rel="nofollow noopener"
               class="inline-block bg-accent hover:bg-red-600 text-white font-bold px-8 py-3 rounded-lg transition text-center mb-6"
               style="background:#e91e8c;">
                MGStageで詳細を見る →
            </a>

            {{-- ジャンルタグ --}}
            @if($genre)
                <div class="mb-6">
                    <h3 class="text-sm text-gray-400 mb-2">ジャンル</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_filter(array_map('trim', explode(' ', $genre))) as $g)
                            <span class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-1 rounded-full transition">{{ $g }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- サイドバー: 同じ女優の他作品 --}}
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold mb-3">
                {{ $firstActress ? $firstActress . 'の他の作品' : '関連作品' }}
            </h3>
            @if($related->isEmpty())
                <p class="text-gray-500 text-sm">関連作品はありません</p>
            @else
                <div class="space-y-4">
                    @foreach($related as $rel)
                        <div class="flex gap-3 group">
                            <a href="{{ route('mgs.show', $rel->dmm_content_id) }}" class="flex-shrink-0 w-32">
                                <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->title }}"
                                     class="w-full aspect-[3/2] object-cover rounded group-hover:opacity-80 transition" loading="lazy">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('mgs.show', $rel->dmm_content_id) }}"
                                   class="text-sm line-clamp-2 hover:text-accent transition">{{ $rel->title }}</a>
                                @if($rel->actress)
                                    <p class="text-xs text-gray-400 mt-1">{{ trim(explode(',', $rel->actress)[0]) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var overlay = document.getElementById('mgsPlayOverlay');
    var player  = document.getElementById('mgsVideoPlayer');
    if (!overlay || !player) return;

    overlay.addEventListener('click', function() {
        overlay.style.display = 'none';
        player.controls = true;
        player.play().catch(function() {});
    });

    // 再生開始したらオーバーレイを消す
    player.addEventListener('play', function() {
        overlay.style.display = 'none';
    });
})();
</script>

@php
    $schema = [
        '@context'     => 'https://schema.org',
        '@type'        => 'VideoObject',
        'name'         => $title,
        'description'  => $seoDesc,
        'thumbnailUrl' => $thumbnail,
        'contentUrl'   => $sampleVideo ?: null,
        'embedUrl'     => $affUrl,
    ];
    if ($releaseDate) $schema['uploadDate'] = $releaseDate;
    if ($actress) {
        $schema['actor'] = array_map(
            fn($a) => ['@type' => 'Person', 'name' => trim($a)],
            array_filter(explode(',', $actress))
        );
    }
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
