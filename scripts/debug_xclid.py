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

        # 元のパターン
        print(f"[1] 'e=>e+\".\"+' present : {'e=>e+\".\"+'  in text}")
        print(f"[2] '+'a.js\"'   present : {'+\"a.js\"'     in text}")
        print(f"[3] '+\".js\"'    present : {'+\".js\"'      in text}")
        print(f"[4] 'ondemand.s' present : {'ondemand.s'   in text}")
        print(f"[5] 'abs.twimg' present  : {'abs.twimg'    in text}")
        print(f"[6] 'twimg.com' present  : {'twimg.com'    in text}")
        print(f"[7] 'twitter-site-verification' present : {'twitter-site-verification' in text}\n")

        # abs.twimg.com 完全URL
        full = re.findall(
            r'https://abs\.twimg\.com/responsive-web/client-web/[^\s"\'<>]+\.js',
            text,
        )
        print(f"[8] Full abs.twimg.com URLs ({len(full)}):")
        for u in full[:8]:
            print(f"    {u}")

        # 相対URL
        rel = re.findall(r'/responsive-web/client-web/[^\s"\'<>]+\.js', text)
        print(f"\n[9] Relative /responsive-web URLs ({len(rel)}):")
        for u in rel[:8]:
            print(f"    {u}")

        # ondemand.s 絞り込み
        od = [u for u in full + rel if "ondemand.s" in u]
        print(f"\n[10] ondemand.s URLs ({len(od)}):")
        for u in od:
            print(f"    {u}")

        # チャンクマップ候補（JSON辞書のある周辺を検索）
        for pattern in [
            r'e\+["\']\.["\'](\{[^}]{20,}\})\[e\]',
            r'chunkhash[^{]{0,50}(\{[^}]{20,}\})',
        ]:
            m = re.search(pattern, text)
            if m:
                print(f"\n[11] Chunk-map candidate pattern '{pattern}':")
                print(f"     {m.group(1)[:200]}")

        # レスポンスの先頭を出力
        print("\n--- Response (first 3000 chars) ---")
        print(text[:3000])


if __name__ == "__main__":
    asyncio.run(main())
