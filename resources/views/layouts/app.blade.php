<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FANZA人気作品ランキング・新着動画まとめ | FanzaGate')</title>
    <meta name="description" content="@yield('description', 'FANZAの人気ランキング・新着動画・VR・DVDを毎日更新。X(Twitter)でバズった話題作もチェックできるFANZA専門ガイドサイト。')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    @php
        $ogTitle = $__env->hasSection('og_title')
            ? $__env->yieldContent('og_title')
            : $__env->yieldContent('title', 'FANZA人気作品ランキング・新着動画まとめ | FanzaGate');
        $ogDesc = $__env->hasSection('og_description')
            ? $__env->yieldContent('og_description')
            : $__env->yieldContent('description', 'FANZAの人気ランキング・新着動画・VR・DVDを毎日更新。X(Twitter)でバズった話題作もチェック！');

        // canonical: ビュー側で @section('canonical', '...') を定義した場合はそちらを優先。
        // それ以外は sort を除去し、page（>1）と category のみ保持して重複コンテンツを防ぐ。
        if ($__env->hasSection('canonical')) {
            $canonicalUrl = $__env->yieldContent('canonical');
        } else {
            $canonicalBase = request()->url();
            $canonicalParams = [];
            if (request()->has('page') && (int) request()->get('page') > 1) {
                $canonicalParams['page'] = (int) request()->get('page');
            }
            if (request()->has('category')) {
                $canonicalParams['category'] = request()->get('category');
            }
            $canonicalUrl = $canonicalParams
                ? $canonicalBase . '?' . http_build_query($canonicalParams)
                : $canonicalBase;
        }

        // og:image を絶対URLに正規化
        $ogImage = $__env->hasSection('og_image')
            ? $__env->yieldContent('og_image')
            : asset('images/og-default.webp');
        if ($ogImage && !preg_match('/^https?:\/\//', $ogImage)) {
            $ogImage = url($ogImage);
        }
    @endphp
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDesc }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogTitle }}">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')">
    <meta property="og:image:height" content="@yield('og_image_height', '630')">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="FanzaGate">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@FanzaGate">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="ja" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://widget-view.dmm.co.jp">
    <link rel="dns-prefetch" href="https://pics.dmm.co.jp">
    <link rel="dns-prefetch" href="https://www.dmm.co.jp">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('head_links')
    @stack('styles')
</head>
<body>
    <script>window.appUrl = '{{ url('/') }}';</script>
    {{-- Header --}}
    <header class="header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-icon">F</span>
                <span class="logo-text">FanzaGate</span>
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">ホーム</a></li>
                    @foreach(config('fanza.categories') as $slug => $cat)
                        <li><a href="{{ route('category.show', $slug) }}" class="nav-link {{ request()->is('category/'.$slug) ? 'active' : '' }}">{{ $cat['label'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('genre.index') }}" class="nav-link {{ request()->routeIs('genre.*') ? 'active' : '' }}">ジャンル</a></li>
                    <li><a href="{{ route('actress.index') }}" class="nav-link {{ request()->routeIs('actress.*') ? 'active' : '' }}">女優</a></li>
                    <li><a href="{{ route('mgs.index') }}" class="nav-link nav-link-mgs {{ request()->routeIs('mgs.*') ? 'active' : '' }}">MGS</a></li>
                    <li><a href="{{ route('tweet.ranking.index') }}" class="nav-link nav-link-buzz {{ request()->routeIs('tweet.ranking.*') || request()->routeIs('tweet.video.*') ? 'active' : '' }}">X バズりランキング</a></li>
                    <li><a href="{{ route('shorts') }}" class="nav-link nav-link-shorts {{ request()->routeIs('shorts') ? 'active' : '' }}">▶ ショートビュー</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" aria-label="メニュー" id="menuToggle">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    {{-- Mobile Nav --}}
    <div class="mobile-nav-overlay" id="mobileOverlay"></div>
    <div class="mobile-nav" id="mobileNav">
        <a href="{{ route('home') }}">ホーム</a>
        @foreach(config('fanza.categories') as $slug => $cat)
            <a href="{{ route('category.show', $slug) }}">{{ $cat['label'] }}</a>
        @endforeach
        <a href="{{ route('genre.index') }}">ジャンル</a>
        <a href="{{ route('actress.index') }}">女優</a>
        <a href="{{ route('mgs.index') }}" class="nav-link-mgs">MGS</a>
        <a href="{{ route('tweet.ranking.index') }}" class="nav-link-buzz">X バズりランキング</a>
        <a href="{{ route('shorts') }}" class="nav-link-shorts">▶ ショートビュー</a>
    </div>

    {{-- Side Ads (300x250 FANZA Widget Banners) --}}
    <div class="side-ad side-ad-left" id="sideAdLeft">
        <div class="side-ad-inner">
            <div class="side-ad-label">AD</div>
            <ins class="widget-banner"></ins>
            <script class="widget-banner-script" src="https://widget-view.dmm.co.jp/js/banner_placement.js?affiliate_id=xlikeranking-001&banner_id=1846_300_250"></script>
            <div class="side-ad-spacer"></div>
            <ins class="widget-banner"></ins>
            <script class="widget-banner-script" src="https://widget-view.dmm.co.jp/js/banner_placement.js?affiliate_id=xlikeranking-001&banner_id=1701_300_250"></script>
        </div>
    </div>
    <div class="side-ad side-ad-right" id="sideAdRight">
        <div class="side-ad-inner">
            <div class="side-ad-label">AD</div>
            <ins class="widget-banner"></ins>
            <script class="widget-banner-script" src="https://widget-view.dmm.co.jp/js/banner_placement.js?affiliate_id=xlikeranking-001&banner_id=1829_300_250"></script>
            <div class="side-ad-spacer"></div>
            <ins class="widget-banner"></ins>
            <script class="widget-banner-script" src="https://widget-view.dmm.co.jp/js/banner_placement.js?affiliate_id=xlikeranking-001&banner_id=1523_300_250"></script>
        </div>
    </div>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Pre-Footer Ad --}}
    @include('partials.ad-inline', ['bannerId' => '1842_640_100'])

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="{{ route('home') }}" class="footer-logo">
                        <span class="logo-icon">F</span>
                        <span class="logo-text">FanzaGate</span>
                    </a>
                    <p class="footer-desc">FANZAの人気作品ランキング、新着情報、レビューをお届けするガイドサイト。あなたにぴったりの作品をご紹介します。</p>
                </div>
                <div class="footer-col">
                    <h4>カテゴリ</h4>
                    <ul>
                        @foreach(config('fanza.categories') as $slug => $cat)
                            <li><a href="{{ route('category.show', $slug) }}">{{ $cat['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>メニュー</h4>
                    <ul>
                        <li><a href="{{ route('tweet.ranking.index') }}">Xバズりランキング</a></li>
                        <li><a href="{{ route('genre.index') }}">ジャンル</a></li>
                        <li><a href="{{ route('actress.index') }}">女優</a></li>
                        <li><a href="{{ route('search') }}">検索</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>サイトについて</h4>
                    <ul>
                        <li><a href="{{ route('privacy') }}">プライバシーポリシー</a></li>
                        <li><a href="{{ route('contact') }}">お問い合わせ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} FanzaGate. ※当サイトはアフィリエイトプログラムに参加しています。</p>
            </div>
        </div>
    </footer>

    @include('partials.sample-modal')
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "FanzaGate",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": {
                "@@type": "EntryPoint",
                "urlTemplate": "{{ route('search') }}?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @stack('scripts')
</body>
</html>
