@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = $item['affiliateURL'] ?? $item['URL'] ?? '#';
    $imageUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $date = isset($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('m/d') : null;
@endphp

<a href="{{ $url }}" class="release-card" target="_blank" rel="noopener noreferrer">
    <div class="release-thumb">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy">
        @else
            <div class="thumb-placeholder-card">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
            </div>
        @endif
        <span class="release-new-badge">NEW</span>
    </div>
    <div class="release-info">
        <h3>{{ $title }}</h3>
        @if($actress)
            <p class="release-actress">{{ $actress }}</p>
        @endif
        @if($date)
            <p class="release-date">{{ $date }}</p>
        @endif
    </div>
</a>
