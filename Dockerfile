FROM php:8.4-apache

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev unzip \
    && docker-php-ext-install curl pdo_mysql mbstring bcmath \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html
ENV APP_ENV=production
RUN printf 'expose_php=Off\ndisplay_errors=Off\nlog_errors=On\npost_max_size=8M\nupload_max_filesize=2M\n' > /usr/local/etc/php/conf.d/security.ini
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

RUN mkdir -p storage/limits storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && secret_group="$(getent group 1000 | cut -d: -f1)" \
    && if [ -z "$secret_group" ]; then groupadd --gid 1000 rendersecrets; secret_group=rendersecrets; fi \
    && usermod --append --groups "$secret_group" www-data

CMD ["sh", "-c", "sed -ri \"s!Listen 80!Listen ${PORT:-10000}!\" /etc/apache2/ports.conf && sed -ri \"s!<VirtualHost \\*:80>!<VirtualHost *:${PORT:-10000}>!\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
