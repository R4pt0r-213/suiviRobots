FROM php:8.4-cli-alpine

RUN apk add --no-cache git unzip sqlite-dev icu-dev \
    && docker-php-ext-install pdo_sqlite intl opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts
COPY . .
RUN chmod +x docker/entrypoint.sh
EXPOSE 8000
