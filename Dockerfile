# Use the official PHP 8.2 image with a full Apache Production Server
FROM php:8.2-apache

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite and mod_headers
RUN a2enmod rewrite headers

# Change Apache's default root to Laravel's public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# THE ULTIMATE ROUTING FIX: Force Apache to send ALL missing routes to index.php
RUN echo "<Directory /var/www/html/public>\n    AllowOverride All\n    FallbackResource /index.php\n</Directory>" >> /etc/apache2/apache2.conf

# GOD-MODE CORS: Force Apache to attach headers to EVERYTHING
RUN echo "Header always set Access-Control-Allow-Origin \"https://govtrack-frontend.vercel.app\"" >> /etc/apache2/apache2.conf
RUN echo "Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"" >> /etc/apache2/apache2.conf
RUN echo "Header always set Access-Control-Allow-Headers \"Origin, Content-Type, Accept, Authorization, X-Requested-With, X-CSRF-TOKEN\"" >> /etc/apache2/apache2.conf
RUN echo "Header always set Access-Control-Allow-Credentials \"true\"" >> /etc/apache2/apache2.conf

# Configure Apache to listen on Render's required port (10000)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:10000/g' /etc/apache2/sites-available/000-default.conf

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy all your Laravel files into the server
COPY . .

# Run composer to install your Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Give Apache full permission to write to Laravel's cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache