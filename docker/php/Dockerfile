FROM php:7.4-fpm-alpine

# Install production dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    oniguruma-dev \
    bash \
    curl \
    git

# Install PECL and PEAR extensions
RUN pecl install xdebug

# Install and enable php extensions
RUN docker-php-ext-enable xdebug

RUN docker-php-ext-install \
    mbstring \
    pdo \
    pdo_mysql

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
