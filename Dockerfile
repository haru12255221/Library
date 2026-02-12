FROM php:8.4
WORKDIR /workdir
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME="/opt/composer"
ENV PATH="$PATH:/opt/composer/vendor/bin"
RUN apt-get update && apt-get install -y zip libpq-dev

RUN docker-php-ext-install pdo_mysql pdo_pgsql

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs

COPY . .
WORKDIR /workdir/laravel-app
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

COPY laravel-app/docker/render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD [ "/render-start.sh" ]
EXPOSE 8000
