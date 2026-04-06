@php
    $hasVideo = !empty($video->sample_video_url);
@endphp

<div class="mgs-card item-card" data-video-url="{{ $video->sample_video_url ?? '' }}">
    <div class="item-thumb mgs-thumb">
        {{-- サムネ（クリックで動画に切り替わる） --}}
        <img src="{{ $video->thumbnail_url }}"
             alt="{{ $video->title }}"
             loading="lazy"
             class="mgs-thumbnail {{ $hasVideo ? 'mgs-thumbnail-clickable' : '' }}">

        @if($hasVideo)
            <div class="mgs-play-overlay">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="white">
                    <circle cx="12" cy="12" r="12" fill="rgba(0,0,0,0.55)"/>
                    <polygon points="10,8 17,12 10,16" fill="white"/>
                </svg>
            </div>
        @endif

        <span class="mgs-badge">MGS</span>
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
