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

# 2. Cài đặt Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào container
COPY . .

# 3. Cài đặt các thư viện PHP (Optimize cho môi trường chạy thật)
# Lưu ý: Đảm bảo bạn đã có file composer.json trong thư mục gốc
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 4. Phân quyền cho Web Server (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 5. Cấu hình Apache: Trỏ thư mục gốc vào /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 6. LỆNH QUAN TRỌNG: Tạo script khởi động để xử lý Port động của Render
RUN printf "#!/bin/sh\n\
# Lấy biến PORT từ Render, nếu không có thì mặc định là 10000\n\
EFFECTIVE_PORT=\${PORT:-10000}\n\
echo \"Starting Apache on port: \$EFFECTIVE_PORT\"\n\
# Ghi đè cấu hình Port của Apache\n\
sed -i \"s/Listen 80/Listen \$EFFECTIVE_PORT/g\" /etc/apache2/ports.conf\n\
sed -i \"s/*:80/*:\$EFFECTIVE_PORT/g\" /etc/apache2/sites-available/000-default.conf\n\
# Các lệnh tối ưu Laravel khi khởi chạy\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
apache2-foreground" > /usr/local/bin/start-app.sh

# 7. Cấp quyền thực thi và thiết lập điểm chạy
RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]