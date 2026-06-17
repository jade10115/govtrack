# Use the official PHP 8.2 image
FROM php:8.2-cli

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory inside the server
WORKDIR /app

# Copy all your Laravel files into the server
COPY . .

# Run composer to install your Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Clear configuration cache
RUN php artisan config:clear

# Serve the app with public/ as the document root so requests boot Laravel
# (public/index.php) directly. This avoids depending on a separate
# server.php router file, which was missing and caused requests to bypass
# Laravel's middleware stack entirely (breaking CORS, auth, everything).
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]