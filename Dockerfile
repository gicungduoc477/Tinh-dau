FROM php:8.2-apache

# 1. Cài đặt các extension cần thiết (bao gồm GD để xử lý ảnh)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài đặt Composer chính chủ
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# Copy toàn bộ nội dung từ GitHub vào container
COPY . .

# 3. SỬA LỖI ĐƯỜNG DẪN: Nếu không thấy folder public ở gốc, tìm và đưa code ra ngoài
RUN if [ ! -d "/var/www/html/public" ]; then \
    echo "Phát hiện code nằm sai vị trí, đang tự động sửa đường dẫn..."; \
    SUBDIR=$(find . -maxdepth 2 -name "public" -type d | cut -d'/' -f2); \
    if [ -n "$SUBDIR" ] && [ "$SUBDIR" != "." ]; then \
        cp -a "$SUBDIR/." .; \
    fi; \
    fi

# 4. Cài đặt thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# 5. Phân quyền cho Web Server (Quan trọng để upload ảnh)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 6. Cấu hình Apache trỏ thẳng vào thư mục public của Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 7. Script khởi động: Tạo link ảnh -> Migrate -> Xóa cache -> Chạy Apache
RUN printf "#!/bin/sh\n\
php artisan storage:link --force\n\
php artisan migrate --force\n\
php artisan config:clear\n\
php artisan cache:clear\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

# Sử dụng ENTRYPOINT để đảm bảo Render chạy script này
ENTRYPOINT ["/usr/local/bin/start-app.sh"]