FROM php:8.2-apache

# 1. Cài đặt các công cụ hệ thống và extension cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# 3. Cài đặt thư viện (Bỏ lệnh storage:link ở đây để tránh lỗi Build)
RUN composer install --no-dev --optimize-autoloader

# 4. Phân quyền và bật Rewrite cho Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN a2enmod rewrite

# 5. Cấu hình Apache trỏ vào thư mục public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. Tạo file Script khởi động (Giải pháp dứt điểm lỗi 127)
RUN echo '#!/bin/sh\n\
php artisan storage:link --force\n\
php artisan migrate --force\n\
php artisan config:clear\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

# Chạy bằng Script để đảm bảo mọi lệnh đều được thực hiện
CMD ["/usr/local/bin/start-app.sh"]