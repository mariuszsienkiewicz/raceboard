FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    icu-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
    intl \
    pdo_pgsql \
    zip \
    opcache

RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini

EXPOSE 9000