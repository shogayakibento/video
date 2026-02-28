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
        <form method="POST" action="{{ route('admin.video.update-likes', $video) }}" class="flex items-end gap-4 flex-wrap">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">いいね数</label>
                <input type="number" name="total_likes" value="{{ $video->total_likes }}" min="0"
                       class="digit-picker-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                <input type="number" name="total_retweets" value="{{ $video->total_retweets }}" min="0"
                       class="digit-picker-input">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded transition">
                更新
            </button>
        </form>
    </div>

    {{-- ツイート紐付け --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold mb-2">ツイートを紐付ける（任意）</h2>
        <p class="text-gray-400 text-sm mb-4">いいね数・RT数を入力して登録してください。</p>
        <form method="POST" action="{{ route('admin.tweet.store', $video) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">いいね数 <span class="text-red-500">*</span></label>
                    <input type="number" name="like_count" value="{{ old('like_count', 0) }}" min="0"
                           class="digit-picker-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                    <input type="number" name="retweet_count" value="{{ old('retweet_count', 0) }}" min="0"
                           class="digit-picker-input">
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
                                <span class="text-gray-400 text-xs">手動登録</span>
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

@push('scripts')
<style>
    .digit-picker { display: flex; align-items: flex-end; gap: 2px; }
    .digit-picker .digit-unit { text-align: center; }
    .digit-picker .digit-label { font-size: 0.65rem; color: #9ca3af; line-height: 1; margin-bottom: 2px; }
    .digit-picker select {
        display: block; width: 2.25rem; padding: 6px 0;
        border: 1px solid #d1d5db; border-radius: 4px;
        text-align: center; font-size: 1rem; font-family: monospace;
        cursor: pointer; background: #fff;
        appearance: none; -webkit-appearance: none;
    }
    .digit-picker select:hover { border-color: #93c5fd; }
    .digit-picker select:focus { border-color: #3b82f6; outline: none; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input.digit-picker-input').forEach(function (input) {
            var name = input.name;
            var initVal = parseInt(input.value) || 0;
            var units = ['万', '千', '百', '十', '一'];
            var valStr = Math.min(initVal, 99999).toString().padStart(5, '0');

            var picker = document.createElement('div');
            picker.className = 'digit-picker';

            var selects = units.map(function (unit, i) {
                var div = document.createElement('div');
                div.className = 'digit-unit';

                var label = document.createElement('div');
                label.className = 'digit-label';
                label.textContent = unit;

                var sel = document.createElement('select');
                for (var d = 0; d <= 9; d++) {
                    var opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    sel.appendChild(opt);
                }
                sel.value = parseInt(valStr[i]);

                div.appendChild(label);
                div.appendChild(sel);
                picker.appendChild(div);
                return sel;
            });

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = initVal;
            picker.appendChild(hidden);

            selects.forEach(function (sel) {
                sel.addEventListener('change', function () {
                    hidden.value = parseInt(selects.map(function (s) { return s.value; }).join('')) || 0;
                });
            });

            input.parentNode.replaceChild(picker, input);
        });
    });
</script>
@endpush
