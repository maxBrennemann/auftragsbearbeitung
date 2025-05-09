FROM composer:2.8.8 AS composer

# Set working directory
WORKDIR /var/www/html

# Copy the composer.json and composer.lock
COPY composer.json composer.lock ./

# Install the dependencies
RUN composer install --no-scripts

FROM php:8.4.6-apache

# Get UID and GID of host user
ARG UID
ARG GID

# Install some base extensions
RUN apt-get update -y && apt-get install -y libpng-dev
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev
# Install extensions
RUN docker-php-ext-install pdo_mysql mysqli zip gd

# Enable apache mod_rewrite
RUN a2enmod rewrite

# Set the document root
WORKDIR /var/www/html

# https://stackoverflow.com/questions/73991553/permission-denied-for-laravel-and-docker
# user muss danach gesetzt werden, da sonst die Rechte nicht stimmen
RUN usermod --non-unique --uid $UID www-data \
  && groupmod --non-unique --gid $UID www-data \
  && chown -R www-data:www-data /var/www
USER $UID

# Expose port 80
EXPOSE 80
