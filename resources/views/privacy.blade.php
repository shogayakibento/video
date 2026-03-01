@extends('layouts.app')

@section('title', 'プライバシーポリシー - FanzaGate')
@section('description', 'FanzaGateのプライバシーポリシーです。Cookie・アクセス解析・アフィリエイトリンクに関する情報取り扱い方針を記載しています。')
@section('robots', 'noindex, follow')

@section('content')
<div class="page-header">
    <div class="container">
        <h1>プライバシーポリシー</h1>
    </div>
</div>

<div class="container">
    @include('partials.breadcrumb', ['items' => [
        ['label' => 'ホーム', 'url' => route('home')],
        ['label' => 'プライバシーポリシー'],
    ]])

    <div class="static-page">
        <p class="static-page-updated">最終更新日：{{ date('Y年n月j日') }}</p>

        <section class="static-section">
            <h2>基本方針</h2>
            <p>FanzaGate（以下「当サイト」）は、ユーザーの個人情報の取り扱いについて、以下のとおりプライバシーポリシーを定めます。</p>
        </section>

        <section class="static-section">
            <h2>アクセス解析ツールについて</h2>
            <p>当サイトでは、Googleが提供するアクセス解析ツール「Google Analytics」を使用しています。Google Analyticsはトラフィックデータの収集のためにCookieを使用します。このトラフィックデータは匿名で収集されており、個人を特定するものではありません。</p>
            <p>Cookieを無効にすることで収集を拒否できます。詳細はGoogleのポリシーをご確認ください。</p>
        </section>

        <section class="static-section">
            <h2>Cookieについて</h2>
            <p>当サイトでは、サービス改善のためCookieを使用する場合があります。Cookieはブラウザの設定から無効にすることができます。ただし、無効にした場合、一部機能が正常に動作しない場合があります。</p>
        </section>

        <section class="static-section">
            <h2>アフィリエイトリンクについて</h2>
            <p>当サイトはFANZA（DMM）のアフィリエイトプログラムに参加しています。当サイト内のリンクからFANZAにアクセスし商品を購入された場合、当サイトに報酬が発生することがあります。購入者様への追加費用は一切発生しません。</p>
        </section>

        <section class="static-section">
            <h2>免責事項</h2>
            <p>当サイトに掲載する情報は正確性に努めていますが、その内容を保証するものではありません。当サイトの情報をご利用いただく際は、ご自身の判断と責任のもとでご利用ください。</p>
            <p>当サイトのリンクから遷移した外部サイトのコンテンツ・サービスについて、当サイトは一切の責任を負いません。</p>
        </section>

        <section class="static-section">
            <h2>著作権について</h2>
            <p>当サイトに掲載されている画像・動画情報はFANZA（DMM）より提供されるものです。無断転載・複製はお断りします。</p>
        </section>

        <section class="static-section">
            <h2>プライバシーポリシーの変更</h2>
            <p>当サイトは、必要に応じてプライバシーポリシーを変更することがあります。変更後のポリシーはこのページに掲載した時点から効力を生じるものとします。</p>
        </section>

        <section class="static-section">
            <h2>お問い合わせ</h2>
            <p>当サイトに関するお問い合わせは<a href="{{ route('contact') }}" class="text-accent">お問い合わせページ</a>からお願いします。</p>
        </section>
    </div>
</div>
@endsection
