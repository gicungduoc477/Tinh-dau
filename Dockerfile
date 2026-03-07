FROM php:8.2-apache

# Cài đặt các extension cần thiết cho MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy toàn bộ code vào trong thư mục server
COPY . /var/www/html

# Phân quyền cho các thư mục quan trọng của Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Bật chế độ rewrite của Apache để chạy được các route Laravel
RUN a2enmod rewrite

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Lệnh tự động chạy migrate mỗi khi khởi động web
CMD php artisan migrate --force && apache2-foreground