FROM php:8.4-fpm
ARG WWWUSER=1002
ARG WWWGROUP=1002
RUN usermod -u ${WWWUSER} www-data
RUN groupmod -g ${WWWGROUP} www-data
# Dépendances système
RUN apt-get update && apt-get install -y unzip libzip-dev && rm -rf /var/lib/apt/lists/*
# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql bcmath zip
# Opcache (déjà compilé, juste à activer)
RUN docker-php-ext-enable opcache
# Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
