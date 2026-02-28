<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Xで話題のAVランキング - FanzaGate')</title>
    <meta name="description" content="@yield('description', 'X(Twitter)でいいねが多い話題のFANZA動画をランキング形式で紹介。')">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a1a2e',
                        secondary: '#16213e',
                        accent: '#e94560',
                        dark: '#0f0f23',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-dark text-gray-200 min-h-screen">
    <header class="bg-primary border-b border-gray-700 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ route('tweet.ranking.index') }}" class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span>話題のAVランキング</span>
                </a>
                <nav class="flex gap-4 text-sm flex-wrap">
                    <a href="{{ route('tweet.ranking.index') }}" class="hover:text-accent transition {{ request()->routeIs('tweet.ranking.index') ? 'text-accent' : '' }}">ランキング</a>
                    <a href="{{ route('tweet.ranking.popular-tweets') }}" class="hover:text-accent transition {{ request()->routeIs('tweet.ranking.popular-tweets') ? 'text-accent' : '' }}">話題のツイート</a>
                    <a href="{{ route('tweet.ranking.latest') }}" class="hover:text-accent transition {{ request()->routeIs('tweet.ranking.latest') ? 'text-accent' : '' }}">新着</a>
                    <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-300 transition text-xs border border-gray-600 px-2 py-0.5 rounded">FanzaGate</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <footer class="bg-primary border-t border-gray-700 mt-12">
        <div class="max-w-6xl mx-auto px-4 py-6 text-center text-gray-500 text-sm">
            <p>当サイトはアフィリエイト広告を利用しています。18歳未満の方の閲覧はご遠慮ください。</p>
            <p class="mt-2">&copy; {{ date('Y') }} FanzaGate</p>
        </div>
    </footer>
</body>
</html>
