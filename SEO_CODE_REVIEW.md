# SEOコードレビュー（2026-03-11）

対象: `/workspace/video`（Laravel）

## 総評

- 基本的なSEO実装（title/description/canonical/OGP/sitemap/robots/noindex制御）はすでに実装されており、土台は良好。
- 一方で、**インデックス最適化**と**重複ページ制御**、**構造化データの網羅性**に改善余地あり。
- 優先度の高い修正は「robots.txtのSitemap URLの環境依存化」「検索・フィルター・ページネーションの正規化ポリシー整理」。

---

## 良い点（現状の強み）

1. **共通レイアウトでメタ情報を集中管理**
   - `title`, `description`, `robots`, OGP, canonical を `layouts/app.blade.php` で一元管理。
2. **サイトマップ生成を動的実装**
   - `SitemapController` でカテゴリ・ジャンル・女優・バズり動画詳細まで収録。
3. **一部ページで noindex を適切に付与**
   - 検索結果（キーワードあり）やソート違いページに `noindex,follow` を付与済み。
4. **JSON-LD を複数テンプレートに実装**
   - ItemList / Person を出力しており、リッチリザルト土台あり。

---

## 懸念点（SEO観点）

### P1: robots.txt の Sitemap URL が固定値

- `public/robots.txt` の `Sitemap` が固定URL（`https://owstrategy.com/fanzavideo/sitemap.xml`）。
- 環境（本番ドメイン・ステージング）ごとに齟齬が出る可能性あり。

**推奨**
- `robots.txt` をルート配信（Controller/Closure）に変更し、`route('sitemap')` で生成。

### P1: ページネーションURLのインデックス戦略が曖昧

- 一部一覧で `?page=2` 以降にも index 対象となりうる。
- canonical は `url()->current()` のため、2ページ目以降でも1ページ目へ寄せない設計（=方針としては可）。

**推奨（どちらかに統一）**
- 方針A: ページ2以降も index（その代わり重複を抑える内部リンク設計）
- 方針B: ページ2以降を noindex,follow

### P2: フィルターURLのクロール予算最適化

- `ranking?category=...` など、実質タブ遷移のパラメータURLが増える。

**推奨**
- 重要フィルターのみ index、その他は noindex を明示。
- あるいはパスベースURL（例 `/ranking/{category}`）へ寄せる。

### P2: SearchAction（WebSite構造化データ）未実装

- サイト内検索を持つため、`WebSite + SearchAction` をトップに付与できる。

**推奨**
- `home` または全体layoutで SearchAction JSON-LD を追加。

### P3: title/description のユニーク性の微調整

- テンプレート化されているため概ね良いが、ページ種別によっては文言重複が起きやすい。

**推奨**
- ページ1/2やカテゴリ差分を説明文に軽く反映（過剰最適化は避ける）。

---

## 優先対応ロードマップ

1. **robots.txt の動的化（最優先）**
2. **ページネーション/パラメータURLの index 方針を文書化して実装統一**
3. **WebSite + SearchAction JSON-LD 追加**
4. **主要テンプレートの title/description 重複チェック（サーチコンソール運用）**

---

## 補足

- 現状実装は「最低限のSEO要件」を十分満たしている。
- これ以上の改善は、実装よりも**運用指標（Search Consoleの表示回数・重複除外・クロール済み未登録）**を見ながら調整するのが費用対効果が高い。
