#!/bin/sh

# Laravel アプリケーションの初期化
echo "Starting Laravel application initialization..."

# 環境変数の確認
if [ ! -f /var/www/.env ]; then
    echo "Creating .env file from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# アプリケーションキーの生成
if ! grep -q "APP_KEY=base64:" /var/www/.env; then
    echo "Generating application key..."
    php /var/www/artisan key:generate --force
fi

# ストレージリンクの作成
if [ ! -L /var/www/public/storage ]; then
    echo "Creating storage link..."
    php /var/www/artisan storage:link
fi

# キャッシュの最適化
echo "Optimizing application..."
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

# データベースマイグレーション（本番環境では慎重に）
if [ "$APP_ENV" != "production" ]; then
    echo "Running database migrations..."
    php /var/www/artisan migrate --force
fi

# 権限の再設定
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "Laravel application initialization completed."

# Supervisorを起動
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf