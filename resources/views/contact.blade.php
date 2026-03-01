@extends('layouts.app')

@section('title', 'お問い合わせ - FanzaGate')
@section('description', 'FanzaGateへのお問い合わせはXアカウントよりお願いします。')
@section('robots', 'noindex, follow')

@section('content')
<div class="page-header">
    <div class="container">
        <h1>お問い合わせ</h1>
    </div>
</div>

<div class="container">
    @include('partials.breadcrumb', ['items' => [
        ['label' => 'ホーム', 'url' => route('home')],
        ['label' => 'お問い合わせ'],
    ]])

    <div class="static-page">
        <section class="static-section">
            <h2>お問い合わせ方法</h2>
            <p>当サイトへのお問い合わせは、X（旧Twitter）のDMにてお受けしています。</p>
            <p>誤情報のご指摘・掲載に関するご要望など、お気軽にご連絡ください。</p>

            <div class="contact-x-link">
                <a href="https://x.com/owstrategy" target="_blank" rel="noopener noreferrer" class="btn-x-contact">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="x-icon" aria-hidden="true">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    @owstrategy にDMを送る
                </a>
            </div>
        </section>

        <section class="static-section">
            <h2>ご注意</h2>
            <ul class="static-list">
                <li>返信までお時間をいただく場合があります</li>
                <li>内容によってはご返答できない場合があります</li>
                <li>作品の購入・視聴に関するお問い合わせはFANZA公式サポートにお問い合わせください</li>
            </ul>
        </section>
    </div>
</div>
@endsection
