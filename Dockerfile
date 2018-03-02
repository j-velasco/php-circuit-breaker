FROM php:7.0-alpine

RUN apk add --no-cache $PHPIZE_DEPS openssl-dev

RUN echo 'no' | pecl install apcu
RUN chmod +x /usr/local/lib/php/extensions/no-debug-non-zts-20151012/apcu.so
COPY php.ini /usr/local/etc/php/php.ini
