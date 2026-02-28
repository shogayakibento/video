<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ログイン</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 w-full max-w-sm">
        <h1 class="text-white text-xl font-bold mb-6 text-center">管理画面ログイン</h1>

        @if($errors->any())
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-4">
                <label for="password" class="block text-gray-300 text-sm mb-2">パスワード</label>
                <input type="password" name="password" id="password" autofocus
                       class="w-full px-4 py-2 rounded bg-gray-700 text-white border border-gray-600 focus:border-blue-500 focus:outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition">
                ログイン
            </button>
        </form>
    </div>
</body>
</html>
