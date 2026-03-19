FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip unzip nginx supervisor curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/optik

# Copy composer files dulu (untuk cache layer)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Copy semua file project
COPY . .

# Jalankan post-install scripts
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Set permissions
RUN chown -R www-data:www-data /var/www/optik \
    && chmod -R 755 /var/www/optik \
    && chmod -R 775 /var/www/optik/storage \
    && chmod -R 775 /var/www/optik/bootstrap/cache

# Copy configs
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
