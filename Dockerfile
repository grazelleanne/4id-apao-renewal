FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

RUN mkdir -p storage/limits \
    && chown -R www-data:www-data storage

CMD ["sh", "-c", "sed -ri \"s!Listen 80!Listen ${PORT:-10000}!\" /etc/apache2/ports.conf && sed -ri \"s!<VirtualHost \\*:80>!<VirtualHost *:${PORT:-10000}>!\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]