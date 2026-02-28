<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'X(Twitter)バズりFANZA動画ランキング - FanzaGate')</title>
    <meta name="description" content="@yield('description', 'X(Twitter)でいいね数が多くバズったFANZA動画を毎日更新でランキング。今SNSで話題の人気AV作品をチェック！')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
    </script>
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

        /* ヘッダー - ガラスモーフィズム */
        .header-glass {
            background: rgba(5, 5, 20, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 45, 120, 0.18);
        }

        /* サイトタイトル - シマーアニメーション */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        .site-title {
            background: linear-gradient(
                135deg,
                #ffb3d9 0%,
                #ff2d78 25%,
                #9b5df5 50%,
                #ff2d78 75%,
                #ffb3d9 100%
            );
            background-size: 250% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            animation: shimmer 5s linear infinite;
            filter: drop-shadow(0 0 14px rgba(255, 45, 120, 0.45));
            transition: filter 0.3s ease;
        }
        .site-title:hover {
            filter: drop-shadow(0 0 28px rgba(255, 45, 120, 0.85));
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

        /* ナビリンク */
        .nav-link {
            position: relative;
            padding-bottom: 2px;
            transition: color 0.2s ease;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #ff2d78, #9b5df5);
            transition: width 0.3s ease;
            border-radius: 9999px;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .nav-link.active { color: #ff2d78; }
        .nav-link:not(.active) { color: #b0b0c8; }
        .nav-link:not(.active):hover { color: #ffffff; }

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
<body class="bg-dark text-gray-200 min-h-screen">
    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3.5">
            <div class="flex items-center justify-between">
                <a href="{{ route('tweet.ranking.index') }}" class="text-xl font-bold site-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="-webkit-text-fill-color:initial; filter:none;">
                        <defs>
                            <linearGradient id="playGrad" x1="0" y1="0" x2="1" y2="1" gradientUnits="objectBoundingBox">
                                <stop offset="0%" stop-color="#ff2d78"/>
                                <stop offset="100%" stop-color="#9b5df5"/>
                            </linearGradient>
                        </defs>
                        <circle cx="12" cy="12" r="10" fill="url(#playGrad)" opacity="0.15"/>
                        <circle cx="12" cy="12" r="10" stroke="url(#playGrad)" stroke-width="1.5"/>
                        <path d="M10 8.5L16.5 12L10 15.5V8.5Z" fill="url(#playGrad)"/>
                    </svg>
                    バズりFANZAランキング
                </a>
                <nav class="flex gap-6 text-sm font-medium">
                    <a href="{{ route('tweet.ranking.index') }}" class="nav-link {{ request()->routeIs('tweet.ranking.index') ? 'active' : '' }}">ランキング</a>
                    <a href="{{ route('tweet.ranking.latest') }}" class="nav-link {{ request()->routeIs('tweet.ranking.latest') ? 'active' : '' }}">新着</a>
                    <a href="{{ route('home') }}" class="nav-link text-xs border border-gray-700 px-2 py-0.5 rounded">FanzaGate</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="footer-glass mt-16">
        <div class="max-w-6xl mx-auto px-4 py-8 text-center text-gray-600 text-xs">
            <p>当サイトはアフィリエイト広告を利用しています。18歳未満の方の閲覧はご遠慮ください。</p>
            <p class="mt-2">&copy; {{ date('Y') }} FanzaGate</p>
        </div>
    </footer>
</body>
</html>
