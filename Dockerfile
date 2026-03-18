FROM php:8.2-apache

# 1. Cài đặt extension cần thiết
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 2. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# 3. Cài thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# 4. Phân quyền cho Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# 5. Cấu hình Apache: Trỏ DocumentRoot và Đổi Port sang 10000 cho Render
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i "s/Listen 80/Listen \${PORT:-10000}/g" /etc/apache2/ports.conf && \
    sed -i "s/*:80/*:\${PORT:-10000}/g" /etc/apache2/sites-available/000-default.conf

# 6. Script khởi động
RUN printf "#!/bin/sh\n\
php artisan config:clear\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]