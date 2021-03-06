FROM php:7.4.12-fpm

RUN apt-get update && apt-get install -y \
    zlib1g-dev libzip-dev libxml2-dev libicu-dev g++ git unzip jq \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && docker-php-ext-install zip

# Install APCu and APC backward compatibility
RUN pecl install apcu \
    && pecl install apcu_bc-1.0.5 \
    && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apc.ini

# Copy the Composer PHAR from the Composer image into the PHP image
COPY --from=composer:2.0.7 /usr/bin/composer /usr/bin/composer

ENV APP_ENV prod
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

COPY . /app
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

RUN chmod +x /app/bin/console
RUN chmod +x /app/docker-entrypoint.sh
RUN chmod +x /usr/bin/composer

RUN rm -rf /app/var \
    && mkdir -p /app/var \
    && php -d memory_limit=-1 /usr/bin/composer install --no-dev --optimize-autoloader

EXPOSE 9000

ENTRYPOINT ["/app/docker-entrypoint.sh"]
