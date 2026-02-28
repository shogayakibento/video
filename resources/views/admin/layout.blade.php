<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面 - @yield('title')</title>
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
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-primary text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="font-bold text-lg">管理画面</a>
                <div class="flex gap-4 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-accent transition {{ request()->routeIs('admin.dashboard') ? 'text-accent' : '' }}">ダッシュボード</a>
                    <a href="{{ route('admin.videos') }}" class="hover:text-accent transition {{ request()->routeIs('admin.videos') ? 'text-accent' : '' }}">動画一覧</a>
                    <a href="{{ route('admin.quick-add') }}" class="hover:text-accent transition {{ request()->routeIs('admin.quick-add') ? 'text-accent' : '' }}">クイック登録</a>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('tweet.ranking.index') }}" class="hover:text-accent transition" target="_blank">サイトを見る</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-accent transition">ログアウト</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
