FROM php:8.2-apache

# Get UID and GID of host user
ARG UID
ARG GID

# Install PDO extension
RUN docker-php-ext-install pdo_mysql

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
