# FanzaGate - FANZAアフィリエイトサイト (Laravel)

FANZAの人気作品ランキング、新着情報、レビューをお届けするアフィリエイトガイドサイトです。

## 技術スタック

- **Laravel** - PHPフレームワーク
- **FANZA (DMM) Web API v3** - 商品データ取得
- **SQLite** - データベース（キャッシュ・セッション）
- **Blade** - テンプレートエンジン

## セットアップ

### 1. 依存関係のインストール

```bash
composer install
```

### 2. 環境設定

`.env` ファイルをコピーして編集：

```bash
cp .env.example .env
php artisan key:generate
```

### 3. FANZA API設定

`.env` に以下を設定してください：

```
FANZA_API_ID=your_api_id_here
FANZA_AFFILIATE_ID=your_affiliate_id_here
```

> API IDとアフィリエイトIDは [DMMアフィリエイト](https://affiliate.dmm.com/) で取得できます。

### 4. データベース準備

```bash
touch database/database.sqlite
php artisan migrate
```

### 5. 起動

```bash
php artisan serve
```

`http://localhost:8000` でサイトにアクセスできます。

## サイト構成

```
/                   - トップページ（ランキング・新着・VRピックアップ）
/category/douga     - 動画カテゴリ
/category/vr        - VR動画カテゴリ
/category/dvd       - DVDカテゴリ
/category/rental    - レンタルカテゴリ
/category/comic     - コミックカテゴリ
/ranking            - ランキングページ
/search?q=keyword   - 検索ページ
/sitemap.xml        - サイトマップ
```

## 機能

- FANZA (DMM) Web API v3連携
- カテゴリ別作品一覧（動画・VR・DVD・レンタル・コミック）
- 人気ランキング表示
- キーワード検索
- ソート機能（人気順・新着順・レビュー順）
- ページネーション
- レスポンシブデザイン（モバイル対応）
- ダークテーマUI
- SEO最適化（メタタグ・サイトマップ）
- APIレスポンスキャッシュ
- API未設定時のサンプルデータ表示

## デプロイ

### 本番環境の設定

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### キャッシュの最適化

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
