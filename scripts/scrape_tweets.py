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
from urllib.parse import parse_qs, unquote, urlparse

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

# dmm.co.jp の cid= または id= を抽出
# video.dmm.co.jp は ?id= 形式、その他は cid= 形式を使用
FANZA_CID_RE = re.compile(
    r'dmm\.co\.jp[^\s]*[?&/]c?id[=/]([a-zA-Z0-9_-]+)',
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
            raw = getattr(link, 'expanded', None) or getattr(link, 'url', None) or ''
            if not raw:
                continue
            urls.append(raw)
            # al.fanza.co.jp / al.dmm.co.jp のアフィリエイトリンクから
            # lurl= パラメータをデコードして実際のDMM URLを取得
            if 'al.fanza.co.jp' in raw or 'al.dmm.co.jp' in raw:
                try:
                    qs = parse_qs(urlparse(raw).query)
                    if 'lurl' in qs:
                        urls.append(unquote(qs['lurl'][0]))
                except Exception:
                    pass
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

        n_replies = sum(1 for t in tweet_map.values() if getattr(t, 'inReplyToTweetId', None))
        sys.stderr.write(f'  取得件数: {len(tweet_map)}ツイート（うち返信: {n_replies}件）\n')

        # DEBUGモード: 最初の5件のURL一覧を出力
        if os.environ.get('TWSCRAPE_DEBUG'):
            for i, (_, t) in enumerate(list(tweet_map.items())[:5]):
                text_preview = (getattr(t, 'rawContent', '') or '')[:100].replace('\n', ' ')
                sys.stderr.write(f'  [DEBUG] tweet={t.id}\n')
                sys.stderr.write(f'    text: {text_preview!r}\n')
                raw_links = getattr(t, 'links', None) or []
                for lnk in raw_links:
                    sys.stderr.write(f'    link: url={getattr(lnk,"url",None)!r} expanded={getattr(lnk,"expanded",None)!r} attrs={[a for a in dir(lnk) if not a.startswith("_")]}\n')

        n_fanza = 0
        n_likes_ok = 0
        seen_tweet_ids: set[str] = set()

        for tweet_id, tweet in tweet_map.items():
            # FANZA URLを探す
            all_text = (getattr(tweet, 'rawContent', '') or '') + ' ' + ' '.join(get_tweet_urls(tweet))
            cid = extract_fanza_cid(all_text)
            if not cid:
                continue
            n_fanza += 1

            parent_id = getattr(tweet, 'inReplyToTweetId', None)

            if parent_id:
                # 返信ツイート → 親ツイートのいいね数で判定
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
                    sys.stderr.write(f'  いいね不足(親): {cid} ({like_count}いいね < {MIN_LIKES})\n')
                    continue

                result_tweet = parent
                result_author = parent.user
            else:
                # 通常ツイート → そのツイート自身のいいね数で判定
                like_count = getattr(tweet, 'likeCount', 0) or 0
                if like_count < MIN_LIKES:
                    sys.stderr.write(f'  いいね不足(自): {cid} ({like_count}いいね < {MIN_LIKES})\n')
                    continue

                result_tweet = tweet
                result_author = tweet.user

            result_id = str(result_tweet.id)
            if result_id in seen_tweet_ids:
                continue
            seen_tweet_ids.add(result_id)
            n_likes_ok += 1

            results.append({
                'dmm_content_id': cid,
                'tweet_id':       result_id,
                'tweet_url':      f'https://x.com/{result_author.username}/status/{result_id}',
                'tweet_text':     getattr(result_tweet, 'rawContent', ''),
                'author_username': result_author.username,
                'like_count':     like_count,
                'retweet_count':  getattr(result_tweet, 'retweetCount', 0) or 0,
                'tweeted_at':     result_tweet.date.isoformat() if result_tweet.date else None,
            })
            sys.stderr.write(f'  ヒット: {cid} ({like_count}いいね)\n')

        sys.stderr.write(f'  フィルタ結果: FANZA含む={n_fanza}件、いいね{MIN_LIKES}以上={n_likes_ok}件\n')

    sys.stdout.buffer.write(json.dumps(results, ensure_ascii=False, indent=2).encode('utf-8'))
    sys.stdout.buffer.write(b'\n')


if __name__ == '__main__':
    asyncio.run(main())
