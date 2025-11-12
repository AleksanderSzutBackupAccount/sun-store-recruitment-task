FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor libpng-dev libzip-dev zip git curl bash \
    && docker-php-ext-install pdo pdo_mysql zip gd

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

COPY ./deployment/nginx.conf /etc/nginx/nginx.conf
COPY ./deployment/supervisord.conf /etc/supervisord.conf
COPY ./deployment/entrypoint.sh /etc/entrypoint.sh

RUN php artisan config:cache && php artisan route:cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
