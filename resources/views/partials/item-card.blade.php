@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = app(\App\Services\FanzaApiService::class)->getItemUrl($item);
    $imageUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
    $review = $item['review']['average'] ?? null;
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $maker = $item['iteminfo']['maker'][0]['name'] ?? null;
    $price = $item['prices']['price'] ?? null;
@endphp

<a href="{{ $url }}" class="item-card" target="_blank" rel="noopener noreferrer">
    <div class="item-thumb">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy">
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
    </div>
    <div class="item-info">
        <h3 class="item-title">{{ $title }}</h3>
        @if($actress)
            <p class="item-actress">{{ $actress }}</p>
        @endif
        <div class="item-bottom">
            @if($maker)
                <span class="item-maker">{{ $maker }}</span>
            @endif
            @if($price)
                <span class="item-price">{{ $price }}</span>
            @endif
        </div>
    </div>
</a>
