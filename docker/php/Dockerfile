FROM php:7.2-cli

RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        libxml2-dev \
        libc-client-dev \
        libkrb5-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


# Type docker-php-ext-install to see available extensions
# Install php extentions
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install dom json imap mbstring pdo pdo_mysql mysqli

# install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Error log
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/error.ini \
    && echo "error_log = /var/log/php/error.log" >> /usr/local/etc/php/conf.d/error.ini \
    && mkdir /var/log/php

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /opt/app

CMD php
