FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# LỆNH QUAN TRỌNG NHẤT: San phẳng thư mục lồng nhau
RUN if [ ! -f "artisan" ]; then \
    SUBFOLDER=$(find . -maxdepth 2 -name "artisan" -print -quit | xargs dirname); \
    if [ -n "$SUBFOLDER" ] && [ "$SUBFOLDER" != "." ]; then \
        echo "Phát hiện code nằm trong thư mục: $SUBFOLDER. Đang di chuyển ra gốc..."; \
        cp -a "$SUBFOLDER/." .; \
    fi; \
    fi

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    a2enmod rewrite

# Cấu hình Apache trỏ thẳng vào thư mục public đã được di chuyển
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Script khởi động
RUN printf "#!/bin/sh\n\
php artisan migrate --force\n\
php artisan config:clear\n\
apache2-foreground" > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
ENTRYPOINT ["/usr/local/bin/start-app.sh"]