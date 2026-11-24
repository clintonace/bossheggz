# syntax=docker/dockerfile:1

# Production image for SeggzApp: Nginx + PHP-FPM (serversideup/php), with Composer
# dependencies and the Vite build baked in. Used by compose.production.yaml for the
# web app, the queue worker and the scheduler.

############################################
# 1. PHP dependencies (no dev packages)
############################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

############################################
# 2. Front-end assets (Vite + Tailwind)
############################################
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
# Tailwind scans Laravel's pagination views inside vendor/.
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

############################################
# 3. Runtime: Nginx + PHP-FPM
############################################
FROM serversideup/php:8.4-fpm-nginx AS app

ENV PHP_OPCACHE_ENABLE=1 \
    PHP_UPLOAD_MAX_FILE_SIZE=20M \
    PHP_POST_MAX_SIZE=60M \
    PHP_MEMORY_LIMIT=256M

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Rebuild the package manifest without dev-only packages.
RUN rm -f bootstrap/cache/*.php && php artisan package:discover --ansi
