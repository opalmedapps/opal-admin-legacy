FROM node:16.10.0-alpine3.14 as dependencies

# Install dependencies for bower
RUN apk add --no-cache git

RUN npm install -g bower

WORKDIR /app

# install modules
# allow to cache by not copying the whole application code in (yet)
# see: https://stackoverflow.com/questions/35774714/how-to-cache-the-run-npm-install-instruction-when-docker-build-a-dockerfile
COPY package.json ./
RUN npm install
COPY bower.json ./
RUN bower --allow-root install


FROM php:7.4.24-apache-bullseye

# Install dependencies
RUN apt-get update \
  # libzip for composer
  && apt-get install -y libzip-dev \
  # cleaning up unused files
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
  && rm -rf /var/lib/apt/lists/*

# Enable mod_headers
RUN a2enmod headers
# Enable mod_rewrite
RUN a2enmod rewrite

# Install and enable PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install composer
# see: https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
# see: https://getcomposer.org/download/
COPY install-composer.sh /tmp
RUN /tmp/install-composer.sh
RUN rm /tmp/install-composer.sh
RUN mv /tmp/composer.phar /usr/local/bin/composer

WORKDIR /var/www/html/opalAdmin

# Parent needs to be owned by www-data to satisfy npm
RUN chown -R www-data:www-data /var/www/

USER www-data

# copy only the dependencies in...
COPY --from=dependencies --chown=www-data:www-data /app/node_modules ./node_modules
COPY --from=dependencies --chown=www-data:www-data /app/bower_components ./bower_components

COPY --chown=www-data:www-data . .

RUN composer install
