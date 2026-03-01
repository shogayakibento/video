#!/bin/bash
set -e

BRANCH="claude/improve-seo-8TiU4"
MSG="${1:-sync: $(date '+%Y-%m-%d %H:%M:%S')}"

git add -A
git diff --cached --quiet && { echo "変更なし"; exit 0; }
git commit -m "$MSG"

for i in 1 2 3 4; do
    git push -u origin "$BRANCH" && exit 0
    echo "push 失敗 (${i}/4) — ${i}秒後にリトライ..."
    sleep "$i"
done

echo "push 失敗" >&2; exit 1
