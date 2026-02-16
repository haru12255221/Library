#!/bin/sh

cd /var/www

# Renderのポート（デフォルト10000）
export PORT=${PORT:-10000}

# Nginx設定テンプレートにポート番号を埋め込む
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

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

# 権限の再設定
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Supervisorで Nginx + PHP-FPM + Queue Worker を起動
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
