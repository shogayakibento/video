@php
    $hasVideo  = !empty($video->sample_video_url);
    $detailUrl = route('mgs.show', $video->dmm_content_id);
@endphp

<div class="item-card item-card-clickable mgs-card"
     data-detail-url="{{ $detailUrl }}"
     data-sample-url="{{ $hasVideo ? $video->sample_video_url : '' }}"
     role="link" tabindex="0">

    <div class="item-thumb">
        <img src="{{ $video->thumbnail_url }}"
             alt="{{ $video->title }}"
             loading="lazy">

        <span class="mgs-badge">MGS</span>

        {{-- ホバープレビュー --}}
        @if($hasVideo)
            <div class="hover-video-wrap" aria-hidden="true"></div>
        @endif
    </div>

    <div class="item-info">
        <h3 class="item-title">{{ $video->title }}</h3>
        @if($video->actress)
            <p class="item-actress">{{ $video->actress }}</p>
        @endif
        <div class="item-bottom">
            @if($video->maker)
                <span class="item-maker">{{ $video->maker }}</span>
            @endif
            <a href="{{ $video->affiliate_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="mgs-btn"
               onclick="event.stopPropagation()">MGStageで見る →</a>
        </div>
    </div>
</div>
