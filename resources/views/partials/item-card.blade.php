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
    $contentId = !empty($item['sampleMovieURL']) ? ($item['content_id'] ?? null) : null;
    $eager = $eager ?? false;
@endphp

@if($contentId)
<div class="item-card item-card-clickable"
     data-content-id="{{ $contentId }}"
     data-title="{{ $title }}"
     data-actress="{{ $actress }}"
     data-actress-id="{{ $actressId }}"
     data-url="{{ $url }}"
     data-price="{{ $price }}"
     role="button" tabindex="0">
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
        @if($contentId)
            <div class="play-overlay" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
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
