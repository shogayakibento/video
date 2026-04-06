@extends('admin.layout')
@section('title', 'MGS作品登録')

@section('content')
    <h1 class="text-2xl font-bold mb-2">MGS作品登録</h1>
    <p class="text-gray-500 text-sm mb-6">MGStageの品番を入力してください。タイトル・女優名・サムネ・サンプル動画URLを自動取得します。</p>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('admin.mgs-add.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        MGS品番 <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="product_code"
                           value="{{ old('product_code') }}"
                           required
                           autofocus
                           placeholder="例: abf-301, sis-001"
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-pink-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">ハイフンあり・なし両方OK（例: abf-301 / abf301）</p>
                </div>
            </div>
            <button type="submit"
                    class="mt-6 w-full bg-pink-600 hover:bg-pink-700 text-white font-bold px-8 py-3 rounded transition">
                登録する
            </button>
        </form>
    </div>

    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-lg text-sm text-yellow-800">
        <p class="font-bold mb-1">登録内容</p>
        <ul class="list-disc list-inside space-y-1">
            <li>タイトル・女優名・メーカーをMGSページから自動取得</li>
            <li>サムネイル画像URLを自動生成</li>
            <li>サンプル動画URLを自動取得（取得できない場合はサムネのみ）</li>
            <li>MGS_AFFILIATE_IDが.envに設定されていると自動でアフィリンクに変換</li>
        </ul>
    </div>
@endsection
