FROM php:8.4
WORKDIR /workdir
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME="/opt/composer"
ENV PATH="$PATH:/opt/composer/vendor/bin"
RUN apt-get update && apt-get install -y zip

RUN docker-php-ext-install pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs

COPY . .
WORKDIR /workdir/laravel-app
RUN composer install
RUN npm install
RUN npm run build
CMD [ "php", "artisan", "serve", "--host", "0.0.0.0" ]
EXPOSE 8000
