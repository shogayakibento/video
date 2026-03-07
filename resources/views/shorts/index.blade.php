@extends('layouts.app')

@section('title', 'サンプル動画ショートビュー | FanzaGate')
@section('description', 'FANZAの人気作品サンプル動画をショート動画感覚でサクサク視聴。スワイプで次々に楽しめます。')

@push('styles')
<style>
/* ショートビューページはフッター非表示 */
body.shorts-page footer,
body.shorts-page .side-ad,
body.shorts-page > ins,
body.shorts-page > script[src*="banner_placement"] {
    display: none !important;
}

/* ====== Info panel（動画の直下に移動） ====== */
.shorts-info-overlay {
    /* 動画中心(42%) + 動画半高(338px) = 動画の下端、そこから少し下 */
    top: calc(42% + 342px);
    bottom: auto;
    left: 50%;
    transform: translateX(-50%);
    width: min(100%, calc((100vh - 64px) * 16 / 9 * 0.95));
    max-width: 1200px;
    padding: 10px 14px 12px;
    background: rgba(12, 12, 12, 0.92);
    backdrop-filter: blur(16px);
    border-radius: 0 0 14px 14px;
    pointer-events: auto;
}

/* info overlay を flex にして CTA を右端に */
.shorts-info-overlay {
    display: flex;
    align-items: center;
    gap: 14px;
}
.shorts-info-text {
    flex: 1;
    min-width: 0;
}
/* CTA をフロー内配置に（小さめ） */
.shorts-cta-btn {
    position: static !important;
    flex-shrink: 0;
    bottom: auto; right: auto;
    padding: 8px 12px;
    font-size: 0.72rem;
    white-space: nowrap;
    line-height: 1.3;
    text-align: center;
    box-shadow: 0 2px 12px rgba(255, 45, 120, 0.5);
}

/* Title */
.shorts-title-text {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.5;
    margin: 0 0 6px;
    padding-left: 10px;
    border-left: 3px solid var(--primary);
    text-shadow: 0 1px 8px rgba(0,0,0,0.9);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Actress row */
.shorts-actress-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 5px;
    pointer-events: auto;
}

.shorts-actress-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #fff;
    background: rgba(255, 45, 120, 0.22);
    border: 1px solid rgba(255, 45, 120, 0.45);
    backdrop-filter: blur(10px);
    padding: 3px 11px;
    border-radius: 9999px;
    text-decoration: none;
    transition: background 0.18s, border-color 0.18s, transform 0.15s;
    pointer-events: auto;
}

.shorts-actress-link::before {
    content: "♀";
    font-size: 0.7rem;
    opacity: 0.8;
}

.shorts-actress-link:hover {
    background: rgba(255, 45, 120, 0.5);
    border-color: rgba(255, 45, 120, 0.8);
    transform: translateY(-1px);
    color: #fff;
}

/* Actress link (non-linkable fallback) */
.shorts-actress-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(255,255,255,0.88);
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    padding: 3px 11px;
    border-radius: 9999px;
}

/* Maker & meta row */
.shorts-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
}

.shorts-maker-badge {
    font-size: 0.72rem;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    padding: 2px 9px;
    border-radius: 4px;
}

.shorts-rating-badge {
    font-size: 0.78rem;
    font-weight: 700;
    color: #fbbf24;
    text-shadow: 0 1px 4px rgba(0,0,0,0.6);
}

/* Genres */
.shorts-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 5px;
}

.shorts-genre-tag {
    font-size: 0.68rem;
    color: rgba(255,255,255,0.55);
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 1px 8px;
    border-radius: 4px;
}

/* Price */
.shorts-price-text {
    font-size: 0.82rem;
    font-weight: 700;
    color: #f9a8d4;
    margin: 0;
    text-shadow: 0 1px 4px rgba(0,0,0,0.6);
}

/* CTA button */
.shorts-cta-btn {
    bottom: 32px;
    right: 16px;
    padding: 13px 20px;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(255, 45, 120, 0.6);
    letter-spacing: 0.02em;
}

.shorts-cta-btn:hover {
    box-shadow: 0 6px 32px rgba(255, 45, 120, 0.8);
    transform: translateY(-2px) scale(1.04);
}
</style>
@endpush

@section('content')

<div class="shorts-wrapper" id="shortsWrapper">

    @if(empty($items))
        <div class="shorts-empty">
            <p>現在サンプル動画が取得できませんでした。</p>
            <a href="{{ route('home') }}" class="shorts-cta">トップへ戻る</a>
        </div>
    @else

    {{-- Counter --}}
    <div class="shorts-counter-bar" id="shortsCounterBar">
        <span id="shortsCounterCurrent">1</span><span class="shorts-counter-sep">/</span><span>{{ count($items) }}</span>
    </div>

    {{-- Scroll container --}}
    <div class="shorts-scroll" id="shortsScroll" data-affiliate-id="{{ config('fanza.affiliate_id') }}">
        @foreach($items as $index => $item)
        @php
            $title    = $item['title'] ?? '';
            $url      = $item['URL'] ?? '#';
            $imgLg    = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
            $actresses = $item['iteminfo']['actress'] ?? [];
            $maker    = $item['iteminfo']['maker'][0]['name'] ?? null;
            $rating   = $item['review']['average'] ?? null;
            $genres   = array_slice($item['iteminfo']['genre'] ?? [], 0, 3);
            $cid      = $item['content_id'];
            $price    = $item['prices']['price'] ?? null;
        @endphp
        <div class="shorts-item"
             data-index="{{ $index }}"
             data-cid="{{ $cid }}"
             data-url="{{ $url }}"
             data-title="{{ $title }}">

            {{-- Blurred background --}}
            @if($imgLg)
            <div class="shorts-bg" style="background-image:url('{{ $imgLg }}')"></div>
            @endif

            {{-- Video player area --}}
            <div class="shorts-player-wrap">
                <div class="shorts-player-box">
                    <iframe class="shorts-iframe"
                            data-src="https://www.dmm.co.jp/litevideo/-/part/=/affi_id={{ config('fanza.affiliate_id') }}/cid={{ $cid }}/size=1280_720/"
                            frameborder="0"
                            allow="autoplay; fullscreen"
                            allowfullscreen
                            scrolling="no"></iframe>
                    <div class="shorts-thumb-placeholder" data-bg="{{ $imgLg }}">
                        <img src="{{ $imgLg }}" alt="{{ $title }}" loading="lazy">
                        <div class="shorts-play-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info panel（動画の直下） --}}
            <div class="shorts-info-overlay">
                <div class="shorts-info-text">
                    <h3 class="shorts-title-text">{{ $title }}</h3>

                    @if(!empty($actresses))
                    <div class="shorts-actress-row">
                        @foreach($actresses as $actress)
                            @if(!empty($actress['id']))
                            <a href="{{ route('actress.show', $actress['id']) }}"
                               class="shorts-actress-link"
                               title="{{ $actress['name'] }}">{{ $actress['name'] }}</a>
                            @else
                            <span class="shorts-actress-badge">{{ $actress['name'] }}</span>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    @if($maker || $rating)
                    <div class="shorts-meta-row">
                        @if($maker)<span class="shorts-maker-badge">{{ Str::limit($maker, 24) }}</span>@endif
                        @if($rating)<span class="shorts-rating-badge">★ {{ $rating }}</span>@endif
                    </div>
                    @endif

                    @if(!empty($genres))
                    <div class="shorts-genres">
                        @foreach($genres as $genre)
                        <span class="shorts-genre-tag">{{ $genre['name'] }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if($price)
                    <p class="shorts-price-text">{{ preg_replace('/~$/', '円〜', $price) }}{{ str_ends_with($price, '~') ? '' : '円' }}</p>
                    @endif
                </div>

                <a href="{{ $url }}"
                   target="_blank"
                   rel="nofollow noopener"
                   class="shorts-cta-btn">FANZAで<br>詳細を見る →</a>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Navigation buttons --}}
    <div class="shorts-nav-btns" id="shortsNavBtns">
        <button class="shorts-nav-up" id="shortsNavUp" aria-label="前の動画">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"/>
            </svg>
        </button>
        <button class="shorts-nav-down" id="shortsNavDown" aria-label="次の動画">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>
    </div>

    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var wrapper     = document.getElementById('shortsWrapper');
    var scroll      = document.getElementById('shortsScroll');
    if (!scroll) return;

    document.body.classList.add('shorts-page');

    var items       = Array.from(scroll.querySelectorAll('.shorts-item'));
    var navUp       = document.getElementById('shortsNavUp');
    var navDown     = document.getElementById('shortsNavDown');
    var counterEl   = document.getElementById('shortsCounterCurrent');
    var currentIdx  = 0;

    /* ---------- Lazy load iframes via IntersectionObserver ---------- */
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            var item    = entry.target;
            var iframe  = item.querySelector('.shorts-iframe');
            var thumb   = item.querySelector('.shorts-thumb-placeholder');
            var idx     = parseInt(item.dataset.index, 10);

            if (entry.isIntersecting) {
                currentIdx = idx;
                if (counterEl) counterEl.textContent = idx + 1;

                // Load iframe
                if (iframe && iframe.dataset.src && iframe.src !== iframe.dataset.src) {
                    iframe.src = iframe.dataset.src;
                }
                if (thumb) thumb.style.display = 'none';
                updateNavButtons();
            } else {
                // Unload iframe to stop video & save resources
                if (iframe && iframe.src) {
                    iframe.src = 'about:blank';
                }
                if (thumb) thumb.style.display = '';
            }
        });
    }, { threshold: 0.6 });

    items.forEach(function (item) { observer.observe(item); });

    /* ---------- Navigate ---------- */
    function goTo(idx) {
        if (idx < 0 || idx >= items.length) return;
        items[idx].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateNavButtons() {
        if (navUp)   navUp.disabled   = currentIdx <= 0;
        if (navDown) navDown.disabled = currentIdx >= items.length - 1;
    }

    if (navUp)   navUp.addEventListener('click',   function () { goTo(currentIdx - 1); });
    if (navDown) navDown.addEventListener('click', function () { goTo(currentIdx + 1); });

    /* ---------- Keyboard ---------- */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { e.preventDefault(); goTo(currentIdx + 1); }
        if (e.key === 'ArrowUp'   || e.key === 'ArrowLeft')  { e.preventDefault(); goTo(currentIdx - 1); }
    });

    /* ---------- Touch swipe ---------- */
    var touchStartY = 0;
    scroll.addEventListener('touchstart', function (e) {
        touchStartY = e.changedTouches[0].clientY;
    }, { passive: true });
    scroll.addEventListener('touchend', function (e) {
        var dy = touchStartY - e.changedTouches[0].clientY;
        if (Math.abs(dy) > 50) {
            goTo(dy > 0 ? currentIdx + 1 : currentIdx - 1);
        }
    }, { passive: true });

    updateNavButtons();
})();
</script>
@endpush
