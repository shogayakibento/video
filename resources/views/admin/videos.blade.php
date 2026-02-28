@extends('admin.layout')
@section('title', '動画一覧')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">動画一覧</h1>
    </div>

    <form method="GET" class="mb-4">
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="タイトル・女優名・品番で検索"
                   class="flex-1 px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">検索</button>
            @if(request('search'))
                <a href="{{ route('admin.videos') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition">クリア</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-left text-gray-500">
                    <th class="px-4 py-3">サムネ</th>
                    <th class="px-4 py-3">タイトル</th>
                    <th class="px-4 py-3">女優</th>
                    <th class="px-4 py-3 text-right">いいね数</th>
                    <th class="px-4 py-3 text-right">RT数</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($videos as $video)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <img src="{{ $video->thumbnail_url }}" class="w-20 h-12 object-cover rounded" alt="">
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('tweet.video.show', $video) }}" class="text-blue-600 hover:underline" target="_blank">
                                {{ \Illuminate\Support\Str::limit($video->title, 30) }}
                            </a>
                            <p class="text-gray-400 font-mono text-xs">{{ $video->dmm_content_id }}</p>
                        </td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $video->actress ?: '-' }}</td>
                        <td class="px-4 py-2" colspan="2">
                            <form method="POST" action="{{ route('admin.video.update-likes', $video) }}" class="flex items-center gap-1 justify-end">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="total_likes" value="{{ $video->total_likes }}" min="0"
                                       class="w-20 px-2 py-1 text-right rounded border border-gray-300 text-xs focus:border-blue-500 focus:outline-none">
                                <input type="number" name="total_retweets" value="{{ $video->total_retweets }}" min="0"
                                       class="w-20 px-2 py-1 text-right rounded border border-gray-300 text-xs focus:border-blue-500 focus:outline-none">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 py-1 rounded transition">更新</button>
                            </form>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.tweet.form', $video) }}"
                               class="text-gray-400 hover:text-blue-600 text-xs">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">動画が見つかりません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $videos->withQueryString()->links() }}
    </div>
@endsection
