# マルチステージビルド: Node.js ビルドステージ
FROM node:18-alpine AS node-builder

WORKDIR /app

# package.json と package-lock.json をコピー
COPY package*.json ./

# NPM依存関係をインストール（ビルド用にdevDependenciesも含める）
RUN npm ci

# ソースコードをコピー
COPY . .

# アセットをビルド
RUN npm run build

# マルチステージビルド: Composer ビルドステージ
FROM composer:2 AS composer-builder

WORKDIR /app

# composer.json と composer.lock をコピー
COPY composer*.json ./

# Composer依存関係をインストール（本番用）
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 本番ステージ
FROM php:8.2-fpm-alpine

# 必要なシステムパッケージをインストール
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    mysql-client \
    redis \
    autoconf \
    gcc \
    g++ \
    make

# PHP拡張機能をインストール
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Redis PHP拡張をインストール
RUN pecl install redis && docker-php-ext-enable redis

# 作業ディレクトリを設定
WORKDIR /var/www

# アプリケーションファイルをコピー
COPY . .

# Composerの依存関係をコピー
COPY --from=composer-builder /app/vendor ./vendor

# ビルドされたアセットをコピー
COPY --from=node-builder /app/public/build ./public/build

# PHP設定ファイルをコピー
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Nginx設定ファイルをコピー
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor設定ファイルをコピー
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 権限を設定
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# ログディレクトリを作成
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/log/nginx \
    && mkdir -p /var/log/php8

# 起動スクリプトをコピー
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# ポートを公開
EXPOSE 80

# ヘルスチェック
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Supervisorで複数サービスを起動
CMD ["/start.sh"]