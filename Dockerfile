FROM php:8.2-apache

# 1. Cài đặt các extension hệ thống và thư viện hỗ trợ ảnh cho GD
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libavif-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip gd

# 2. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào trước
COPY . .

# --- BƯỚC MỚI: PHÂN QUYỀN TRƯỚC KHI CÀI COMPOSER ---
# Tạo thư mục nếu chưa có và cấp quyền ghi cho user hiện tại (root) để Composer chạy script
RUN mkdir -p storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# 3. Cài đặt các thư viện PHP
# Thêm --no-scripts để tránh chạy artisan command khi chưa đủ quyền, 
# sau đó chạy thủ công hoặc để script khởi động lo.
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 4. Phân quyền chính thức cho Web Server (www-data)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 5. Cấu hình Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. Script khởi động
RUN printf "#!/bin/sh\n\
EFFECTIVE_PORT=\${PORT:-10000}\n\
echo \"Starting Apache on port: \$EFFECTIVE_PORT\"\n\
sed -i \"s/Listen 80/Listen \$EFFECTIVE_PORT/g\" /etc/apache2/ports.conf\n\
sed -i \"s/*:80/*:\$EFFECTIVE_PORT/g\" /etc/apache2/sites-available/000-default.conf\n\
# Chạy discovery và cache khi khởi động\n\
php artisan package:discover --ansi\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
apache2-foreground" > /usr/local/bin/start-app.sh

# 7. Cấp quyền thực thi
RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]