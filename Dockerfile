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

# 3. Cài đặt thư viện Laravel (vendor) và tối ưu hóa
RUN composer install --no-dev --optimize-autoloader

# 4. Phân quyền cho các thư mục quan trọng để Laravel ghi được log/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN a2enmod rewrite

# 5. Cấu hình Apache trỏ thẳng vào thư mục public của Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. Lệnh khởi động: Dọn cache -> Migrate tạo bảng -> Seed nạp dữ liệu -> Chạy Apache
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    apache2-foreground