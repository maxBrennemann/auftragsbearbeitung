FROM composer:2.8.8 AS composer

# Set working directory
WORKDIR /var/www/html

# Copy the composer.json and composer.lock
COPY composer.json composer.lock ./

# Install the dependencies
RUN composer install --no-scripts

FROM php:8.4.7-fpm

# Get UID and GID of host user
ARG UID
ARG GID

# Install some base extensions
RUN apt-get update \
     && docker-php-ext-install mysqli pdo pdo_mysql \
     && docker-php-ext-enable pdo_mysql

# Set the document root
WORKDIR /var/www/html

# https://stackoverflow.com/questions/73991553/permission-denied-for-laravel-and-docker
# user muss danach gesetzt werden, da sonst die Rechte nicht stimmen
#RUN usermod --non-unique --uid $UID www-data \
#  && groupmod --non-unique --gid $UID www-data \
#  && chown -R www-data:www-data /var/www
#USER $UID
