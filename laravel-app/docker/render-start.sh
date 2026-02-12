#!/bin/sh

cd /workdir/laravel-app

# APP_KEYが未設定なら生成
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# ストレージリンク作成
php artisan storage:link 2>/dev/null || true

# マイグレーション実行
php artisan migrate --force

# キャッシュ最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache

# サーバー起動
php artisan serve --host=0.0.0.0 --port=8000
