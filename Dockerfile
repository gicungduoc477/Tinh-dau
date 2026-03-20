FROM php:8.2-apache

# 1. Cài đặt các extension hệ thống và thư viện hỗ trợ ảnh cho GD
# Bổ sung đầy đủ thư viện để GD xử lý được JPEG, WebP, AVIF
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

# 2. Cài đặt Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào container
COPY . .

# --- PHÂN QUYỀN TRƯỚC KHI CÀI COMPOSER ---
# Đảm bảo thư mục tồn tại để không bị lỗi khi Composer chạy script post-install
RUN mkdir -p storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# 3. Cài đặt các thư viện PHP (Tối ưu cho production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 4. Phân quyền chính thức cho Web Server (www-data)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 5. Cấu hình Apache: Trỏ thư mục gốc vào /public theo chuẩn Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. Script khởi động: Xử lý Port động của Render và FIX LỖI CACHE
# LƯU Ý: Dùng config:clear để tránh lỗi "Undefined array key cloud" do cache cũ
RUN printf "#!/bin/sh\n\
EFFECTIVE_PORT=\${PORT:-10000}\n\
echo \"Starting Apache on port: \$EFFECTIVE_PORT\"\n\
sed -i \"s/Listen 80/Listen \$EFFECTIVE_PORT/g\" /etc/apache2/ports.conf\n\
sed -i \"s/*:80/*:\$EFFECTIVE_PORT/g\" /etc/apache2/sites-available/000-default.conf\n\
\n\
# Các lệnh làm sạch môi trường mỗi khi khởi động\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
php artisan cache:clear\n\
php artisan package:discover --ansi\n\
\n\
# Tạo link storage để hiển thị ảnh local (nếu có)\n\
php artisan storage:link --force\n\
\n\
echo \"Environment cleared and storage linked.\"\n\
apache2-foreground" > /usr/local/bin/start-app.sh

# 7. Cấp quyền thực thi và thiết lập điểm chạy
RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]