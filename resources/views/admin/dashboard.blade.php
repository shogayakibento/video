@extends('admin.layout')
@section('title', 'ダッシュボード')

@section('content')
    <h1 class="text-2xl font-bold mb-6">ダッシュボード</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">動画数</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($totalVideos) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">ツイート数</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($totalTweets) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">総クリック数</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($totalClicks) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">今日のクリック</p>
            <p class="text-3xl font-bold text-green-600">{{ number_format($todayClicks) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">いいね数 Top 5</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">
                    <th class="py-2">#</th>
                    <th class="py-2">タイトル</th>
                    <th class="py-2">女優</th>
                    <th class="py-2 text-right">いいね</th>
                    <th class="py-2 text-right">クリック</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($topVideos as $i => $video)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 font-bold text-gray-400">{{ $i + 1 }}</td>
                        <td class="py-2">
                            <a href="{{ route('admin.tweet.form', $video) }}" class="text-blue-600 hover:underline">
                                {{ \Illuminate\Support\Str::limit($video->title, 40) }}
                            </a>
                        </td>
                        <td class="py-2 text-gray-500">{{ $video->actress ?: '-' }}</td>
                        <td class="py-2 text-right font-bold text-red-500">{{ number_format($video->total_likes) }}</td>
                        <td class="py-2 text-right">{{ number_format($video->click_count) }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('admin.tweet.form', $video) }}" class="text-blue-500 hover:underline text-xs">ツイート管理</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
