@extends('layouts.app')

@section('title', 'サンプル動画ショートビュー | FanzaGate')
@section('description', 'FANZAの人気作品サンプル動画をショート動画感覚でサクサク視聴。スワイプで次々に楽しめます。')

@push('styles')
<style>
body.shorts-page footer,
body.shorts-page .side-ad { display: none !important; }

/* ====== 動画 + 情報を1枚カードとしてまとめるラッパー ====== */
.shorts-content-wrap {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
    /* 横幅: viewport高さの 60% × 16/9 で動画高さを制限 */
    width: min(
        calc(100% - 80px),
        calc((100vh - 64px) * 0.60 * 16 / 9)
    );
    max-width: 1400px;
    display: flex;
    flex-direction: column;
}

/* プレイヤー: content-wrap 内では relative に戻す */
.shorts-content-wrap .shorts-player-wrap {
    position: relative;
    top: auto;
    left: auto;
    transform: none;
    width: 100%;
    max-width: none;
}

/* 動画の底角は角丸なし（info-panel に接続） */
.shorts-content-wrap .shorts-player-box {
    border-radius: 14px 14px 0 0;
}

/* ====== 情報パネル（動画の直下） ====== */
.shorts-info-panel {
    background: rgba(14, 14, 14, 0.92);
    backdrop-filter: blur(16px);
    border-radius: 0 0 14px 14px;
    padding: 12px 14px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.shorts-info-text {
    flex: 1;
    min-width: 0;
}

/* タイトル */
.shorts-title-text {
    font-size: 0.88rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.4;
    margin: 0 0 6px;
    padding-left: 9px;
    border-left: 3px solid var(--primary);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 女優リンク */
.shorts-actress-row {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 5px;
}

.shorts-actress-link {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #fff;
    background: rgba(255, 45, 120, 0.2);
    border: 1px solid rgba(255, 45, 120, 0.4);
    padding: 2px 9px;
    border-radius: 9999px;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s;
}

.shorts-actress-link::before {
    content: "♀";
    font-size: 0.62rem;
    opacity: 0.75;
}

.shorts-actress-link:hover {
    background: rgba(255, 45, 120, 0.45);
    border-color: rgba(255, 45, 120, 0.75);
    color: #fff;
}

.shorts-actress-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 2px 9px;
    border-radius: 9999px;
}

/* メーカー・評価・ジャンル */
.shorts-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px;
    margin-bottom: 4px;
}

.shorts-maker-badge {
    font-size: 0.68rem;
    color: rgba(255, 255, 255, 0.55);
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1px 7px;
    border-radius: 4px;
}

.shorts-rating-badge {
    font-size: 0.72rem;
    font-weight: 700;
    color: #fbbf24;
}

.shorts-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-bottom: 4px;
}

.shorts-genre-tag {
    font-size: 0.63rem;
    color: rgba(255, 255, 255, 0.45);
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 1px 6px;
    border-radius: 3px;
}

.shorts-price-text {
    font-size: 0.78rem;
    font-weight: 700;
    color: #f9a8d4;
    margin: 0;
}

/* CTAボタン（info-panel 右側） */
.shorts-cta-btn {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    padding: 10px 15px;
    background: linear-gradient(135deg, var(--primary) 0%, #ff6b9d 100%);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 3px 16px rgba(255, 45, 120, 0.55);
    transition: box-shadow 0.2s, transform 0.15s;
    line-height: 1.3;
    text-align: center;
}

.shorts-cta-btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 5px 24px rgba(255, 45, 120, 0.75);
    color: #fff;
}

/* モバイル */
@media (max-width: 640px) {
    .shorts-content-wrap {
        width: 100%;
        top: 48%;
    }
    .shorts-content-wrap .shorts-player-box {
        border-radius: 0;
    }
    .shorts-info-panel {
        border-radius: 0;
        padding: 10px 12px 12px;
        flex-wrap: wrap;
    }
    .shorts-cta-btn {
        width: 100%;
        justify-content: center;
        margin-top: 4px;
    }
}
</style>
@endpush

@section('content')

<div class="shorts-wrapper" id="shortsWrapper">

    @if(empty($items))
        <div class="shorts-empty">
            <p>現在サンプル動画が取得できませんでした。</p>
            <a href="{{ route('home') }}" style="color:var(--primary)">トップへ戻る</a>
        </div>
    @else

    {{-- カウンター --}}
    <div class="shorts-counter-bar" id="shortsCounterBar">
        <span id="shortsCounterCurrent">1</span><span class="shorts-counter-sep">/</span><span>{{ count($items) }}</span>
    </div>

    {{-- ナビボタン --}}
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

    <div class="shorts-scroll" id="shortsScroll">
        @foreach($items as $index => $item)
        @php
            $title     = $item['title'] ?? '';
            $url       = $item['URL'] ?? '#';
            $imgLg     = $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? $item['imageURL']['list'] ?? '';
            $actresses = $item['iteminfo']['actress'] ?? [];
            $maker     = $item['iteminfo']['maker'][0]['name'] ?? null;
            $rating    = $item['review']['average'] ?? null;
            $genres    = array_slice($item['iteminfo']['genre'] ?? [], 0, 3);
            $cid       = $item['content_id'];
            $price     = $item['prices']['price'] ?? null;
        @endphp
        <div class="shorts-item"
             data-index="{{ $index }}"
             data-cid="{{ $cid }}"
             data-url="{{ $url }}"
             data-title="{{ $title }}">

            @if($imgLg)
            <div class="shorts-bg" style="background-image:url('{{ $imgLg }}')"></div>
            @endif

            {{-- 動画 + 情報パネルをまとめたカード --}}
            <div class="shorts-content-wrap">

                {{-- プレイヤー --}}
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

                {{-- 情報パネル（動画の直下） --}}
                <div class="shorts-info-panel">
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
                        <p class="shorts-price-text">{{ $price }}円</p>
                        @endif
                    </div>

                    <a href="{{ $url }}"
                       target="_blank"
                       rel="nofollow noopener"
                       class="shorts-cta-btn">FANZAで<br>詳細を見る &rarr;</a>
                </div>

            </div>{{-- /.shorts-content-wrap --}}

        </div>
        @endforeach
    </div>

    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var scroll = document.getElementById('shortsScroll');
    if (!scroll) return;

    document.body.classList.add('shorts-page');

    var items      = Array.from(scroll.querySelectorAll('.shorts-item'));
    var navUp      = document.getElementById('shortsNavUp');
    var navDown    = document.getElementById('shortsNavDown');
    var counterEl  = document.getElementById('shortsCounterCurrent');
    var currentIdx = 0;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            var item   = entry.target;
            var iframe = item.querySelector('.shorts-iframe');
            var thumb  = item.querySelector('.shorts-thumb-placeholder');
            var idx    = parseInt(item.dataset.index, 10);

            if (entry.isIntersecting) {
                currentIdx = idx;
                if (counterEl) counterEl.textContent = idx + 1;
                if (iframe && iframe.dataset.src && iframe.src !== iframe.dataset.src) {
                    iframe.src = iframe.dataset.src;
                }
                if (thumb) thumb.style.display = 'none';
                updateNav();
            } else {
                if (iframe && iframe.src) iframe.src = 'about:blank';
                if (thumb) thumb.style.display = '';
            }
        });
    }, { threshold: 0.6 });

    items.forEach(function (item) { observer.observe(item); });

    function goTo(idx) {
        if (idx < 0 || idx >= items.length) return;
        items[idx].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateNav() {
        if (navUp)   navUp.disabled   = currentIdx <= 0;
        if (navDown) navDown.disabled = currentIdx >= items.length - 1;
    }

    if (navUp)   navUp.addEventListener('click',   function () { goTo(currentIdx - 1); });
    if (navDown) navDown.addEventListener('click', function () { goTo(currentIdx + 1); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { e.preventDefault(); goTo(currentIdx + 1); }
        if (e.key === 'ArrowUp'   || e.key === 'ArrowLeft')  { e.preventDefault(); goTo(currentIdx - 1); }
    });

    var touchStartY = 0;
    scroll.addEventListener('touchstart', function (e) {
        touchStartY = e.changedTouches[0].clientY;
    }, { passive: true });
    scroll.addEventListener('touchend', function (e) {
        var dy = touchStartY - e.changedTouches[0].clientY;
        if (Math.abs(dy) > 50) goTo(dy > 0 ? currentIdx + 1 : currentIdx - 1);
    }, { passive: true });

    updateNav();
})();
</script>
@endpush
