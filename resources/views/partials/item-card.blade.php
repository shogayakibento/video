@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = $item['URL'] ?? '#';
    $anyImageUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
    $isMono = str_contains($anyImageUrl, '/mono/');
    if ($isMono) {
        // mono(DVD等): pt.jpg → pl.jpg で横長パッケージ画像を使う
        $listUrl = str_replace('http://', 'https://', $item['imageURL']['list'] ?? $anyImageUrl);
        $imageUrl = str_replace('pt.jpg', 'pl.jpg', $listUrl);
    } else {
        $imageUrl = str_replace('http://', 'https://',
            $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '');
    }
    $review = $item['review']['average'] ?? null;
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $actressId = $item['iteminfo']['actress'][0]['id'] ?? null;
    $maker = $item['iteminfo']['maker'][0]['name'] ?? null;
    $price = $item['prices']['price'] ?? null;
    $hasSample = !empty($item['sampleMovieURL']['size_720_480'])
              || !empty($item['sampleMovieURL']['size_644_414'])
              || !empty($item['sampleMovieURL']['size_560_360']);
    $eager = $eager ?? false;

    // サンプルMP4 URL（contentId設定より先に構築）
    $sampleMp4 = '';
    if ($isMono) {
        $sampleImgUrl = $item['sampleImageURL']['sample_s']['image'][0] ?? '';
        if ($sampleImgUrl && preg_match('/\/digital\/video\/([^\/]+)\//', $sampleImgUrl, $m)) {
            $cid = $m[1];
        } else {
            $cid = preg_replace('/(dl|dp|ec|vec|[xda])$/i', '', $item['content_id'] ?? '');
        }
        if ($cid) {
            $c1  = substr($cid, 0, 1);
            $c3  = substr($cid, 0, 3);
            $sampleMp4 = "https://cc3001.dmm.co.jp/litevideo/freepv/{$c1}/{$c3}/{$cid}/{$cid}_mhb_w.mp4";
        }
    } elseif ($hasSample) {
        $rawId = $item['content_id'] ?? '';
        $cid = strtolower($rawId);
        $c1  = substr($cid, 0, 1);
        $c3  = substr($cid, 0, 3);
        $sampleMp4 = "https://cc3001.dmm.co.jp/litevideo/freepv/{$c1}/{$c3}/{$cid}/{$cid}_mhb_w.mp4";
    }
    $contentId = ($hasSample || !empty($sampleMp4)) ? ($item['content_id'] ?? null) : null;
@endphp

@php
    $sampleImagesJson = '';
    if ($isMono && !empty($item['sampleImageURL']['sample_s']['image'])) {
        $sampleImagesJson = json_encode(array_values($item['sampleImageURL']['sample_s']['image']));
    }
@endphp
@if($contentId)
<div class="item-card item-card-clickable"
     data-detail-url="{{ route('fanza.video.show', $contentId) }}"
     data-sample-url="{{ $sampleMp4 }}"
     @if($sampleImagesJson) data-sample-images="{{ $sampleImagesJson }}"@endif
     role="link" tabindex="0">
@else
<a href="{{ $url }}" class="item-card" target="_blank" rel="noopener noreferrer">
@endif
    <div class="item-thumb">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="{{ $eager ? 'eager' : 'lazy' }}"@if($eager) fetchpriority="high"@endif>
        @else
            <div class="thumb-placeholder-card">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
            </div>
        @endif
        @if(isset($rank) && $rank)
            <span class="rank-badge rank-{{ $rank }}">{{ $rank }}</span>
        @endif
        @if($review)
            <span class="rating-badge">★ {{ $review }}</span>
        @endif
        @if($sampleMp4 || $sampleImagesJson)
            <div class="hover-video-wrap" aria-hidden="true"></div>
        @endif
    </div>
    <div class="item-info">
        <h3 class="item-title">{{ $title }}</h3>
        @if($actress)
            <p class="item-actress">
                @if($contentId && $actressId)
                    <a href="{{ route('actress.show', $actressId) }}" class="item-actress-link" onclick="event.stopPropagation()">{{ $actress }}</a>
                @else
                    {{ $actress }}
                @endif
            </p>
        @endif
        <div class="item-bottom">
            @if($maker)
                <span class="item-maker">{{ $maker }}</span>
            @endif
            @if($price)
                <span class="item-price">{{ preg_replace('/~$/', '円〜', $price) }}{{ str_ends_with($price, '~') ? '' : '円' }}</span>
            @endif
        </div>
    </div>
@if($contentId)
</div>
@else
</a>
@endif
