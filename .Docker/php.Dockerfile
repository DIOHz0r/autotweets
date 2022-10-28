FROM php:7.4-fpm-alpine

RUN apk update && apk add autoconf g++ make bash

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN echo 'xdebug.mode = ${XDEBUG_MODE}' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY ./.Docker/start-container.sh /app/.Docker/start-container.sh

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer