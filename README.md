# CinemaFind - DMMアフィリエイトサイト

映画・アニメ・ドラマ・ゲームの総合エンタメガイドサイトです。DMMアフィリエイトプログラムと連携しています。

## セットアップ

### 1. アフィリエイトIDの設定

各HTMLファイルの `SITE_CONFIG` にDMMアフィリエイトIDを設定してください：

```javascript
window.SITE_CONFIG = {
  affiliateId: 'YOUR_AFFILIATE_ID',  // ここにあなたのIDを設定
  siteUrl: 'https://your-domain.com'
};
```

対象ファイル:
- `index.html`
- `category/movie.html`
- `category/anime.html`
- `category/drama.html`
- `category/game.html`

### 2. ドメインの設定

`sitemap.xml` と各HTMLファイルの `canonical` URLを実際のドメインに変更してください。

### 3. デプロイ

静的サイトなので、以下のサービスにそのままデプロイできます：

- **GitHub Pages** - リポジトリ設定から有効化
- **Netlify** - リポジトリを接続するだけ
- **Vercel** - リポジトリを接続するだけ
- **Cloudflare Pages** - リポジトリを接続するだけ

## サイト構成

```
/
├── index.html           # トップページ
├── css/
│   └── style.css        # スタイルシート
├── js/
│   └── main.js          # JavaScript
├── category/
│   ├── movie.html       # 映画カテゴリ
│   ├── anime.html       # アニメカテゴリ
│   ├── drama.html       # ドラマカテゴリ
│   └── game.html        # ゲームカテゴリ
├── sitemap.xml          # サイトマップ
├── robots.txt           # クローラー設定
└── README.md
```

## 機能

- レスポンシブデザイン（モバイル対応）
- SEO最適化（メタタグ、サイトマップ、構造化データ）
- カテゴリ別ページ（映画・アニメ・ドラマ・ゲーム）
- おすすめ作品のタブ切り替え
- 人気ランキング表示
- 検索機能（DMMの検索へ連携）
- FAQ（アコーディオン）
- スクロールアニメーション
- モバイルメニュー

## DMMアフィリエイトの始め方

1. [DMMアフィリエイト](https://affiliate.dmm.com/)にアクセス
2. 無料会員登録を行う
3. アフィリエイトIDを取得
4. 本サイトの `SITE_CONFIG` にIDを設定
5. サイトをデプロイして運用開始
