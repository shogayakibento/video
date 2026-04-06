@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = $item['URL'] ?? '#';
    $imageUrl = str_replace('http://', 'https://',
        $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '');
    $review = $item['review']['average'] ?? null;
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $actressId = $item['iteminfo']['actress'][0]['id'] ?? null;
    $maker = $item['iteminfo']['maker'][0]['name'] ?? null;
    $price = $item['prices']['price'] ?? null;
    $hasSample = !empty($item['sampleMovieURL']);
    $contentId = $hasSample ? ($item['content_id'] ?? null) : null;
    $eager = $eager ?? false;

    // サンプルMP4 URL を組み立て
    // パターン: cc3001.dmm.co.jp/litevideo/freepv/{c1}/{c3}/{cid}/{cid}_mhb_w.mp4
    // ※ 英字始まりの標準形式 content_id のみ対応（h_xxx 等の特殊形式は除外）
    $sampleMp4 = '';
    if ($contentId && preg_match('/^[a-z]{2,}/i', $contentId)) {
        $cid = strtolower($contentId);
        $c1  = substr($cid, 0, 1);
        $c3  = substr($cid, 0, 3);
        $sampleMp4 = "https://cc3001.dmm.co.jp/litevideo/freepv/{$c1}/{$c3}/{$cid}/{$cid}_mhb_w.mp4";
    }
@endphp

@if($contentId)
<div class="item-card item-card-clickable"
     data-detail-url="{{ route('fanza.video.show', $contentId) }}"
     data-sample-url="{{ $sampleMp4 }}"
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
        @if($sampleMp4)
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
