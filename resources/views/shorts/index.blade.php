@extends('layouts.app')

@section('title', 'FANZAサンプル動画をショートで楽しむ | FanzaGate')
@section('description', 'FANZAの人気AV・動画サンプルをショート動画感覚でサクサク視聴。スワイプ・矢印キーで次々に楽しめる無料サンプル動画まとめ。')
@section('keywords', 'FANZA, サンプル動画, 無料サンプル, AV, ショート動画, 人気動画, 女優')
@section('og_type', 'website')

@push('styles')
<style>
/* ショートビューページはフッター非表示 */
body.shorts-page footer,
body.shorts-page .side-ad,
body.shorts-page > ins,
body.shorts-page > script[src*="banner_placement"] {
    display: none !important;
}

/* ====== デスクトップ: 左:動画 / 右:情報パネル の2カラムレイアウト ====== */
@media (min-width: 641px) {
    .shorts-item {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
    }

    .shorts-player-wrap {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        transform: none !important;
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
        max-width: none !important;

    /* 中の動画を拡大して枠いっぱいに合わせる（デスクトップ） */
    .shorts-iframe {
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        transform: translateY(10%) scale(1.28) !important;
        transform-origin: center center !important;
    }

        height: 100% !important;
        padding: 0 !important;
    }

    .shorts-player-box {
        padding-bottom: 0 !important;
        width: 100% !important;
        height: 100% !important;
        aspect-ratio: auto !important;
        position: relative !important;
    }

    /* 左カラムのプレイヤー枠いっぱいに動画を合わせる */
    .shorts-iframe {
        /* プレイヤー外の下余白を隠すため、少し上へ寄せて拡大 */
        top: -6% !important;
        height: 108% !important;
        transform: none !important;
    }

    .shorts-info-overlay {
        position: relative !important;
        top: auto !important;
        bottom: auto !important;
        left: auto !important;
        transform: none !important;
        width: 300px !important;
        flex-shrink: 0 !important;
        max-width: none !important;
        border-radius: 0 !important;
        padding: 24px 16px !important;
        background: rgba(12, 12, 12, 0.95) !important;
        backdrop-filter: blur(16px) !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
        justify-content: flex-start !important;
        overflow-y: auto !important;
    }

    .shorts-info-text {
        flex: 1;
    }

    .shorts-cta-btn {
        position: static !important;
        width: 100% !important;
        text-align: center !important;
        justify-content: center !important;
        padding: 10px 14px !important;
        font-size: 0.82rem !important;
        white-space: normal !important;
        line-height: 1.4 !important;
        box-shadow: 0 2px 12px rgba(255, 45, 120, 0.5) !important;
    }
}

.shorts-info-text {
    flex: 1;
    min-width: 0;
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

/* 女優リンク: meta-tag.actress に準拠 */
.shorts-actress-link {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary-light);
    background: rgba(255, 45, 120, 0.15);
    padding: 2px 10px;
    border-radius: 4px;
    text-decoration: none;
    transition: background 0.15s;
    pointer-events: auto;
}
.shorts-actress-link:hover {
    background: rgba(255, 45, 120, 0.3);
    color: var(--primary-light);
}

/* 女優バッジ（リンクなし） */
.shorts-actress-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary-light);
    background: rgba(255, 45, 120, 0.1);
    padding: 2px 10px;
    border-radius: 4px;
}

/* メーカー・評価・ジャンル・価格行 */
.shorts-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-bottom: 5px;
}

/* メーカー: 目立たせない */
.shorts-maker-badge {
    font-size: 0.72rem;
    color: var(--text-muted);
}

/* 評価: 黄色 */
.shorts-rating-badge {
    font-size: 0.82rem;
    font-weight: 700;
    color: #f59e0b;
}

/* Genres */
.shorts-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 5px;
}

/* ジャンル: genre-tag に準拠 */
.shorts-genre-tag {
    font-size: 0.7rem;
    color: var(--text-muted);
    background: var(--bg-light);
    padding: 2px 8px;
    border-radius: 4px;
}

/* 価格: item-price / sample-modal-price に準拠 */
.shorts-price-text {
    font-size: 0.8rem;
    font-weight: 700;
    color: #6ee7b7;
    margin: 0;
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

/* ====== モバイル: 情報パネルを画面下部オーバーレイに ====== */
@media (max-width: 640px) {
    .shorts-info-overlay {
        /* 動画下固定ではなく画面下部へ */
        top: auto !important;
        bottom: env(safe-area-inset-bottom, 0) !important;
        left: 0 !important;
        right: 0 !important;
        transform: none !important;
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 0 !important;
        padding: 60px 14px 14px !important;
        background: linear-gradient(
            to top,
            rgba(0,0,0,0.93) 0%,
            rgba(0,0,0,0.6) 65%,
            transparent 100%
        ) !important;
        backdrop-filter: none !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }

    .shorts-info-text {
        width: 100%;
    }

    .shorts-title-text {
        font-size: 0.88rem !important;
        -webkit-line-clamp: 2 !important;
        display: -webkit-box !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    .shorts-cta-btn {
        position: static !important;
        width: 100% !important;
        justify-content: center !important;
        padding: 10px 16px !important;
        font-size: 0.82rem !important;
        border-radius: 10px !important;
    }

    .shorts-actress-link,
    .shorts-actress-badge {
        font-size: 0.7rem !important;
    }

    .shorts-genres,
    .shorts-meta-row {
        gap: 4px !important;
    }

    .shorts-genre-tag {
        font-size: 0.65rem !important;
        padding: 1px 6px !important;
    }
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
@if(!empty($items))
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "FANZAサンプル動画ショートビュー",
    "description": "FANZAの人気作品サンプル動画をショート動画感覚でサクサク視聴",
    "numberOfItems": {{ count($items) }},
    "itemListElement": [
        @foreach(array_slice($items, 0, 10) as $i => $item)
        @php
            $sTitle = addslashes($item['title'] ?? '');
            $sCid   = $item['content_id'] ?? null;
            $sImg   = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? '';
            $sActresses = $item['iteminfo']['actress'] ?? [];
            $sDate  = $item['date'] ?? null;
            $sActorJson = !empty($sActresses)
                ? collect($sActresses)->map(function($a) { return ['@type' => 'Person', 'name' => $a['name']]; })->values()->toJson()
                : null;
        @endphp
        {
            "@@type": "ListItem",
            "position": {{ $i + 1 }},
            "item": {
                "@@type": "VideoObject",
                "name": "{{ $sTitle }}",
                "thumbnailUrl": "{{ $sImg }}"
                @if($sCid)
                ,"embedUrl": "https://www.dmm.co.jp/litevideo/-/part/=/affi_id={{ config('fanza.affiliate_id') }}/cid={{ $sCid }}/size=1280_720/"
                @endif
                @if($sDate)
                ,"uploadDate": "{{ \Carbon\Carbon::parse($sDate)->format('c') }}"
                @endif
                @if($sActorJson)
                ,"actor": {!! $sActorJson !!}
                @endif
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
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
