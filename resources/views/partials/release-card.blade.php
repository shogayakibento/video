@php
    $title = $item['title'] ?? 'タイトル未設定';
    $url = $item['URL'] ?? '#';
    $anyImageUrl = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
    $isMono = str_contains($anyImageUrl, '/mono/');
    if ($isMono) {
        $listUrl = str_replace('http://', 'https://', $item['imageURL']['list'] ?? $anyImageUrl);
        $imageUrl = str_replace('pt.jpg', 'pl.jpg', $listUrl);
    } else {
        $imageUrl = str_replace('http://', 'https://', $anyImageUrl);
    }
    $actress = $item['iteminfo']['actress'][0]['name'] ?? null;
    $actressId = $item['iteminfo']['actress'][0]['id'] ?? null;
    $date = isset($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('m/d') : null;
    $hasSample = !empty($item['sampleMovieURL']['size_720_480'])
              || !empty($item['sampleMovieURL']['size_644_414'])
              || !empty($item['sampleMovieURL']['size_560_360']);
    $price = $item['prices']['price'] ?? null;
    $sampleMp4 = '';
    $hasDvdSample = $isMono && !empty($item['sampleImageURL']['sample_s']['image']);
    if ($hasDvdSample) {
        $sampleImgUrl = $item['sampleImageURL']['sample_s']['image'][0] ?? '';
        if ($sampleImgUrl && preg_match('/\/digital\/video\/([^\/]+)\//', $sampleImgUrl, $m)) {
            $cid = $m[1];
        } else {
            $cid = preg_replace('/(dl|dp|ec|vec|[xda])$/i', '', $item['content_id'] ?? '');
        }
        if ($cid) {
            $c1 = substr($cid, 0, 1);
            $c3 = substr($cid, 0, 3);
            $sampleMp4 = "https://cc3001.dmm.co.jp/litevideo/freepv/{$c1}/{$c3}/{$cid}/{$cid}_mhb_w.mp4";
        }
    } elseif (!$isMono && $hasSample) {
        $cid = strtolower($item['content_id'] ?? '');
        $c1  = substr($cid, 0, 1);
        $c3  = substr($cid, 0, 3);
        $sampleMp4 = "https://cc3001.dmm.co.jp/litevideo/freepv/{$c1}/{$c3}/{$cid}/{$cid}_mhb_w.mp4";
    }
    $contentId = ($hasSample || !empty($sampleMp4)) ? ($item['content_id'] ?? null) : null;
@endphp

@if($contentId)
<div class="release-card item-card-clickable"
     data-detail-url="{{ route('fanza.video.show', $contentId) }}"
     data-sample-url="{{ $sampleMp4 }}"
     role="link" tabindex="0">
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
        @if($sampleMp4)
            <div class="hover-video-wrap" aria-hidden="true"></div>
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
