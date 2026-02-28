<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FANZA人気作品ランキング・新着動画まとめ | FanzaGate')</title>
    <meta name="description" content="@yield('description', 'FANZAの人気ランキング・新着動画・VR・DVDを毎日更新。X(Twitter)でバズった話題作もチェックできるFANZA専門ガイドサイト。')">
    <meta name="keywords" content="@yield('keywords', 'FANZA, 動画, VR, DVD, コミック, ランキング, 新着, おすすめ, バズり')">
    <meta property="og:title" content="@yield('og_title', 'FANZA人気作品ランキング・新着動画まとめ | FanzaGate')">
    <meta property="og:description" content="@yield('og_description', 'FANZAの人気ランキング・新着動画・VR・DVDを毎日更新。X(Twitter)でバズった話題作もチェック！')">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body>
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
                    <li><a href="{{ route('ranking') }}" class="nav-link {{ request()->routeIs('ranking') ? 'active' : '' }}">ランキング</a></li>
                    <li><a href="{{ route('tweet.ranking.index') }}" class="nav-link {{ request()->routeIs('tweet.ranking.*') || request()->routeIs('tweet.video.*') ? 'active' : '' }}">Xバズりランキング</a></li>
                    <li><a href="{{ route('genre.index') }}" class="nav-link {{ request()->routeIs('genre.*') ? 'active' : '' }}">ジャンル</a></li>
                    <li><a href="{{ route('actress.index') }}" class="nav-link {{ request()->routeIs('actress.*') ? 'active' : '' }}">女優</a></li>
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
        <a href="{{ route('ranking') }}">ランキング</a>
        <a href="{{ route('tweet.ranking.index') }}">Xバズりランキング</a>
        <a href="{{ route('genre.index') }}">ジャンル</a>
        <a href="{{ route('actress.index') }}">女優</a>
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
                        <li><a href="{{ route('ranking') }}">ランキング</a></li>
                        <li><a href="{{ route('tweet.ranking.index') }}">Xバズりランキング</a></li>
                        <li><a href="{{ route('genre.index') }}">ジャンル</a></li>
                        <li><a href="{{ route('actress.index') }}">女優</a></li>
                        <li><a href="{{ route('search') }}">検索</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>サイトについて</h4>
                    <ul>
                        <li><a href="#">プライバシーポリシー</a></li>
                        <li><a href="#">お問い合わせ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} FanzaGate. ※当サイトはアフィリエイトプログラムに参加しています。</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
