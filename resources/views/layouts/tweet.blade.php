<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'X(Twitter)バズりFANZA動画ランキング - FanzaGate')</title>
    <meta name="description" content="@yield('description', 'X(Twitter)でいいね数が多くバズったFANZA動画を毎日更新でランキング。今SNSで話題の人気AV作品をチェック！')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script>
        tailwind = {
            config: {
                corePlugins: { preflight: false, container: false },
                theme: {
                    extend: {
                        colors: {
                            primary: '#0a0a1e',
                            secondary: '#0f0f28',
                            accent: '#ff2d78',
                            purple: '#9b5df5',
                            dark: '#05050f',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Noto Sans JP', 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            background: #05050f;
            background-image:
                radial-gradient(ellipse at 15% 15%, rgba(155, 93, 245, 0.09) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 80%, rgba(255, 45, 120, 0.07) 0%, transparent 55%);
            background-attachment: fixed;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ページH1タイトル */
        .page-title {
            background: linear-gradient(135deg, #ffe0f0 0%, #ff2d78 50%, #9b5df5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        .page-title::after {
            content: '';
            display: block;
            height: 3px;
            margin-top: 6px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #ff2d78, #9b5df5, transparent);
        }

        /* ビデオカード - ガラスエフェクト */
        .video-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: border-color 0.35s ease, transform 0.35s ease, box-shadow 0.35s ease;
        }
        .video-card:hover {
            border-color: rgba(255, 45, 120, 0.45);
            transform: translateY(-4px);
            box-shadow:
                0 16px 48px rgba(255, 45, 120, 0.15),
                0 0 0 1px rgba(255, 45, 120, 0.2);
        }

        /* ランクバッジ */
        .rank-1 { background: linear-gradient(135deg, #ffd700, #ff8c00); box-shadow: 0 0 14px rgba(255, 165, 0, 0.6); }
        .rank-2 { background: linear-gradient(135deg, #e8e8e8, #a0a0a0); box-shadow: 0 0 10px rgba(180, 180, 180, 0.5); }
        .rank-3 { background: linear-gradient(135deg, #d4854a, #a0522d); box-shadow: 0 0 10px rgba(205, 127, 50, 0.5); }
        .rank-other { background: rgba(80, 80, 100, 0.7); }

        /* フィルターピル */
        .filter-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #a0a0bc;
            transition: all 0.2s ease;
        }
        .filter-pill:hover {
            background: rgba(255, 45, 120, 0.1);
            border-color: rgba(255, 45, 120, 0.35);
            color: #fff;
        }
        .filter-pill-active {
            background: linear-gradient(135deg, #ff2d78, #9b5df5);
            border: 1px solid transparent;
            color: #fff;
            box-shadow: 0 0 14px rgba(255, 45, 120, 0.45);
        }

        /* FANZAボタン */
        .fanza-btn {
            background: linear-gradient(135deg, #ff2d78, #ff1493);
            transition: all 0.25s ease;
            box-shadow: 0 2px 10px rgba(255, 45, 120, 0.35);
        }
        .fanza-btn:hover {
            background: linear-gradient(135deg, #ff1493, #9b5df5);
            box-shadow: 0 4px 18px rgba(255, 45, 120, 0.55);
            transform: translateY(-1px);
        }

        /* フッター */
        .footer-glass {
            background: rgba(5, 5, 20, 0.55);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* スクロールバー */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #05050f; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #ff2d78, #9b5df5);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="text-gray-200 min-h-screen">
    {{-- Header (FanzaGate共通) --}}
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
                    <li><a href="{{ route('tweet.ranking.index') }}" class="nav-link nav-link-buzz {{ request()->routeIs('tweet.ranking.*') || request()->routeIs('tweet.video.*') ? 'active' : '' }}">X バズりランキング</a></li>
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
        <a href="{{ route('tweet.ranking.index') }}" class="nav-link-buzz">X バズりランキング</a>
        <a href="{{ route('genre.index') }}">ジャンル</a>
        <a href="{{ route('actress.index') }}">女優</a>
    </div>

    <main class="max-w-6xl mx-auto px-4 py-8" style="padding-top: calc(64px + 2rem);">
        @yield('content')
    </main>

    <footer class="footer-glass mt-16">
        <div class="max-w-6xl mx-auto px-4 py-8 text-center text-gray-600 text-xs">
            <p>当サイトはアフィリエイト広告を利用しています。18歳未満の方の閲覧はご遠慮ください。</p>
            <p class="mt-2">&copy; {{ date('Y') }} FanzaGate</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
