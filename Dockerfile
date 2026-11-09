FROM node:24-bookworm-slim AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM composer:2 AS composer
FROM php:8.5-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=5M\npost_max_size=16M\n' > "$PHP_INI_DIR/conf.d/uploads.ini"

WORKDIR /var/www/html

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && mkdir -p storage/app/public storage/app/private \
        storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && php artisan storage:link --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

COPY <<'APACHE' /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    DocumentRoot /var/www/html/public
    SetEnvIf X-Forwarded-Proto "^https$" HTTPS=on

    <Directory /var/www/html/public>
        Options FollowSymLinks
        AllowOverride FileInfo Options=Indexes,MultiViews
        Require all granted
    </Directory>

    <Directory /var/www/html/public/storage>
        AllowOverride None
        php_admin_flag engine Off
        <FilesMatch "(?i)(^\.|\.ph(p[0-9]*|ar|tml)$)">
            Require all denied
        </FilesMatch>
    </Directory>
</VirtualHost>
APACHE

RUN apache2ctl configtest
