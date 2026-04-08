/**
 * MGS動画 Puppeteerクローラー
 *
 * 使い方:
 *   node scripts/mgs-crawl.js --mode=exclusive           # 専属女優全員
 *   node scripts/mgs-crawl.js --mode=actress --name=涼森れむ  # 特定女優
 */

import puppeteer from 'puppeteer';
import { parseArgs } from 'util';

const { values: args } = parseArgs({
    options: {
        mode:     { type: 'string', default: 'exclusive' },
        name:     { type: 'string', default: '' },
        aff:      { type: 'string', default: '' },
        limit:    { type: 'string', default: '0' },
        headless: { type: 'string', default: 'true' },
    },
    strict: false,
});

const BASE     = 'https://www.mgstage.com';
const AFF_CODE = args.aff || '';
const LIMIT    = parseInt(args.limit) || 0;
const HEADLESS = args.headless !== 'false';

function log(...msg) { process.stderr.write(msg.join(' ') + '\n'); }
function sleep(ms)   { return new Promise(r => setTimeout(r, ms)); }

function buildAffUrl(productCode) {
    const upper = productCode.toUpperCase();
    const base  = `${BASE}/product/product_detail/${upper}/`;
    return AFF_CODE ? `${base}?aff=${AFF_CODE}` : base;
}

async function launchBrowser() {
    return puppeteer.launch({
        headless: HEADLESS,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--lang=ja-JP'],
    });
}

async function setupPage(browser) {
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'ja,en-US;q=0.9' });
    await page.setCookie({ name: 'adc', value: '1', domain: '.mgstage.com', path: '/' });
    await page.setViewport({ width: 1280, height: 800 });
    return page;
}

// -------- 専属女優名リストを取得 --------
async function getExclusiveActressNames(page) {
    log('[1/3] 専属女優一覧を取得中...');

    const names = new Set();
    let pageNum = 1;

    while (true) {
        const url = `${BASE}/list/actress_list.php?exclusive=1&page=${pageNum}`;
        log(`  → ページ${pageNum}: ${url}`);

        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await sleep(1000);

        const found = await page.evaluate(() => {
            // 女優名のリンクを取得（actor[]= パラメータから女優名を抽出）
            const links = document.querySelectorAll('a[href*="actor[]"]');
            const result = [];
            links.forEach(a => {
                const match = a.href.match(/actor\[\]=([^&]+)/);
                if (match) {
                    result.push(decodeURIComponent(match[1]).replace(/_\d+$/, ''));
                }
            });
            return [...new Set(result)];
        });

        if (found.length === 0) {
            // 別のセレクターを試す
            const alt = await page.evaluate(() => {
                const links = document.querySelectorAll('.actress_name a, .name a, h5 a, .th_actress a');
                return Array.from(links).map(a => a.textContent.trim()).filter(Boolean);
            });
            if (alt.length === 0) break;
            alt.forEach(n => names.add(n));
            log(`  → ${alt.length}人（累計: ${names.size}人）`);
        } else {
            found.forEach(n => names.add(n));
            log(`  → ${found.length}人（累計: ${names.size}人）`);
        }

        // 次ページ確認
        const hasNext = await page.evaluate(() => {
            return !!(document.querySelector('.next_page a, .pagination .next, a[rel="next"]'));
        });
        if (!hasNext) break;

        pageNum++;
        await sleep(1500);
    }

    return [...names];
}

// -------- 女優名から作品URLリストを取得 --------
async function getProductUrlsByActress(page, actressName) {
    log(`  「${actressName}」の作品を取得中...`);

    const productUrls = new Map();
    let pageNum = 1;

    while (true) {
        const encoded = encodeURIComponent(actressName);
        const url = `${BASE}/search/cSearch.php?type=top&actor[]=${encoded}&page=${pageNum}&list_cnt=120`;

        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await sleep(800);

        const found = await page.evaluate(() => {
            const items = [];
            // 各商品カードを取得
            const cards = document.querySelectorAll('.rank_list li, .list_items li, li.s_list');
            cards.forEach(card => {
                const a = card.querySelector('a[href*="/product/product_detail/"]');
                if (!a) return;
                const url = a.href.split('?')[0];

                // レビュースコア（例: 4.6\n(15件)）
                const rateEl = card.querySelector('p.review');
                let reviewScore = null, reviewCount = 0;
                if (rateEl) {
                    const text = rateEl.textContent;
                    const scoreMatch = text.match(/(\d+\.\d+)/);
                    const countMatch = text.match(/\((\d+)\s*件\)/);
                    if (scoreMatch) reviewScore = parseFloat(scoreMatch[1]);
                    if (countMatch) reviewCount = parseInt(countMatch[1]);
                }

                items.push({ url, reviewScore, reviewCount, releaseDate: null });
            });

            // カード構造が見つからない場合はURLのみ
            if (items.length === 0) {
                const links = document.querySelectorAll('a[href*="/product/product_detail/"]');
                return [...new Set(Array.from(links).map(a => a.href.split('?')[0]))].map(url => ({ url, reviewScore: null, reviewCount: 0, releaseDate: null }));
            }
            return items;
        });

        if (found.length === 0) break;
        found.forEach(item => productUrls.set(item.url, item));

        log(`    ページ${pageNum}: ${found.length}件（累計: ${productUrls.size}件）`);

        const hasNext = await page.evaluate(() => {
            return !!(document.querySelector('.next_page a, a[rel="next"], .pagination .next'));
        });
        if (!hasNext && found.length < 100) break;

        pageNum++;
        await sleep(1000);

        if (LIMIT > 0 && productUrls.size >= LIMIT) break;
    }

    return [...productUrls.values()];
}

// -------- 作品ページから情報取得 --------
async function scrapeProduct(browser, productUrl, listingData = {}) {
    const page = await setupPage(browser);
    let sampleUrl = null;

    // sampleRespons.phpのレスポンスを傍受
    page.on('response', async (res) => {
        if (res.url().includes('sampleRespons.php')) {
            try {
                const json = await res.json();
                if (json.url) {
                    sampleUrl = json.url.replace(/\.ism\/request.*$/, '.mp4');
                }
            } catch (_) {}
        }
    });

    try {
        await page.goto(productUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await sleep(800);

        const info = await page.evaluate(() => {
            const getMeta = (prop) => document.querySelector(`meta[property="${prop}"]`)?.content || '';

            const getThTd = (label) => {
                const ths = document.querySelectorAll('th');
                for (const th of ths) {
                    if (th.textContent.includes(label)) {
                        const td = th.nextElementSibling;
                        return td ? td.textContent.replace(/\s+/g, ' ').trim() : '';
                    }
                }
                return '';
            };

            let title = getMeta('og:title');
            title = title.replace(/^「(.+)」：.+$/, '$1')
                         .replace(/\s*[：:]\s*MGS.*$/, '')
                         .trim();

            // レビュー取得（評価：のth/tdから）
            const ratingText = getThTd('評価：');
            const scoreMatch = ratingText.match(/(\d+\.\d+)/);
            const countMatch = ratingText.match(/\((\d+)\s*件\)/);
            const reviewScore = scoreMatch ? parseFloat(scoreMatch[1]) : null;
            const reviewCount = countMatch ? parseInt(countMatch[1]) : 0;

            return {
                title,
                thumbnail_url: getMeta('og:image'),
                actress:      getThTd('出演：'),
                maker:        getThTd('メーカー：'),
                genre:        getThTd('ジャンル：'),
                release_date: getThTd('配信開始日：').replace(/\//g, '-'),
                review_score: reviewScore,
                review_count: reviewCount,
            };
        });

        // 品番をURLから取得
        const match = productUrl.match(/product_detail\/([^/]+)/i);
        const productCode = match ? match[1].toLowerCase() : '';

        // サンプル動画ボタンをクリック
        const btn = await page.$('.button_sample, a.sample_movie, a[href*="sampleplayer"]');
        if (btn) {
            await btn.click();
            await sleep(3000);
        }

        await page.close();

        return {
            product_code:     productCode,
            title:            info.title || productCode,
            actress:          info.actress,
            maker:            info.maker,
            genre:            info.genre,
            thumbnail_url:    info.thumbnail_url,
            sample_video_url: sampleUrl,
            affiliate_url:    buildAffUrl(productCode),
            release_date:     info.release_date || listingData.releaseDate || null,
            review_score:     listingData.reviewScore ?? info.review_score,
            review_count:     listingData.reviewCount || info.review_count,
            mgs_rank:         listingData.mgsRank ?? null,
        };

    } catch (err) {
        log(`  エラー: ${productUrl} - ${err.message}`);
        try { await page.close(); } catch (_) {}
        return null;
    }
}

// -------- メイン --------
async function main() {
    const browser = await launchBrowser();
    const page    = await setupPage(browser);
    const results = [];

    try {
        let actressNames = [];

        if (args.mode === 'actress' && args.name) {
            actressNames = [args.name];
        } else {
            actressNames = await getExclusiveActressNames(page);
        }

        if (actressNames.length === 0) {
            log('女優が見つかりませんでした。');
            console.log(JSON.stringify([]));
            await browser.close();
            return;
        }

        log(`[2/3] ${actressNames.length}人の作品URLを収集中...`);

        const productMap = new Map(); // url → {reviewScore, reviewCount, releaseDate}
        for (const name of actressNames) {
            const items = await getProductUrlsByActress(page, name);
            items.forEach(item => productMap.set(item.url, item));
            log(`  作品累計: ${productMap.size}件`);
            await sleep(1000);
            if (LIMIT > 0 && productMap.size >= LIMIT) break;
        }

        await page.close();

        const itemList = [...productMap.values()].slice(0, LIMIT || Infinity);
        log(`[3/3] ${itemList.length}件の作品を詳細取得中...`);

        for (let i = 0; i < itemList.length; i++) {
            const { url, reviewScore, reviewCount, releaseDate } = itemList[i];
            log(`  [${i + 1}/${itemList.length}] ${url}`);
            const data = await scrapeProduct(browser, url, { reviewScore, reviewCount, releaseDate, mgsRank: i + 1 });
            if (data) results.push(data);
            await sleep(1500);
        }

    } finally {
        await browser.close();
    }

    log(`完了: ${results.length}件取得`);
    console.log(JSON.stringify(results, null, 2));
}

main().catch(err => {
    log('Fatal:', err.message);
    process.exit(1);
});
