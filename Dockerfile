# SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

# Build/install JS dependencies
FROM node:22.12.0-alpine3.21 AS js-dependencies

WORKDIR /app
 
# install modules
# allow to cache by not copying the whole application code in (yet)
# see: https://stackoverflow.com/questions/35774714/how-to-cache-the-run-npm-install-instruction-when-docker-build-a-dockerfile
COPY package.json ./
COPY package-lock.json ./
COPY .npmrc ./
RUN npm ci

# Build/install PHP dependencies
FROM composer:2.8.6 AS php-dependencies

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --ignore-platform-reqs --optimize-autoloader

# Build final image
FROM php:8.4.2-apache-bookworm

# Install dependencies
RUN apt-get update \
  && apt-get install --no-install-recommends -y \
      # for cronjobs
      busybox-static \
      # to install Perl modules
      cpanminus \
      # Perl modules
      # Perl mysql dependency
      libmariadb-dev-compat \
      # IntlDateFormatter dependency
      libicu-dev \
      # libxml for php-soap
      libxml2-dev \
  # cleaning up unused files
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
  && rm -rf /var/lib/apt/lists/* \
  && mkdir -p /var/spool/cron/crontabs

# satisfy DL4006 (see: https://github.com/hadolint/hadolint/wiki/DL4006)
SHELL ["/bin/bash", "-o", "pipefail", "-c"]
# Install redis
# see: https://stackoverflow.com/a/71607810
# installation asks answers, answer them all with no
RUN echo -n no | pecl install redis

RUN cpanm --notest install \
      Array::Utils \
      Const::Fast \
      Data::Dumper \
      Date::Calc \
      DateTime::Format::Strptime \
      DBI \
      DBD::MariaDB \
      File::Spec \
      Net::HTTP \
      JSON \
      LWP::UserAgent \
      LWP::Protocol::https \
      MIME::Lite \
      Net::Address::IP::Local \
      Storable \
      String::Util

# Enable apache2 mods
RUN a2enmod headers rewrite \
  # Install and enable PHP extensions
  && docker-php-ext-install pdo pdo_mysql intl soap \
  # Enable redis extension
  && docker-php-ext-enable redis

# which php.ini to use, can be either production or development
ARG PHP_ENV=production
ENV PHP_ENV=${PHP_ENV}

COPY docker/update_php_config.sh /tmp

# Change default port to 8080 to allow non-root user to bind port
# Binding port 80 on CentOS seems to be forbidden for non-root users
RUN sed -ri -e 's!Listen 80!Listen 8080!g' /etc/apache2/ports.conf \
  # Use production php.ini file by default
  && mv "/usr/local/etc/php/php.ini-${PHP_ENV}" /usr/local/etc/php/php.ini \
  && /tmp/update_php_config.sh

WORKDIR /var/www/html

# Parent needs to be owned by www-data to satisfy npm
RUN chown -R www-data:www-data /var/www/

USER www-data

# copy only the dependencies in...
COPY --from=js-dependencies --chown=www-data:www-data /app/node_modules ./node_modules
COPY --from=php-dependencies --chown=www-data:www-data /app/vendor ./vendor

# Specifically add only the required files
COPY --chown=www-data:www-data ./favicon.png ./
COPY --chown=www-data:www-data ./index.php ./
COPY --chown=www-data:www-data ./.htaccess ./
COPY --chown=www-data:www-data ./css ./css
COPY --chown=www-data:www-data ./docker ./docker
COPY --chown=www-data:www-data ./fonts ./fonts
COPY --chown=www-data:www-data ./images ./images
COPY --chown=www-data:www-data ./js ./js
COPY --chown=www-data:www-data ./labs ./labs
COPY --chown=www-data:www-data ./php ./php
COPY --chown=www-data:www-data ./publisher ./publisher
COPY --chown=www-data:www-data ./templates ./templates
COPY --chown=www-data:www-data ./translate ./translate
COPY docker/crontab /var/spool/cron/crontabs/www-data
COPY docker/cron.sh /cron.sh

EXPOSE 8080
