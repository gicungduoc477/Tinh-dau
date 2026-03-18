FROM php:8.2-apache

# 1. Cài đặt các thư viện hệ thống cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài đặt Composer chính chủ
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# 3. KỸ THUẬT CHUYÊN SÂU: Tự động đưa code ra thư mục gốc nếu bị lồng folder
RUN if [ ! -f "artisan" ]; then \
    SUBFOLDER=$(find . -maxdepth 2 -name "artisan" -print -quit | xargs dirname); \
    [ -n "$SUBFOLDER" ] && [ "$SUBFOLDER" != "." ] && cp -a "$SUBFOLDER/." . ; \
    fi

# 4. Cài đặt các gói thư viện PHP (Optimize cho Production)
RUN composer install --no-dev --optimize-autoloader

# 5. PHÂN QUYỀN: Gán quyền cho User www-data (User của Web Server)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 6. CẤU HÌNH APACHE: Trỏ DocumentRoot vào /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 7. SCRIPT KHỞI ĐỘNG (ENTRYPOINT)
RUN printf "#!/bin/sh\n\
php artisan migrate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]