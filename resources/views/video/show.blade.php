@extends('layouts.app')

@php
    $title       = $item['title'] ?? 'タイトル未設定';
    $affiliateUrl = $item['affiliateURL'] ?? $item['URL'] ?? '#';
    $imageUrl    = str_replace('http://', 'https://', $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? '');
    $contentId   = $item['content_id'] ?? '';
    $actresses   = $item['iteminfo']['actress'] ?? [];
    $genres      = $item['iteminfo']['genre'] ?? [];
    $maker       = $item['iteminfo']['maker'][0]['name'] ?? null;
    $review      = $item['review']['average'] ?? null;
    $reviewCount = $item['review']['count'] ?? null;
    $price       = $item['prices']['price'] ?? null;
    $releaseDate = $item['date'] ?? null;

    $genreSlugMap = collect(config('fanza.genres'))->mapWithKeys(fn($g, $slug) => [$g['label'] => $slug])->all();

    $primaryActressName = $actresses[0]['name'] ?? null;
    $primaryActressId   = $actresses[0]['id'] ?? null;
    $primaryGenreName   = $genres[0]['name'] ?? null;

    $seoTitle = $primaryActressName
        ? $title . '｜' . $primaryActressName . ' - FanzaGate'
        : $title . ' - FanzaGate';
    $seoDesc = $title . 'の無料サンプル動画。'
        . ($primaryActressName ? $primaryActressName . '出演。' : '')
        . ($maker ? $maker . '制作。' : '')
        . 'FANZAで配信中。';
@endphp

@section('title', $seoTitle)
@section('description', $seoDesc)
@section('og_type', 'video.movie')
@if($imageUrl)
@section('og_image', $imageUrl)
@endif
@section('canonical', route('fanza.video.show', $contentId))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tweet.css') }}?v={{ filemtime(public_path('css/tweet.css')) }}">
@endpush

@section('content')
<div class="tweet-page">
    @include('partials.breadcrumb', ['items' => [
        ['label' => 'ホーム', 'url' => route('home')],
        ['label' => 'ランキング', 'url' => route('ranking')],
        ['label' => $title],
    ]])

    <div class="mb-4">
        <a href="javascript:history.back()" class="text-gray-400 hover:text-accent text-sm transition">&larr; 戻る</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- メインコンテンツ --}}
        <div class="lg:col-span-2">
            {{-- サンプル動画 --}}
            <div class="bg-black rounded-lg overflow-hidden mb-4">
                @if($contentId)
                    <div class="relative" style="padding-top: 65%;">
                        <iframe
                            src="https://www.dmm.co.jp/litevideo/-/part/=/affi_id={{ config('fanza.affiliate_id') }}/cid={{ $contentId }}/size=1280_720/"
                            class="absolute left-0 right-0 w-full"
                            style="top: -15%; height: 125%;"
                            frameborder="0"
                            allowfullscreen
                            scrolling="no"
                        ></iframe>
                    </div>
                @elseif($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-full aspect-video object-cover">
                @endif
            </div>

            <h1 class="text-xl font-bold mb-3">{{ $title }}</h1>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mb-4 text-sm text-gray-400">
                @if($actresses)
                    <span>出演:
                        @foreach($actresses as $a)
                            <a href="{{ route('actress.show', $a['id']) }}" class="item-actress-link">{{ $a['name'] }}</a>@if(!$loop->last), @endif
                        @endforeach
                    </span>
                @endif
                @if($maker)<span>メーカー: {{ $maker }}</span>@endif
                @if($releaseDate)<span>発売日: {{ $releaseDate }}</span>@endif
            </div>

            {{-- 評価・価格 --}}
            @if($review || $price)
                <div class="flex items-center gap-6 mb-6 py-3 border-t border-b border-gray-700">
                    @if($review)
                        <div class="flex items-center gap-1.5 text-yellow-400">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                            <span class="font-bold text-lg leading-none">{{ $review }}</span>
                            <span class="text-gray-500 text-sm">/ 5.0</span>
                            @if($reviewCount)
                                <span class="text-gray-500 text-xs">({{ number_format($reviewCount) }}件)</span>
                            @endif
                        </div>
                    @endif
                    @if($price)
                        <div class="text-gray-300 text-sm">
                            <span class="font-bold text-base">{{ $price }}</span>
                            <span class="text-gray-400">円</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- FANZAリンク --}}
            <a href="{{ $affiliateUrl }}" target="_blank" rel="nofollow noopener"
               class="flex items-center justify-center gap-2 w-full sm:w-auto bg-accent hover:bg-red-600 text-white font-bold px-8 py-3 rounded-lg transition mb-6">
                FANZAで詳細を見る
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>

            {{-- ジャンルタグ --}}
            @if($genres)
                <div class="mb-6">
                    <h3 class="text-sm text-gray-400 mb-2">ジャンル</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($genres as $g)
                            @php $matchedSlug = $genreSlugMap[$g['name']] ?? null; @endphp
                            @if($matchedSlug)
                                <a href="{{ route('genre.show', $matchedSlug) }}"
                                   class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-1 rounded-full transition">{{ $g['name'] }}</a>
                            @else
                                <span class="bg-gray-800 text-gray-500 text-xs px-3 py-1 rounded-full">{{ $g['name'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- この作品が好きな人はこちらも --}}
            @if(!empty($alsoWatched))
                <div class="mt-4">
                    <h2 class="text-lg font-bold mb-4">
                        @if($primaryGenreName){{ $primaryGenreName }}好きな人はこちらも@else関連作品@endif
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem;">
                        @foreach($alsoWatched as $related)
                            @include('partials.item-card', ['item' => $related])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- サイドバー: 同じ女優の作品 --}}
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold mb-3">
                {{ $primaryActressName ? $primaryActressName . 'の他の作品' : '関連作品' }}
            </h3>
            @if(empty($actressItems))
                <p class="text-gray-500 text-sm">関連作品はありません</p>
            @else
                <div class="space-y-4">
                    @foreach($actressItems as $related)
                        @php
                            $rTitle    = $related['title'] ?? '';
                            $rUrl      = route('fanza.video.show', $related['content_id'] ?? '');
                            $rImg      = str_replace('http://', 'https://', $related['imageURL']['small'] ?? $related['imageURL']['large'] ?? '');
                            $rActress  = $related['iteminfo']['actress'][0]['name'] ?? '';
                            $rReview   = $related['review']['average'] ?? null;
                        @endphp
                        <div class="flex gap-3 group">
                            <a href="{{ $rUrl }}" class="flex-shrink-0 w-32">
                                <img src="{{ $rImg }}" alt="{{ $rTitle }}"
                                     class="w-full aspect-video object-cover rounded group-hover:opacity-80 transition" loading="lazy">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $rUrl }}" class="text-sm line-clamp-2 hover:text-accent transition">{{ $rTitle }}</a>
                                @if($rActress)<p class="text-xs text-gray-400 mt-1">{{ $rActress }}</p>@endif
                                @if($rReview)<span class="text-xs text-yellow-400">★ {{ $rReview }}</span>@endif
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
@php
    $schema = [
        '@context'     => 'https://schema.org',
        '@type'        => 'VideoObject',
        'name'         => $title,
        'description'  => $seoDesc,
        'thumbnailUrl' => $imageUrl,
        'embedUrl'     => $contentId
            ? 'https://www.dmm.co.jp/litevideo/-/part/=/affi_id=' . config('fanza.affiliate_id') . '/cid=' . $contentId . '/size=1280_720/'
            : null,
    ];
    if ($releaseDate) $schema['uploadDate'] = $releaseDate;
    if ($actresses) {
        $schema['actor'] = array_map(fn($a) => ['@type' => 'Person', 'name' => $a['name']], $actresses);
    }
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
