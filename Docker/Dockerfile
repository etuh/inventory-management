FROM php:8.4-rc-fpm-alpine3.23

# Install system dependencies
# (Uncomment gd-related packages below if you need image manipulation in the future)
RUN apk add --no-cache \
    curl \
    postgresql-dev \
    mysql-client \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev
# libpng-dev
# libjpeg-turbo-dev
# freetype-dev

# Install PHP extensions required for Laravel & Pest
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    zip \
    mbstring \
    fileinfo \
    dom \
    xml \
    xmlwriter \
    simplexml

WORKDIR /var/www/html
