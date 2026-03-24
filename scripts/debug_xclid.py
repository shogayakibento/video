#!/usr/bin/env python3
"""
xclid デバッグスクリプト
x.com/tesla のページに含まれるパターンを調べる
"""
import asyncio
import re
import httpx
from fake_useragent import UserAgent


async def main():
    headers = {"user-agent": UserAgent().chrome}
    async with httpx.AsyncClient(headers=headers, follow_redirects=True) as clt:
        rep = await clt.get("https://x.com/tesla")
        print(f"Status : {rep.status_code}")
        print(f"URL    : {rep.url}")
        text = rep.text
        print(f"Length : {len(text)} chars\n")

        checks = {
            'e=>e+"."+':          'e=>e+"."+',
            '+"a.js"':            '+"a.js"',
            '[e]+"a.js"':         '[e]+"a.js"',
            '+e+"."+':            '+e+"."+ ',
            'e+"."+{':            'e+"."+{',
            '}[e]+':              '}[e]+',
            'ondemand.s':         'ondemand.s',
        }
        for label, pat in checks.items():
            print(f"  {label!r:25s} present: {pat in text}")

        # +"a.js" 前後50文字を表示
        idx = text.find('+"a.js"')
        if idx >= 0:
            print(f"\n[A] Context around +'a.js\"' (pos {idx}):")
            print(f"    ...{text[max(0,idx-80):idx+20]}...")

        # [e]+ 前後を確認
        idx2 = text.find('[e]+')
        if idx2 >= 0:
            print(f"\n[B] Context around '[e]+' (pos {idx2}):")
            print(f"    ...{text[max(0,idx2-80):idx2+30]}...")

        # }[e] 前後を確認
        idx3 = text.find('}[e]')
        if idx3 >= 0:
            print(f"\n[C] Context around '}}[e]' (pos {idx3}):")
            print(f"    ...{text[max(0,idx3-200):idx3+30]}...")

        # abs.twimg.com 完全URL
        full = re.findall(
            r'https://abs\.twimg\.com/responsive-web/client-web/[^\s"\'<>]+\.js',
            text,
        )
        print(f"\n[D] Full abs.twimg.com URLs ({len(full)}):")
        for u in set(full):
            print(f"    {u}")

        # twscrape xclid の get_tw_page_text を直接使って取得するページも確認
        # (main.jsなど外部JSの中にチャンクマップがある可能性)
        main_urls = [u for u in set(full) if '/main.' in u]
        if main_urls:
            print(f"\n[E] Fetching {main_urls[0]} for chunk map...")
            r2 = await clt.get(main_urls[0])
            js = r2.text
            print(f"    Length: {len(js)} chars")
            for pat in ['e=>e+"."+', '[e]+"a.js"', '+"a.js"', 'ondemand.s']:
                print(f"    {pat!r:25s} present in main.js: {pat in js}")

            idx4 = js.find('[e]+"a.js"')
            if idx4 >= 0:
                print(f"\n    Context around '[e]+\"a.js\"' in main.js (pos {idx4}):")
                print(f"    ...{js[max(0,idx4-120):idx4+30]}...")

            # チャンクマップ候補
            m = re.search(r'(\{[^{}]{50,}\})\[e\]\+"a\.js"', js)
            if m:
                print(f"\n    Chunk map found in main.js: {m.group(1)[:300]}")


if __name__ == "__main__":
    asyncio.run(main())
