@echo off
cd /d "%~dp0.."
set OUTPUT=storage\app\private\tweets_export.json

echo スクレイピング開始...
python scripts\scrape_tweets.py > %OUTPUT% 2>nul

if %errorlevel% neq 0 (
    echo スクレイピング失敗
    pause
    exit /b 1
)

echo 完了: %OUTPUT% に保存されました
echo.
echo 次の手順:
echo 1. FileZillaで %OUTPUT% をサーバーの storage/app/private/ にアップロード
echo 2. SSHで以下を実行:
echo    php artisan tweet:import-json storage/app/private/tweets_export.json
echo.
pause
