FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache git icu-dev libpq-dev unzip \
    && docker-php-ext-install intl pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

CMD ["php-fpm"]
