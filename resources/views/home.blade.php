@extends('layouts.app')

@section('title', 'FanzaGate - 人気作品ランキング＆レビューガイド')
@section('description', 'FANZAの人気作品ランキング、新着情報、レビューをお届け。動画・VR・DVD・コミックの最新おすすめ作品を毎日更新。')

@section('content')
    {{-- Hero Section --}}
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="container hero-content">
            <h1 class="hero-title">あなたの<span class="highlight">お気に入り</span>を見つけよう</h1>
            <p class="hero-subtitle">FANZAの人気作品ランキング・新着情報を毎日更新</p>
            <form action="{{ route('search') }}" method="GET" class="search-box">
                <input type="text" name="q" class="search-input" placeholder="作品名・キーワードで検索..." value="">
                <button type="submit" class="search-btn">検索</button>
            </form>
            <div class="hero-tags">
                <a href="{{ route('search', ['q' => '人気']) }}" class="tag">人気作品</a>
                <a href="{{ route('search', ['q' => '新作']) }}" class="tag">新作</a>
                <a href="{{ route('category.show', 'vr') }}" class="tag">VR動画</a>
                <a href="{{ route('ranking') }}" class="tag">ランキング</a>
            </div>
        </div>
    </section>

    {{-- Categories Section --}}
    <section class="section categories-section">
        <div class="container">
            <h2 class="section-title">カテゴリから探す</h2>
            <div class="categories-grid">
                @foreach($categories as $slug => $cat)
                    <a href="{{ route('category.show', $slug) }}" class="category-card animate-on-scroll">
                        <div class="category-icon">
                            @include('partials.icon', ['icon' => $cat['icon']])
                        </div>
                        <h3>{{ $cat['label'] }}</h3>
                        <p>{{ $cat['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ranking Section --}}
    <section class="section ranking-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">人気ランキング</h2>
                <a href="{{ route('ranking') }}" class="section-link">すべて見る →</a>
            </div>
            <div class="items-grid">
                @foreach(array_slice($rankingItems, 0, 8) as $index => $item)
                    @include('partials.item-card', ['item' => $item, 'rank' => $index + 1])
                @endforeach
            </div>
        </div>
    </section>

    {{-- New Releases Section --}}
    <section class="section new-releases-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">新着作品</h2>
                <a href="{{ route('category.show', 'douga') }}?sort=date" class="section-link">すべて見る →</a>
            </div>
            <div class="releases-slider">
                @foreach(array_slice($newReleaseItems, 0, 12) as $item)
                    @include('partials.release-card', ['item' => $item])
                @endforeach
            </div>
        </div>
    </section>

    {{-- VR Section --}}
    <section class="section vr-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">VR作品ピックアップ</h2>
                <a href="{{ route('category.show', 'vr') }}" class="section-link">すべて見る →</a>
            </div>
            <div class="items-grid cols-3">
                @foreach(array_slice($vrItems, 0, 6) as $index => $item)
                    @include('partials.item-card', ['item' => $item, 'rank' => null])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Benefits Section --}}
    <section class="section benefits-section">
        <div class="container">
            <h2 class="section-title">FANZAの特徴</h2>
            <div class="benefits-grid">
                <div class="benefit-card animate-on-scroll">
                    <div class="benefit-number">01</div>
                    <h3>豊富なコンテンツ</h3>
                    <p>動画、VR、DVD、コミックなど多彩なジャンルの作品が揃っています。あなたの好みに合った作品がきっと見つかります。</p>
                </div>
                <div class="benefit-card animate-on-scroll">
                    <div class="benefit-number">02</div>
                    <h3>高画質配信</h3>
                    <p>フルHD・4K対応の高画質配信で、クリアな映像を楽しめます。VR作品も高品質で没入感抜群です。</p>
                </div>
                <div class="benefit-card animate-on-scroll">
                    <div class="benefit-number">03</div>
                    <h3>マルチデバイス対応</h3>
                    <p>PC、スマートフォン、タブレットなど様々なデバイスで視聴可能。いつでもどこでもお楽しみいただけます。</p>
                </div>
                <div class="benefit-card animate-on-scroll">
                    <div class="benefit-number">04</div>
                    <h3>安心のセキュリティ</h3>
                    <p>大手DMMグループが運営する安全なプラットフォーム。安心してご利用いただけます。</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="section faq-section">
        <div class="container">
            <h2 class="section-title">よくある質問</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <button class="faq-question">
                        FANZAとは？
                        <span class="faq-toggle">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>FANZAは、DMMが運営する大手デジタルコンテンツプラットフォームです。動画配信、VRコンテンツ、DVD販売・レンタル、電子書籍など、幅広いサービスを提供しています。</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        どんなデバイスで見られる？
                        <span class="faq-toggle">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>PC（Windows/Mac）、スマートフォン（iOS/Android）、タブレット、VRヘッドセットなど幅広いデバイスに対応しています。</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        支払い方法は？
                        <span class="faq-toggle">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>クレジットカード（Visa、Mastercard、JCB、American Express）、DMMポイント、電子マネー、キャリア決済に対応しています。</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        このサイトは何？
                        <span class="faq-toggle">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>当サイトはFANZAの作品情報をまとめたガイドサイトです。ランキングや新着情報を通じて、お気に入りの作品を見つけるお手伝いをします。※当サイトはアフィリエイトプログラムに参加しています。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
