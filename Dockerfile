FROM php:8.2-apache

# 1. Cài đặt các công cụ cần thiết cho Laravel (GD, PDO, Zip)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# Copy toàn bộ nội dung từ GitHub vào container
COPY . .

# 3. SỬA LỖI CẤU TRÚC THƯ MỤC: Tự động tìm file artisan
# Nếu bạn để code trong folder "Tinh-dau", lệnh này sẽ lôi nó ra ngoài /var/www/html/
RUN if [ ! -f "artisan" ]; then \
    echo "Phát hiện code nằm trong thư mục con, đang đưa ra gốc..."; \
    SUBFOLDER=$(find . -maxdepth 2 -name "artisan" -print -quit | xargs dirname); \
    if [ -n "$SUBFOLDER" ] && [ "$SUBFOLDER" != "." ]; then \
        cp -a "$SUBFOLDER/." .; \
        rm -rf "$SUBFOLDER"; \
    fi; \
    fi

# 4. Cài đặt thư viện Laravel (Chạy sau khi đã đưa code ra đúng chỗ)
RUN composer install --no-dev --optimize-autoloader

# 5. Phân quyền và bật Rewrite cho Apache
# Gán quyền cho www-data để Apache có thể đọc/ghi folder storage
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 6. Cấu hình Apache: Trỏ DocumentRoot vào thư mục /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 7. Script khởi động: Chạy Link Storage -> Migrate -> Clear Cache -> Chạy Apache
RUN printf "#!/bin/sh\n\
php artisan storage:link --force\n\
php artisan migrate --force\n\
php artisan config:clear\n\
php artisan cache:clear\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

# Sử dụng ENTRYPOINT để Render luôn chạy script này đầu tiên
ENTRYPOINT ["/usr/local/bin/start-app.sh"]