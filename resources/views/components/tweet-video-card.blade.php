@props(['video', 'rank' => null])

<div class="item-card">
    <a href="{{ route('tweet.video.show', $video) }}" class="item-card-thumb">
        <div style="position: relative;">
            @if($rank)
                <div class="rank-number rank-{{ min($rank, 10) }}" style="position: absolute; top: 8px; left: 8px; z-index: 10; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; background: {{ $rank <= 3 ? 'var(--accent)' : '#555' }}; color: white;">
                    {{ $rank }}
                </div>
            @endif
            <img
                src="{{ $video->thumbnail_url }}"
                alt="{{ $video->title }}"
                style="width: 100%; aspect-ratio: 16/10; object-fit: cover; display: block;"
                loading="lazy"
            >
            @if($video->sample_video_url)
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; background: rgba(0,0,0,0.3);" class="play-overlay">
                    <div style="background: rgba(0,0,0,0.7); border-radius: 50%; padding: 10px;">
                        <svg width="28" height="28" fill="white" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                </div>
            @endif
        </div>
    </a>
    <div class="item-card-body">
        <a href="{{ route('tweet.video.show', $video) }}">
            <h3 class="item-card-title">{{ $video->title }}</h3>
        </a>
        @if($video->actress)
            <p class="item-card-meta">{{ $video->actress }}</p>
        @endif
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
            <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-secondary);">
                <span style="display: flex; align-items: center; gap: 4px; color: var(--accent);">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    {{ number_format($video->total_likes) }}
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                    {{ number_format($video->total_retweets) }}
                </span>
            </div>
            <a href="{{ route('tweet.video.redirect', $video) }}" target="_blank" rel="nofollow noopener"
               class="btn-primary" style="font-size: 11px; padding: 4px 10px;">
                FANZA
            </a>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.item-card:hover .play-overlay { opacity: 1 !important; }
</style>
@endpush
@endonce
