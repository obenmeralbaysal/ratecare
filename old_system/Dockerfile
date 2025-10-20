# generic dockerfile for php laravel development
# author umurkaragoz
# version 1.2.4
# use php container as base image
# page: https://hub.docker.com/_/php
FROM php:7.3-apache

ENV ACCEPT_EULA=Y
ENV COMPOSER_MEMORY_LIMIT=-1
# The WORKDIR instruction sets the working directory for any RUN, CMD, ENTRYPOINT, COPY and ADD instructions that follow
WORKDIR /var/www


# ----------------------------------------------------------------------------------------------------------------------------------- php Settings --#

# remove php memory limit
RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# ------------------------------------------------------------------------------------------------------------------------ install Common Packages --#
RUN apt-get update && apt-get install -y \
  zip \
  unzip \
  # required for gd
  libpng-dev \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  # required for php-soap
  libxml2-dev \
  # required for php-zip
  libzip-dev

# install missing php extensions
RUN  docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-install zip soap mysqli pdo pdo_mysql exif \
    # configure & enable exif extension
    && docker-php-ext-configure exif --enable-exif \
    # configure & enable GD extension
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


# ---------------------------------------------------------------------------------------------------------------------------------- set Up Apache --#
ENV APACHE_DOCUMENT_ROOT /var/www/public

# change apache document root to `/var/www/public`
RUN sed -ri -e 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# enable required modules & restart the apache
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod session \
    && service apache2 restart


# -------------------------------------------------------------------------------------------------------------------------------- install Imagick --#
RUN apt-get install -y \
  # see: https://github.com/docker-library/php/issues/105#issuecomment-172652604
  libmagickwand-dev \
  && pecl install imagick \
  && docker-php-ext-enable imagick
