<!--
SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>

SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Opal labs

## Development

Copy `.env.template` to `.env`  and fill in the environment variable values. For example:

   ```dotenv
    ENV="dev"
    OPAL_DB_HOST="127.0.0.1"
    OPAL_DB_PORT="8889"
    OPAL_DB_DATABASE="OpalDB_dev"
    OPAL_DB_USER="root"
    OPAL_DB_PASSWORD="root"
    LABS_OASIS_WSDL_URL="http://YOUR-HOST-OR-IP/cis-ws/vitalSign?WSDL"
    LABS_PUSH_NOTIFICATION_URL="http://YOUR-HOST-OR-IP/opalAdmin/publisher/php/sendPushNotificationPerl.php"
   ```

### Docker

This project contains a `Dockerfile` as well as a `docker-compose.yml` to run it within a Docker container. First, build the image via `docker compose build`.

Then start the container with `docker compose up`. Once the container is up and running, you can access the API documentation via `http://localhost:8081/`.

In order to access the database running on your machine (whether in a container or not) the DB host needs to be `host.docker.internal` (macOS and Windows).

If port `8081` is already in use, change the port mapping in `docker-compose.yml`.

To force a re-build of the image. You may call `docker compose build` before running or `docker compose up --build` to force a re-build when running the container.

### Server/Local Machine Installation

1. Make sure dependencies are met:
   - PHP 8.0
   - composer
   - redis
2. Install project dependencies:

    ```bash
    composer install
    ```

3. Create `.env` file with the following flags, and replace for values that make sense.

   ```dotenv
    ENV="dev"
    OPAL_DB_HOST="127.0.0.1"
    OPAL_DB_PORT="8889"
    OPAL_DB_DATABASE="OpalDB_dev"
    OPAL_DB_USER="root"
    OPAL_DB_PASSWORD="root"
    LABS_OASIS_WSDL_URL="http://YOUR-HOST-OR-IP/cis-ws/vitalSign?WSDL"
    LABS_PUSH_NOTIFICATION_URL="http://YOUR-HOST-OR-IP/opalAdmin/publisher/php/sendPushNotificationPerl.php"
   ```

4. Test installation by running unit tests:

   ```bash
    ./vendor/phpunit/phpunit/phpunit 
   ```

   The expected output should look like this:

   ```bash
    PHPUnit 8.5.3 by Sebastian Bergmann and contributors.

    ........................                    24 / 24 (100%)

    Time: 331 ms, Memory: 6.00 MB

    OK (24 tests, 43 assertions)
   ```

### Production

The main image is `opalmedapps/opal-labs` which depends on `redis`. The expectation is that the Redis container is accessible via the `redis` hostname from the app.

The following `docker-compose.yml` file can be used to set up `opal-labs` in production:

```yml
services:
  redis:
    image: redis:7.0.5-alpine3.16
  app:
    build: .
    image: opalmedapps/opal-labs
    ports:
      - 8081:80
    depends_on:
      - redis
    volumes:
      - ./.env:/var/www/html/.env
```

## Design

_Please see the [sequence diagram](diagram.png) for the workflow details. The source code of the diagram can be found [here](https://gitlab.com/opalmedapps/docs/-/blob/main/docs/development/architecture/diagrams/labs.puml?ref_type=heads)._

The notification for test results arrives from the interface engine (IE) via post request to a PHP script.

Using these notifications, one may then obtain the test results using the Oasis webservice via the Oasis [Soap client](https://en.wikipedia.org/wiki/SOAP):

```php
response = oasis_soap_client->getLabList(oasisPatientId, fromDate, toDate); // dates in "Y-m-d"
```
