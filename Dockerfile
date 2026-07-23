FROM node:20-bookworm AS frontend

WORKDIR /var/www

COPY package.json package-lock.json* ./
RUN npm install

COPY . .
RUN npm run build

FROM php:8.3-fpm-bookworm

WORKDIR /var/www

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip zlib1g-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=frontend /var/www/public/build ./public/build
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
