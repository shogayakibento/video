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

ACCOUNTS_FILE = os.environ.get('ACCOUNTS_FILE', 'storage/app/private/twitter_accounts.txt')
DB_PATH       = os.environ.get('TWSCRAPE_DB',   'storage/app/private/twscrape.db')
MIN_LIKES     = int(os.environ.get('MIN_LIKES', '1000'))
TWEETS_PER_USER = int(os.environ.get('TWEETS_PER_USER', '200'))

TWITTER_USERNAME = os.environ.get('TWITTER_USERNAME', '')
TWITTER_EMAIL    = os.environ.get('TWITTER_EMAIL', '')
TWITTER_PASSWORD = os.environ.get('TWITTER_PASSWORD', '')

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

    # アカウントが未登録なら追加してログイン
    accounts_in_pool = await api.pool.get_all()
    if not accounts_in_pool:
        if not (TWITTER_USERNAME and TWITTER_EMAIL and TWITTER_PASSWORD):
            print(json.dumps({'error': 'TWITTER_USERNAME/EMAIL/PASSWORD が設定されていません'}))
            sys.exit(1)
        await api.pool.add_account(
            username=TWITTER_USERNAME,
            password=TWITTER_PASSWORD,
            email=TWITTER_EMAIL,
            email_password='',
        )
        await api.pool.login_all()

    results = []

    for username in account_names:
        sys.stderr.write(f'処理中: @{username}\n')
        try:
            user = await api.user_by_login(username)
            if not user:
                sys.stderr.write(f'  ユーザーが見つかりません: @{username}\n')
                continue

            # ツイート＋返信を取得してIDでマップ
            tweet_map: dict[int, object] = {}
            async for tweet in api.user_tweets_and_replies(user.id, limit=TWEETS_PER_USER):
                tweet_map[tweet.id] = tweet

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
                        parent = await api.tweet_details(parent_id)
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

        except Exception as e:
            sys.stderr.write(f'エラー @{username}: {e}\n')
            continue

    print(json.dumps(results, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    asyncio.run(main())
