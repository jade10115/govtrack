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

# Clear configuration cache to force load the published cors.php settings
RUN php artisan config:clear

# Run via PHP's production router to ensure /api routes forward safely
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]