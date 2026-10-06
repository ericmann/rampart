ARG PHP_VERSION=8.4

FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json /app/
RUN npm ci
COPY resources /app/resources
COPY vite.config.js tailwind.config.js postcss.config.js /app/
RUN npm run build

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
        libicu-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        intl \
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
COPY --from=assets /app/public/build /var/www/html/public/build

# Not --optimize: the checkout is bind-mounted over this directory at runtime, and a
# classmap frozen at build time would keep pointing at files attendees move or delete.
RUN composer dump-autoload

# Stamped by `make build` so `make doctor` can tell when this image predates the checkout.
# Declared last so changing it never invalidates the cached layers above.
ARG RAMPART_REVISION=unknown
LABEL rampart.revision=${RAMPART_REVISION}

EXPOSE 8080

# Run via bash rather than relying on the file's exec bit — at runtime this is the host's
# copy (bind-mounted), and git tracks it as 644.
ENTRYPOINT ["bash", "docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
