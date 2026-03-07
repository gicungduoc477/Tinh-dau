FROM php:8.2-apache

# 1. Cài đặt các công cụ hệ thống và extension cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Cài đặt Composer chính chủ từ Docker Hub
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# 3. Cài đặt thư viện và ÉP BUỘC quét lại toàn bộ Class (Autoload)
RUN composer install --no-dev --optimize-autoloader && \
    composer dump-autoload --optimize

# 4. Phân quyền cho các thư mục quan trọng
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN a2enmod rewrite

# 5. Cấu hình Apache trỏ thẳng vào thư mục public của Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. Lệnh khởi động: Migrate -> Seed -> Xóa cache -> Chạy Apache
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan config:clear && \
    php artisan cache:clear && \
    apache2-foreground