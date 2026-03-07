FROM php:8.2-apache

# Cài đặt các công cụ hệ thống và extension cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Cài đặt Composer chính chủ
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Cài đặt thư viện Laravel (vendor)
RUN composer install --no-dev --optimize-autoloader

# Phân quyền
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN a2enmod rewrite

# Cấu hình Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Tự động chạy migrate và khởi động server
CMD php artisan migrate --force && apache2-foreground