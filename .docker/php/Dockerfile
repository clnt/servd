ARG PHP_IMAGE="php:7.3"
FROM $PHP_IMAGE
ARG PHP_IMAGE

MAINTAINER MClinton

RUN apt-get update

RUN apt autoremove -y
RUN apt-get install libicu-dev zip -y --no-install-recommends
RUN apt-get install libmcrypt-dev -y --no-install-recommends
RUN apt-get install libpng-dev libjpeg62-turbo-dev libfreetype6-dev -y --no-install-recommends
RUN apt-get install git unzip -y
RUN docker-php-ext-install mysqli pdo_mysql bcmath
RUN if [ "$PHP_IMAGE" = "php:7.3" ] ; then docker-php-ext-configure gd --with-gd --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ --with-freetype-dir=/usr/include ; fi
RUN if [ "$PHP_IMAGE" = "php:7.4" ] ; then docker-php-ext-configure gd --with-jpeg --with-freetype ; fi
RUN docker-php-ext-install gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get install zlib1g-dev libzip-dev -y --no-install-recommends
RUN docker-php-ext-install zip

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

ENV XDEBUG_MODE=coverage

RUN rm -rf /var/lib/apt/lists/*
