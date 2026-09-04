FROM php:8.4-fpm-alpine


RUN set -ex \
	&& apk add --update --no-cache \
	postgresql-dev \
	git zlib-dev freetype \
	libpng libjpeg-turbo freetype-dev \
	libpng-dev libjpeg-turbo-dev libwebp-dev \
	libzip-dev zip unzip \
	nodejs npm \
	&& docker-php-ext-configure gd \
	--with-freetype \
	--with-jpeg \
	--with-webp

RUN docker-php-ext-install pdo_pgsql mysqli pdo pdo_mysql gd zip calendar

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Override PHP (memory_limit, upload size) — lihat docker/php.ini
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-custom.ini

WORKDIR /var/www/app
COPY . .

# Buang sisa cache framework yang mungkin terbawa (menyebut provider dev seperti
# Laravel\Boost yang tidak ada di --no-dev) agar artisan bisa boot saat build.
RUN rm -f bootstrap/cache/*.php

# 1) Dependency PHP. vendor ini dipakai untuk men-seed named volume "vendor_volume"
#    saat container pertama dibuat, agar bind-mount source tidak menimpanya.
# Tanpa --prefer-dist agar saat download zip GitHub (codeload) gagal (HTTP 400),
# composer otomatis fallback clone via git.
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 2) Build asset frontend. Plugin Wayfinder di vite.config.ts butuh `php artisan`,
#    jadi butuh .env + APP_KEY agar artisan bisa boot saat build.
RUN cp -n .env.example .env || true \
	&& php artisan key:generate --force || true



RUN chown -R www-data:www-data public/ storage/ bootstrap/ vendor/

# Liveness check: pastikan php-fpm menerima koneksi di port 9000. Dipakai nginx
# (depends_on: condition: service_healthy) agar nginx tidak start sebelum fpm siap.
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s --retries=5 \
	CMD php -r "exit(@fsockopen('127.0.0.1', 9000) ? 0 : 1);"

#USER www-data
