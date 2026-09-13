FROM node:22-bookworm-slim AS node

FROM composer:2 AS composer

FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libpq-dev libzip-dev unzip \
    && docker-php-ext-install pcntl pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY docker/php.ini /usr/local/etc/php/conf.d/99-betareadr.ini

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && git config --global --add safe.directory /var/www/html

WORKDIR /var/www/html

EXPOSE 8000 5173 8080
