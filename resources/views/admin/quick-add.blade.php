@extends('admin.layout')
@section('title', 'クイック登録')

@section('content')
    <h1 class="text-2xl font-bold mb-2">クイック登録</h1>
    <p class="text-gray-500 text-sm mb-6">品番といいね数だけでOK。ツイートURLは任意です。DMM APIが設定されていれば動画情報を自動取得します。</p>

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('admin.quick-add.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">FANZA品番 <span class="text-red-500">*</span></label>
                    <input type="text" name="dmm_content_id" value="{{ old('dmm_content_id') }}" required autofocus
                           placeholder="abc00123"
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">いいね数 <span class="text-red-500">*</span></label>
                        <input type="number" name="like_count" value="{{ old('like_count') }}" min="0" required
                               placeholder="5000"
                               class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                        <input type="number" name="retweet_count" value="{{ old('retweet_count') }}" min="0"
                               placeholder="1200"
                               class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ツイートURL（任意）</label>
                    <input type="url" name="tweet_url" value="{{ old('tweet_url') }}"
                           placeholder="https://x.com/..."
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">なくてもいいね数は反映されます</p>
                </div>
            </div>
            <button type="submit" class="mt-6 w-full bg-accent hover:bg-red-600 text-white font-bold px-8 py-3 rounded transition">
                登録する
            </button>
        </form>
    </div>
@endsection
