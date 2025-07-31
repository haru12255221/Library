# 開発環境用Dockerfile
FROM php:8.4-fpm

WORKDIR /workdir

# Composerをインストール
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME="/opt/composer"
ENV PATH="$PATH:/opt/composer/vendor/bin"

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張をインストール
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Redis拡張をインストール
RUN pecl install redis && docker-php-ext-enable redis

# Node.jsをインストール
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# 開発環境用PHP設定
COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/dev.ini

# アプリケーションファイルをコピー
COPY . .

# Laravel作業ディレクトリに移動
WORKDIR /workdir/laravel-app

# 開発用.envファイルを設定
RUN if [ -f .env.local ]; then cp .env.local .env; fi

# 開発用依存関係をインストール
RUN composer install --dev --no-scripts --no-autoloader && \
    composer dump-autoload --optimize

# NPM依存関係をインストール
RUN npm install

# 権限設定
RUN chown -R www-data:www-data /workdir && \
    chmod -R 755 /workdir/laravel-app/storage && \
    chmod -R 755 /workdir/laravel-app/bootstrap/cache

# 開発環境用の設定
ENV APP_ENV=local
ENV APP_DEBUG=true

# ポートを公開
EXPOSE 8000
EXPOSE 5174

# ヘルスチェック（PHP-FPM用）
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -v || exit 1

# PHP-FPMを起動
CMD ["php-fpm"]
