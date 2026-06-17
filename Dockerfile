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

# Clear the configuration cache right before starting the server
RUN php artisan config:clear

# Start the Laravel production server cleanly
# Start the Laravel server pointing directly to the public router file
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000", "--router=server.php"]