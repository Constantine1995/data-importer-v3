#FROM php:8.2-fpm
#
#RUN apt-get -y update && apt-get upgrade -y
#
#RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql
#
#WORKDIR /var/www




FROM php:8.2-fpm

# Установка зависимостей
RUN apt-get -y update && apt-get upgrade -y && apt-get install -y \
    bash \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd && docker-php-ext-enable pdo_mysql

# Копирование Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка wait-for-it.sh
RUN curl -sSL https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh -o /usr/local/bin/wait-for-it.sh \
    && chmod +x /usr/local/bin/wait-for-it.sh

WORKDIR /var/www