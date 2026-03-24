#!/usr/bin/env python3
"""
Twitter scraper using twscrape.
対象アカウントの返信ツイートからFANZA品番を抽出し、
いいね数が閾値以上の親ツイートをJSON出力する。
"""

import asyncio
import json
import os
import re
import sys
from pathlib import Path

import twscrape

# --- twscrape xclid.py patch ---
# Twitter changed page format; original get_scripts_list raises IndexError.
# Fallback: scan HTML for abs.twimg.com script URLs directly.
try:
    import json as _json
    import re as _re
    import twscrape.xclid as _xclid

    def _fix_js_obj(s: str) -> str:
        """Quote unquoted JS object keys so json.loads can parse them."""
        return _re.sub(r'([{,])\s*(\w+)\s*:', r'\1"\2":', s)

    def _fixed_get_scripts_list(text: str):
        # Strategy 1: Original pattern  e=>e+"."+{hash_dict}[e]+"a.js"
        try:
            parts = text.split('e=>e+"."+')
            if len(parts) > 1:
                scripts_json = parts[1].split('[e]+"a.js"')[0]
                for k, v in _json.loads(_fix_js_obj(scripts_json)).items():
                    yield _xclid.script_url(str(k), f"{v}a")
                return
        except Exception:
            pass

        # Strategy 2: New two-dict pattern
        #   ({name_dict}[e]||e)+"."+{hash_dict}[e]+"a.js"
        # name_dict maps chunkId -> name (e.g. "ondemand.s")
        # hash_dict maps chunkId -> content hash
        try:
            m = _re.search(
                r'(\{[^{}]+\})\[e\]\|\|e\)\+"\."\+(\{[^{}]+\})\[e\]\+"a\.js"',
                text,
            )
            if m:
                name_dict = _json.loads(_fix_js_obj(m.group(1)))
                hash_dict = _json.loads(_fix_js_obj(m.group(2)))
                for k, v in hash_dict.items():
                    name = name_dict.get(str(k), str(k))
                    yield _xclid.script_url(name, f"{v}a")
                return
        except Exception:
            pass

    _xclid.get_scripts_list = _fixed_get_scripts_list
except Exception:
    pass
# --- end patch ---

ACCOUNTS_FILE = os.environ.get('ACCOUNTS_FILE', 'storage/app/private/twitter_accounts.txt')
DB_PATH       = os.environ.get('TWSCRAPE_DB',   'storage/app/private/twscrape.db')
MIN_LIKES     = int(os.environ.get('MIN_LIKES', '1000'))
TWEETS_PER_USER = int(os.environ.get('TWEETS_PER_USER', '200'))

TWITTER_USERNAME   = os.environ.get('TWITTER_USERNAME', '')
TWITTER_EMAIL      = os.environ.get('TWITTER_EMAIL', '')
TWITTER_PASSWORD   = os.environ.get('TWITTER_PASSWORD', '')
TWITTER_AUTH_TOKEN = os.environ.get('TWITTER_AUTH_TOKEN', '')
TWITTER_CT0        = os.environ.get('TWITTER_CT0', '')

# dmm.co.jp の cid= を抽出
FANZA_CID_RE = re.compile(
    r'dmm\.co\.jp[^\s]*[?&/]cid[=/]([a-zA-Z0-9_-]+)',
    re.IGNORECASE
)


def extract_fanza_cid(text: str) -> str | None:
    m = FANZA_CID_RE.search(text)
    if m:
        cid = m.group(1).rstrip('/').lower()
        return cid
    return None


def get_tweet_urls(tweet) -> list[str]:
    urls = []
    if hasattr(tweet, 'links') and tweet.links:
        for link in tweet.links:
            expanded = getattr(link, 'expanded', None) or getattr(link, 'url', None) or ''
            if expanded:
                urls.append(expanded)
    return urls


async def main():
    accounts_path = Path(ACCOUNTS_FILE)
    if not accounts_path.exists():
        print(json.dumps({'error': f'アカウントファイルが見つかりません: {ACCOUNTS_FILE}'}))
        sys.exit(1)

    account_names = [
        line.strip().lstrip('@')
        for line in accounts_path.read_text(encoding='utf-8').splitlines()
        if line.strip() and not line.startswith('#')
    ]

    if not account_names:
        print(json.dumps({'error': 'アカウントリストが空です'}))
        sys.exit(1)

    api = twscrape.API(DB_PATH)

    if not TWITTER_USERNAME:
        print(json.dumps({'error': 'TWITTER_USERNAME が設定されていません'}))
        sys.exit(1)

    if not (TWITTER_AUTH_TOKEN and TWITTER_CT0):
        print(json.dumps({'error': 'TWITTER_AUTH_TOKEN と TWITTER_CT0 が設定されていません'}))
        sys.exit(1)

    # 毎回クッキーを更新して追加（古いセッションを上書き）
    cookies = f"auth_token={TWITTER_AUTH_TOKEN}; ct0={TWITTER_CT0}"
    await api.pool.add_account(
        username=TWITTER_USERNAME,
        password=TWITTER_PASSWORD or 'dummy',
        email=TWITTER_EMAIL or 'dummy@example.com',
        email_password='',
        cookies=cookies,
    )

    # アカウントが有効か確認
    accounts_in_pool = await api.pool.get_all()
    active_accounts = [a for a in accounts_in_pool if getattr(a, 'active', False)]
    if not active_accounts:
        print(json.dumps({
            'error': 'Twitterアカウントが無効です。auth_token と ct0 が最新のものか確認してください。'
        }))
        sys.exit(1)

    sys.stderr.write(f'アクティブアカウント: {len(active_accounts)}件\n')

    results = []

    USER_TIMEOUT  = int(os.environ.get('USER_TIMEOUT', '120'))   # ユーザーごとの上限秒数
    TWEET_TIMEOUT = int(os.environ.get('TWEET_TIMEOUT', '30'))    # 親ツイート取得の上限秒数

    for username in account_names:
        sys.stderr.write(f'処理中: @{username}\n')
        try:
            async with asyncio.timeout(USER_TIMEOUT):
                user = await api.user_by_login(username)
                if not user:
                    sys.stderr.write(f'  ユーザーが見つかりません: @{username}\n')
                    continue

                # ツイート＋返信を取得してIDでマップ
                tweet_map: dict[int, object] = {}
                async for tweet in api.user_tweets_and_replies(user.id, limit=TWEETS_PER_USER):
                    tweet_map[tweet.id] = tweet

        except TimeoutError:
            sys.stderr.write(f'  タイムアウト（{USER_TIMEOUT}s）: @{username}\n')
            continue
        except Exception as e:
            sys.stderr.write(f'エラー @{username}: {e}\n')
            continue

        sys.stderr.write(f'  取得件数: {len(tweet_map)}ツイート\n')

        for tweet_id, tweet in tweet_map.items():
            # 返信ツイートのみ対象
            if not getattr(tweet, 'inReplyToTweetId', None):
                continue

            # FANZA URLを探す
            all_text = (getattr(tweet, 'rawContent', '') or '') + ' ' + ' '.join(get_tweet_urls(tweet))
            cid = extract_fanza_cid(all_text)
            if not cid:
                continue

            # 親ツイートを取得（マップにあればそれを使う）
            parent_id = tweet.inReplyToTweetId
            parent = tweet_map.get(parent_id)
            if not parent:
                try:
                    async with asyncio.timeout(TWEET_TIMEOUT):
                        parent = await api.tweet_details(parent_id)
                except TimeoutError:
                    sys.stderr.write(f'  親ツイート取得タイムアウト {parent_id}\n')
                    continue
                except Exception as e:
                    sys.stderr.write(f'  親ツイート取得失敗 {parent_id}: {e}\n')
                    continue

            if not parent:
                continue

            like_count = getattr(parent, 'likeCount', 0) or 0
            if like_count < MIN_LIKES:
                continue

            author = parent.user
            results.append({
                'dmm_content_id': cid,
                'tweet_id':       str(parent.id),
                'tweet_url':      f'https://x.com/{author.username}/status/{parent.id}',
                'tweet_text':     getattr(parent, 'rawContent', ''),
                'author_username': author.username,
                'like_count':     like_count,
                'retweet_count':  getattr(parent, 'retweetCount', 0) or 0,
                'tweeted_at':     parent.date.isoformat() if parent.date else None,
            })
            sys.stderr.write(f'  ヒット: {cid} ({like_count}いいね)\n')

    print(json.dumps(results, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    asyncio.run(main())
