ARG PHP_VERSION
ARG NODE_VERSION
ARG COMPOSER_VERSION

FROM node:${NODE_VERSION}-alpine3.15 AS node
FROM composer:${COMPOSER_VERSION} AS composer
WORKDIR /

FROM php:${PHP_VERSION}-fpm-alpine3.15 as php

LABEL maintainer="Matt Clinton <matt@mattc.dev>"

USER root

ARG CHANGE_SOURCE=false
RUN if [ ${CHANGE_SOURCE} = true ]; then \
    # Change application source from dl-cdn.alpinelinux.org to aliyun source
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/' /etc/apk/repositories \
;fi

ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

RUN addgroup -g ${PGID} servd && \
    adduser -u ${PUID} -G servd -h /home/servd -D servd;

USER root

#---------------------------------------------------------------------
# Setup PHP Repository
#---------------------------------------------------------------------

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

#---------------------------------------------------------------------
# Initial Installation
#---------------------------------------------------------------------
RUN apk update \
    && apk upgrade \
    && apk --update-cache add ca-certificates \
    && apk add --no-cache \
        libedit-dev \
        libxml2-dev \
        htop \
        rsync \
        git \
        curl \
        vim \
        nano \
        tree \
        yarn \
        mysql-client \
        postgresql-client \
        nginx \
        supervisor \
        patch;

#---------------------------------------------------------------------
# PHP Installation
#---------------------------------------------------------------------
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install uploadprogress \
    && docker-php-ext-enable uploadprogress \
    && apk del .build-deps $PHPIZE_DEPS \
    && chmod uga+x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions bcmath \
        bz2 \
        calendar \
        curl \
        exif \
        fileinfo \
        ftp \
        gd \
        gettext \
        igbinary \
        imagick \
        imap \
        intl \
        ldap \
        mcrypt \
        mysqli \
        pdo \
        pdo_mysql \
        pgsql \
        mbstring \
        soap \
        pcov \
        sockets \
        sodium \
        sysvsem \
        sysvshm \
        xmlrpc \
        xsl \
        redis \
        xdebug \
        yaml \
        zip \
        msgpack \
#    &&  echo -e "\n opcache.enable=1 \n opcache.enable_cli=1 \n opcache.memory_consumption=128 \n opcache.interned_strings_buffer=8 \n opcache.max_accelerated_files=4000 \n opcache.revalidate_freq=60 \n opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    &&  echo -e "\n xdebug.remote_enable=1 \n xdebug.remote_host=localhost \n xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    &&  echo -e "\n xhprof.output_dir='/var/tmp/xhprof'" >> /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini;

#---------------------------------------------------------------------
# PHP-FPM Configuration
#---------------------------------------------------------------------
COPY config/fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY config/php.ini /usr/local/etc/php/conf.d/custom.ini

#---------------------------------------------------------------------
# Nginx Configuration
#---------------------------------------------------------------------
COPY config/nginx.conf /etc/nginx/nginx.conf

#---------------------------------------------------------------------
# Supervisor Configuration
#---------------------------------------------------------------------
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

#---------------------------------------------------------------------
# NodeJS Installation (specified version)
#---------------------------------------------------------------------
RUN apk del nodejs

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/share /usr/local/share
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

#---------------------------------------------------------------------
# Composer Installation (specified version)
#---------------------------------------------------------------------

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

ENV PATH /home/servd/.composer/vendor/bin:$PATH

ARG INSTALL_DRUSH
ENV INSTALL_DRUSH ${INSTALL_DRUSH}
ARG DRUSH_VERSION
ENV DRUSH_VERSION ^${DRUSH_VERSION}

USER root

RUN chmod +x /usr/bin/supervisord

# Set default work directory
WORKDIR /var/www

# Set ownership and permissions so non-root user can access relevant directories.
RUN chmod 775 /var/www && \
    chown -R servd:servd /var/www && \
    chown -R servd:servd /var/lib/nginx && \
    chown -R servd:servd /etc/nginx && \
    chown -R servd:servd /var/log/nginx && \
    chown -R servd:servd /usr/lib/nginx/modules && \
    chown -R servd:servd /run/nginx

# Create www-default directory for default system pages.
RUN mkdir -p /var/www-default
RUN chmod 775 /var/www-default && \
    chown -R servd:servd /var/www-default

EXPOSE 9000 8080 443

USER servd

#---------------------------------------------------------------------
# Drush Installation
#---------------------------------------------------------------------
RUN if [ ${INSTALL_DRUSH} = true ]; then \
    composer global require drush/drush:${DRUSH_VERSION} \
;fi

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

USER root

#HEALTHCHECK --timeout=30s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
