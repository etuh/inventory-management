FROM php:8.4-rc-fpm-alpine3.23

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    mysql-client \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev

# Install PHP extensions required for Laravel & Pest
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    gd \
    bcmath \
    zip \
    mbstring \
    fileinfo \
    dom \
    xml \
    xmlwriter \
    simplexml

# Install Redis extension via PECL (optional)
RUN pecl install redis && \
    docker-php-ext-enable redis

WORKDIR /var/www/html
