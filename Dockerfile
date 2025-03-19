# Build/install JS dependencies
FROM node:16.18.1-alpine3.16 as js-dependencies

# Install dependencies for bower
RUN apk add --no-cache git

RUN npm install -g bower

WORKDIR /app

# install modules
# allow to cache by not copying the whole application code in (yet)
# see: https://stackoverflow.com/questions/35774714/how-to-cache-the-run-npm-install-instruction-when-docker-build-a-dockerfile
COPY package.json ./
COPY package-lock.json ./
RUN npm ci

COPY bower.json ./
RUN bower --allow-root --production install

# Build/install PHP dependencies
FROM composer:2.4.4 as php-dependencies

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --ignore-platform-reqs --optimize-autoloader

# Build final image
FROM php:8.0.26-apache-bullseye

# Install dependencies
RUN apt-get update \
  && apt-get install -y \
      # to install Perl modules
      cpanminus \
      # Perl mysql dependency
      libmariadb-dev-compat \
      # Perl modules
      # Aria DB uses Sybase
      libdbd-sybase-perl \
  # cleaning up unused files
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
  && rm -rf /var/lib/apt/lists/*

RUN cpanm --notest install \
      Array::Utils \
      Const::Fast \
      Data::Dumper \
      Date::Calc \
      DateTime::Format::Strptime \
      DBI \
      DBD::mysql \
      File::Spec \
      Net::HTTP \
      JSON \
      LWP::UserAgent \
      MIME::Lite \
      Net::Address::IP::Local \
      Storable \
      String::Util

# Enable apache2 mods
RUN a2enmod headers rewrite

# Install and enable PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html/opalAdmin
# Change default port to 8080 to allow non-root user to bind port
# Binding port 80 on CentOS seems to be forbidden for non-root users
RUN sed -ri -e 's!Listen 80!Listen 8080!g' /etc/apache2/ports.conf

WORKDIR /var/www/html

# Parent needs to be owned by www-data to satisfy npm
RUN chown -R www-data:www-data /var/www/

USER www-data

# copy only the dependencies in...
COPY --from=js-dependencies --chown=www-data:www-data /app/node_modules ./node_modules
COPY --from=js-dependencies --chown=www-data:www-data /app/bower_components ./bower_components
COPY --from=php-dependencies --chown=www-data:www-data /app/vendor ./vendor

COPY --chown=www-data:www-data . .

EXPOSE 8080
