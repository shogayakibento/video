@extends('layouts.app')

@section('title', 'ランキング - FanzaGate')
@section('description', 'FANZAの人気作品ランキング。動画・VR・DVD・コミックのランキングをリアルタイムで更新。')

@section('content')
    <div class="page-header">
        <div class="container">
            <h1>人気ランキング</h1>
            <p>FANZAの人気作品をランキング形式でお届け</p>
        </div>
    </div>

    <div class="container">
        {{-- Category Tabs --}}
        <div class="filter-bar filter-bar-center">
            @foreach($categories as $slug => $cat)
                <a href="{{ route('ranking', ['category' => $slug]) }}"
                   class="tab-btn {{ $activeCategory === $slug ? 'active' : '' }}">
                    {{ $cat['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Ranking List --}}
        <div class="ranking-list-full">
            @forelse($items as $index => $item)
                <div class="ranking-item-full animate-on-scroll">
                    <div class="rank-number rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                    <div class="ranking-item-thumb">
                        @if(!empty($item['imageURL']['list']))
                            <img src="{{ $item['imageURL']['list'] }}" alt="{{ $item['title'] ?? '' }}" loading="lazy">
                        @else
                            <div class="thumb-placeholder">
                                @include('partials.icon', ['icon' => $categories[$activeCategory]['icon'] ?? 'play-circle'])
                            </div>
                        @endif
                    </div>
                    <div class="ranking-item-info">
                        <h3>{{ $item['title'] ?? 'タイトル未設定' }}</h3>
                        <div class="item-meta">
                            @if(!empty($item['iteminfo']['actress']))
                                <span class="meta-tag actress">{{ $item['iteminfo']['actress'][0]['name'] ?? '' }}</span>
                            @endif
                            @if(!empty($item['iteminfo']['maker']))
                                <span class="meta-tag maker">{{ $item['iteminfo']['maker'][0]['name'] ?? '' }}</span>
                            @endif
                            @if(!empty($item['review']['average']))
                                <span class="meta-rating">★ {{ $item['review']['average'] }}</span>
                            @endif
                        </div>
                        @if(!empty($item['iteminfo']['genre']))
                            <div class="item-genres">
                                @foreach(array_slice($item['iteminfo']['genre'], 0, 4) as $genre)
                                    <span class="genre-tag">{{ $genre['name'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="ranking-item-action">
                        <a href="{{ $item['affiliateURL'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="detail-btn">
                            詳細を見る
                        </a>
                        @if(!empty($item['prices']['price']))
                            <span class="price">{{ $item['prices']['price'] }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>ランキングデータを取得できませんでした。</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
