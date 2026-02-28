@extends('admin.layout')
@section('title', 'ツイート管理')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.videos') }}" class="text-blue-600 hover:underline text-sm">&larr; 動画一覧に戻る</a>
    </div>

    <div class="flex gap-4 items-start mb-6">
        <img src="{{ $video->thumbnail_url }}" class="w-32 h-20 object-cover rounded" alt="">
        <div>
            <h1 class="text-lg font-bold">{{ $video->title }}</h1>
            <p class="text-gray-500 text-sm">{{ $video->actress ?: '不明' }} / {{ $video->dmm_content_id }}</p>
            <p class="text-sm mt-1">
                合計いいね: <span class="font-bold text-red-500">{{ number_format($video->total_likes) }}</span>
                / 合計RT: <span class="font-bold">{{ number_format($video->total_retweets) }}</span>
            </p>
        </div>
    </div>

    {{-- いいね数を直接編集 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold mb-4">いいね数を直接編集</h2>
        <form method="POST" action="{{ route('admin.video.update-likes', $video) }}" class="flex items-end gap-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">いいね数</label>
                <input type="number" name="total_likes" value="{{ $video->total_likes }}" min="0"
                       class="w-40 px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                <input type="number" name="total_retweets" value="{{ $video->total_retweets }}" min="0"
                       class="w-40 px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded transition">
                更新
            </button>
        </form>
    </div>

    {{-- ツイート紐付け --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold mb-2">ツイートを紐付ける（任意）</h2>
        <p class="text-gray-400 text-sm mb-4">ツイートURLがあれば紐付けできます。なくても登録可能です。</p>
        <form method="POST" action="{{ route('admin.tweet.store', $video) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">いいね数 <span class="text-red-500">*</span></label>
                    <input type="number" name="like_count" value="{{ old('like_count', 0) }}" min="0" required
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                    <input type="number" name="retweet_count" value="{{ old('retweet_count', 0) }}" min="0"
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ツイートURL（任意）</label>
                    <input type="url" name="tweet_url" value="{{ old('tweet_url') }}"
                           placeholder="https://x.com/username/status/1234567890"
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-gray-600 hover:bg-gray-700 text-white font-bold px-6 py-2 rounded transition">
                登録する
            </button>
        </form>
    </div>

    {{-- 登録済みツイート一覧 --}}
    @if($video->tweets->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold mb-4">紐付け済みツイート ({{ $video->tweets->count() }}件)</h2>
            <div class="space-y-3">
                @foreach($video->tweets->sortByDesc('like_count') as $tweet)
                    <div class="flex items-center justify-between border-b pb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 text-sm">
                                @if($tweet->tweet_url)
                                    <a href="{{ $tweet->tweet_url }}" target="_blank" class="text-blue-600 hover:underline font-mono text-xs">
                                        {{ $tweet->tweet_url }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">URLなし（手動登録）</span>
                                @endif
                            </div>
                            <div class="flex gap-4 text-xs text-gray-400 mt-1">
                                <span class="text-red-500 font-bold">{{ number_format($tweet->like_count) }} いいね</span>
                                <span>{{ number_format($tweet->retweet_count) }} RT</span>
                                <span>{{ $tweet->created_at->format('Y/m/d H:i') }}</span>
                            </div>
                        </div>
                        <form action="{{ route('admin.tweet.delete', $tweet) }}" method="POST"
                              onsubmit="return confirm('削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs ml-4">削除</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
