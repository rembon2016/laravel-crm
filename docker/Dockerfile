FROM php:8.4-fpm-alpine

RUN set -ex \
	&& apk add --update --no-cache \
	postgresql-dev \
	git zlib-dev freetype \
	libpng libjpeg-turbo freetype-dev \
	libpng-dev libjpeg-turbo-dev libwebp-dev \
	libzip-dev zip \
	&& docker-php-ext-configure gd \
	--with-freetype \
	--with-jpeg \
	--with-webp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo_pgsql mysqli pdo pdo_mysql  gd zip

RUN composer install --no-dev --optimize-autoloader

WORKDIR /var/www/app
COPY . .

RUN mkdir -p /var/www/app/vendor

RUN chown www-data:www-data -R public/ storage/ bootstrap/ vendor/

#USER www-data
