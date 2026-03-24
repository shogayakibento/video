@props(['video', 'rank' => null])

@php
    $rankClass = match(true) {
        $rank === 1 => 'rank-1',
        $rank === 2 => 'rank-2',
        $rank === 3 => 'rank-3',
        default     => 'rank-other',
    };
    $fmtCount = fn(int $n) => $n >= 10000
        ? rtrim(rtrim(number_format($n / 10000, 1), '0'), '.') . '万'
        : number_format($n);
@endphp

<div class="video-card rounded-2xl overflow-hidden group">
    <a href="{{ route('tweet.video.show', $video) }}" class="block">
        <div class="relative overflow-hidden">
            @if($rank)
                <div class="absolute top-2 left-2 z-10 {{ $rankClass }} text-white font-bold rounded-full w-8 h-8 flex items-center justify-center text-xs shadow-lg">
                    {{ $rank }}
                </div>
            @endif
            <img
                src="{{ $video->thumbnail_url }}"
                alt="{{ $video->title }}"
                class="w-full object-cover transition-transform duration-500 group-hover:scale-105" style="aspect-ratio: 3/2"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </div>
    </a>
    <div class="p-3.5">
        <a href="{{ route('tweet.video.show', $video) }}">
            <h3 class="text-sm font-medium line-clamp-2 leading-snug hover:text-accent transition-colors duration-200">{{ $video->title }}</h3>
        </a>
        @if($video->actress)
            <p class="text-xs mt-1.5 font-light tracking-wide" style="color: #7a7a9a;">{{ $video->actress }}</p>
        @endif
        <div class="flex items-center justify-between mt-3">
            <div class="flex items-center gap-3 text-xs" style="color: #5a5a7a;">
                <span class="flex items-center gap-1" style="color: #ff2d78;">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    {{ $fmtCount($video->total_likes) }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"/></svg>
                    {{ $fmtCount($video->total_retweets) }}
                </span>
            </div>
            <a href="{{ route('tweet.video.redirect', $video) }}" target="_blank" rel="nofollow noopener"
               class="fanza-btn text-white text-xs px-3.5 py-1.5 rounded-lg font-semibold tracking-wider">
                FANZA
            </a>
        </div>
    </div>
</div>
