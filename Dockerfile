# マルチステージビルド: Node.js
FROM node:18-alpine AS node-builder
WORKDIR /app
COPY laravel-app/package*.json ./
RUN npm ci
COPY laravel-app/ .
RUN npm run build

# マルチステージビルド: Composer
FROM composer:2 AS composer-builder
WORKDIR /app
COPY laravel-app/composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 本番ステージ: Nginx + PHP-FPM + Supervisor
FROM php:8.4-fpm-alpine

# 必要なシステムパッケージをインストール
RUN apk add --no-cache \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    postgresql-dev \
    gettext

# PHP拡張機能をインストール
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

WORKDIR /var/www

# アプリケーションファイルをコピー
COPY laravel-app/ .

# Composer依存関係をコピー
COPY --from=composer-builder /app/vendor ./vendor

# ビルド済みアセットをコピー
COPY --from=node-builder /app/public/build ./public/build

# PHP設定
COPY laravel-app/docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY laravel-app/docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Render環境ではRedisがないため、セッションをfiles/databaseに委ねる
# （LaravelのSESSION_DRIVER=database が環境変数で設定済み）
RUN echo "session.save_handler = files" > /usr/local/etc/php/conf.d/zz-session-override.ini && \
    echo "session.save_path = /tmp" >> /usr/local/etc/php/conf.d/zz-session-override.ini

# PHP-FPM設定（Render無料プラン 512MB対応）
RUN echo "[www]" > /usr/local/etc/php-fpm.d/zz-render.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/zz-render.conf && \
    echo "pm.max_children = 5" >> /usr/local/etc/php-fpm.d/zz-render.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.d/zz-render.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.d/zz-render.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.d/zz-render.conf

# Nginx設定
COPY laravel-app/docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY laravel-app/docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template

# Supervisor設定
COPY laravel-app/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 権限を設定
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# ログディレクトリを作成
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/log/php8

# 起動スクリプト
COPY laravel-app/docker/render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD ["/render-start.sh"]
