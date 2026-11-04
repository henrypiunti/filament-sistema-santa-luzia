# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4

############################################
# 1) Dependências PHP (sem pacotes de dev)
############################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-autoloader \
        --no-progress \
        --prefer-dist \
        --ignore-platform-reqs

############################################
# 2) Build dos assets (Vite + Tailwind v4)
############################################
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
# O app.css usa @source em views do vendor (paginação)
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views

RUN npm run build

############################################
# 3) Runtime: FrankenPHP (Caddy + PHP), compatível com ARM (Ampere A1)
############################################
FROM dunglas/frankenphp:1-php${PHP_VERSION}-bookworm AS app

ENV APP_ENV=production \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1 \
    SERVER_NAME=":80" \
    PHP_INI_SCAN_DIR=":/usr/local/etc/php/app.d"

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends sqlite3 curl unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_sqlite \
        zip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/app.d/99-app.ini
COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/database \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && ln -sfn /app/storage/app/public /app/public/storage \
    && chmod +x /usr/local/bin/app-entrypoint \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /data/caddy /config/caddy

USER www-data

EXPOSE 80 443 443/udp

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1:2019/metrics > /dev/null || exit 1

ENTRYPOINT ["app-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
