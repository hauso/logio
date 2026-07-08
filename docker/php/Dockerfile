FROM php:8.4-fpm-alpine3.21

WORKDIR /app

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        git \
        icu-dev \
        linux-headers \
        oniguruma-dev \
        unzip \
    && docker-php-ext-install \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/logio-docker-entrypoint

ENTRYPOINT ["logio-docker-entrypoint"]
CMD ["php-fpm"]
