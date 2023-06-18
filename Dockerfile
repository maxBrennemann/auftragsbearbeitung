FROM php:8.2-apache

# Install PDO extension
RUN docker-php-ext-install pdo_mysql

# Copy application files to the container
COPY . /var/www/html

# Set the document root
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80
