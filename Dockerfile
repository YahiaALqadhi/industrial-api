FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd intl

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD php artisan migrate --force && sed -i "s/80/${PORT:-10000}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground