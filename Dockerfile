FROM php:8.2-apache

# 1. Cài đặt các extension cần thiết
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# Copy toàn bộ nội dung từ máy bạn vào container
COPY . .

# 3. SỬA LỖI ĐƯỜNG DẪN: Nếu code bị nằm trong thư mục con (như CDPHP), đưa nó ra ngoài
RUN if [ ! -d "/var/www/html/public" ]; then \
    cp -r /var/www/html/*/. /var/www/html/ 2>/dev/null || true; \
    fi

# 4. Cài đặt thư viện
RUN composer install --no-dev --optimize-autoloader

# 5. Phân quyền và bật Rewrite cho Apache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN a2enmod rewrite

# 6. Cấu hình Apache trỏ thẳng vào thư mục public của Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 7. Script khởi động: Link storage -> Migrate -> Bật Web
RUN printf "#!/bin/sh\n\
php artisan storage:link --force\n\
php artisan migrate --force\n\
php artisan config:clear\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

ENTRYPOINT ["/usr/local/bin/start-app.sh"]