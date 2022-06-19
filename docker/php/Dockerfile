FROM php:8-fpm-alpine

# Install production dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    oniguruma-dev \
    bash \
    curl \
    git \
    mysql-client \
    rabbitmq-c-dev

# Install PECL and PEAR extensions
RUN pecl install xdebug amqp

# Install and enable php extensions
RUN docker-php-ext-enable xdebug

RUN docker-php-ext-install \
    bcmath \
    mbstring \
    pdo \
    pdo_mysql \
    sockets

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD docker/php/schedule-run /etc/crontabs/schedule-run
ADD docker/php/conf.d/* /usr/local/etc/php/conf.d/
RUN chmod 0755 /etc/crontabs/schedule-run
RUN crontab -u www-data /etc/crontabs/schedule-run

WORKDIR /var/www
