# Build/install JS dependencies
FROM node:20.11.0-alpine3.19 as js-dependencies

# Install dependencies for bower
RUN apk add --no-cache git \
  && npm install -g bower

WORKDIR /app

# install modules
# allow to cache by not copying the whole application code in (yet)
# see: https://stackoverflow.com/questions/35774714/how-to-cache-the-run-npm-install-instruction-when-docker-build-a-dockerfile
COPY package.json ./
COPY package-lock.json ./
COPY .npmrc ./
RUN npm ci

COPY bower.json ./
RUN bower --allow-root --production install

# Build/install PHP dependencies
FROM composer:2.6.6 as php-dependencies

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --ignore-platform-reqs --optimize-autoloader

# Build final image
FROM php:8.1.27-apache-bookworm

# Install dependencies
RUN apt-get update \
  && apt-get install -y \
      # for cronjobs
      busybox-static \
      # to install Perl modules
      cpanminus \
      # Perl modules
      # Perl mysql dependency
      libmariadb-dev-compat \
  # cleaning up unused files
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
  && rm -rf /var/lib/apt/lists/* \
  && mkdir -p /var/spool/cron/crontabs

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
  && docker-php-ext-install pdo pdo_mysql

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
COPY --from=js-dependencies --chown=www-data:www-data /app/bower_components ./bower_components
COPY --from=php-dependencies --chown=www-data:www-data /app/vendor ./vendor

COPY --chown=www-data:www-data . .
COPY docker/crontab /var/spool/cron/crontabs/www-data

ARG GIT_VERSION='undefined'
ARG GIT_BRANCH='unknown'
RUN echo "$GIT_VERSION" > ./VERSION && echo "$GIT_BRANCH" >> ./VERSION

EXPOSE 8080
