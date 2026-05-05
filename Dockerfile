FROM php:8.4-fpm

RUN apt-get update && apt-get install -y nginx git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql mbstring zip gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY laravel /var/www/html

RUN composer install --no-interaction --optimize-autoloader --no-dev

COPY nginx.conf /etc/nginx/conf.d/default.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]

