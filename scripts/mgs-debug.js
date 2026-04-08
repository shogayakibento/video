import puppeteer from 'puppeteer';

const BASE = 'https://www.mgstage.com';
const name = process.argv[2] || '八掛うみ';

const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--lang=ja-JP'],
});
const page = await browser.newPage();
await page.setExtraHTTPHeaders({ 'Accept-Language': 'ja,en-US;q=0.9' });
await page.setCookie({ name: 'adc', value: '1', domain: '.mgstage.com', path: '/' });

const url = `${BASE}/search/cSearch.php?type=top&actor[]=${encodeURIComponent(name)}&list_cnt=120`;
await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
await new Promise(r => setTimeout(r, 1000));

// 複数女優作品の出演欄を確認（3P作品など）
const productUrl = process.argv[2] || 'https://www.mgstage.com/product/product_detail/ABF-301/';
await page.goto(productUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
await new Promise(r => setTimeout(r, 1000));

const debug = await page.evaluate(() => {
    const rows = [];
    document.querySelectorAll('th').forEach(th => {
        if (th.textContent.includes('出演')) {
            const td = th.nextElementSibling;
            rows.push({
                th: th.textContent.trim(),
                tdText: td?.textContent?.trim(),
                tdHTML: td?.innerHTML?.trim(),
            });
        }
    });
    return rows;
});

console.log(JSON.stringify(debug, null, 2));
await browser.close();
