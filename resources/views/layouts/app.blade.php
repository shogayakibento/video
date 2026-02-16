<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FanzaGate - 人気作品ランキング＆レビューガイド')</title>
    <meta name="description" content="@yield('description', 'FANZAの人気作品ランキング、新着情報、レビューをお届け。動画・VR・DVD・コミックの最新おすすめ作品を毎日更新。')">
    <meta name="keywords" content="@yield('keywords', 'FANZA, 動画, VR, DVD, コミック, ランキング, レビュー, おすすめ')">
    <meta property="og:title" content="@yield('og_title', 'FanzaGate - 人気作品ランキング＆レビューガイド')">
    <meta property="og:description" content="@yield('og_description', 'FANZAの人気作品ランキング、新着情報、レビューをお届け。')">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
    </div>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

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
