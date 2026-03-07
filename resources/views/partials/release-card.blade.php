@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = $item['URL'] ?? '#';
    $imageUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $actressId = $item['iteminfo']['actress'][0]['id'] ?? null;
    $date = isset($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('m/d') : null;
    $contentId = !empty($item['sampleMovieURL']) ? ($item['content_id'] ?? null) : null;
    $price = $item['prices']['price'] ?? null;
@endphp

@if($contentId)
<div class="release-card item-card-clickable"
     data-content-id="{{ $contentId }}"
     data-title="{{ $title }}"
     data-actress="{{ $actress }}"
     data-actress-id="{{ $actressId }}"
     data-url="{{ $url }}"
     data-price="{{ $price }}"
     role="button" tabindex="0">
@else
<a href="{{ $url }}" class="release-card" target="_blank" rel="noopener noreferrer">
@endif
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
        @if($contentId)
            <div class="play-overlay" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
        @endif
    </div>
    <div class="release-info">
        <h3>{{ $title }}</h3>
        @if($actress)
            <p class="release-actress">
                @if($contentId && $actressId)
                    <a href="{{ route('actress.show', $actressId) }}" class="item-actress-link" onclick="event.stopPropagation()">{{ $actress }}</a>
                @else
                    {{ $actress }}
                @endif
            </p>
        @endif
        @if($date)
            <p class="release-date">{{ $date }}</p>
        @endif
    </div>
@if($contentId)
</div>
@else
</a>
@endif
