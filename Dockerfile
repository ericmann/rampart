ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli-bookworm

# Recorded here for `docker inspect`/CI: the app's real floor is PHP 8.4, declared in
# composer.json as "php": "^8.4". This build arg only lets us exercise a newer PHP without
# changing that contract.
ARG PHP_VERSION
LABEL org.opencontainers.image.title="rampart" \
      org.opencontainers.image.description="Intentionally-vulnerable Laravel workshop app" \
      rampart.php-version=${PHP_VERSION}

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* /var/www/html/
# Dev deps are kept in the shipped image on purpose — attendees run `composer test` and
# `composer test:exploits` (phpunit, mockery, faker) inside this very container.
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY . /var/www/html

RUN composer dump-autoload --optimize \
    && chmod +x docker/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
