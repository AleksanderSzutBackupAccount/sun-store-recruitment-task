FROM composer:2 AS vendor
WORKDIR /app
COPY ../composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor libpng-dev libzip-dev zip git curl bash \
    && docker-php-ext-install pdo pdo_mysql zip gd

WORKDIR /var/www/html
COPY .. .
COPY --from=vendor /app/vendor ./vendor

COPY ./nginx.conf /etc/nginx/nginx.conf

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

COPY ./supervisord.conf /etc/supervisord.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
