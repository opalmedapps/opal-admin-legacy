<!--
SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>

SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Opal labs

![Test](https://github.com/Sable/opal-labs/workflows/Test/badge.svg)

## Table of Contents

[[_TOC_]]

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
    LABS_EMAIL_RECIPIENTS="john.kildea@mcgill.ca"
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
    LABS_EMAIL_RECIPIENTS="john.kildea@mcgill.ca"
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

## Problem and Design

_Please see the [sequence diagram](diagram.png) for the workflow details. The source code of the diagram can be found [here](https://gitlab.com/opalmedapps/docs/-/blob/main/docs/development/architecture/diagrams/labs.puml?ref_type=heads)._

The notification for test results arrives from the interface engine (IE) via post request to a PHP script; this notification contains the following fields:

```php
PatientId, Site, Ramq, SpecimenReceivedDate, ResultDate // Dates are specified using: "Y-m-d hh:mm:ss"
```

Here, the `(PatientId, Site)|RAMQ` represent the identifier for the patient, the `SpecimenReceivedDate` represents the date the result was collected, and the lastly the `ResultDate` represents the date the lab result was published.

The notifications for test results from the interface engine (IE) are received in batches for a given test. I.e.,
if a patient gets a test at certain time, the results do not arrive all at once from the IE, but rather in batches where we may get different test result notifications for the same test of a patient.

Using these notifications, one may then obtain the test results using the Oasis webservice via the Oasis [Soap client](https://en.wikipedia.org/wiki/SOAP):

```php
response = oasis_soap_client->getLabList(oasisPatientId, fromDate, toDate); // dates in "Y-m-d"
```

There are a few problems with this current workflow:

1. If the Oasis API is queried as soon as the notification arrives a race condition occurs in the case where Oasis has not processed the test results by the time we obtain the notification and query Oasis. Note that Oasis sometimes takes hours to process the results magnifying this problem.
2. The API offered by Oasis receives two dates (fromDate, toDate) with no times, this means if two notifications arrive on the same date but different time for a given patient, we cannot distinguish the results to the query in Oasis if queried at the same time.
3. The results returned from Oasis do not offer a link to the original notification from the IE, therefore we can never be sure if we have obtained all the lab results for a given notification since they may be from an earlier notification on the same date.
4. If we have different batches for the same test, the notifications will be undistinguishible from one another, which means the call to Oasis is the same.
5. The only real way to distinguish between notifications is by checking the difference in the results returned by Oasis.

### Interim Solution

To mitigate this problem, we have created a workflow using a queue. The following image offers the current architecture:

![image](./design.png)

Here are some details not conveyed by the diagram:

- If there is a failure inserting the notification into the queue, the IE queues the notifications, and we get alerted upon those failures.
- Every notification may be in three possible states: `PENDING`, when the notification is yet to be process successfully; `COMPLETED`, when the notification is successfully processed; and `MAX_ATTEMPTS_REACHED`, when the notification has reached ten attempts in processing.
- The IE places a post request to [api/test-notification.php](./api/test-notification.php). This script adds the notification to `TestNotificationQueue` with the following structure:

    ```php
    `TestResultNotificationQueueId`
    `PatientSerNum`
    `Mrn` 
    `Site` 
    `Ramq` 
    `SourceId` // To replace eventually the ambiguity
    `SpecimenDateTime`
    `ResultDateTime` 
    `Status` enum('COMPLETED','PENDING','MAX_ATTEMPTS_REACHED') // Default PENDING
    `ImportDateTime` 
    `InsertedRows` // Default 0
    `UpdatedRows` // Default 0 
    `ProcessingAttemptNumber` // Default 0
    `LastProcessingDateTime` // Default NULL
    `LastProcessingError` // Default NULL
    ```

    This structure keeps track of processing attemps, attempt times, and errors in processing. Each attempt for a given notification is then recorded via SQL triggers in the `TestNotificationProcessingLog` table.

- A failed attempt for a test notification may result from:
  - Error connecting to the database
  - Error connecting to Oasis
  - Empty results in Oasis (This means we have reached our race condition, the test results have not been processed by Oasis).
- A notification is considered completed if results from Oasis are not empty and no errors occured during the test result table updates in the OpalDB, the `TestResultProcessor` module returns a success flag to indicate this success flag along with how many rows were affected during this processing.
- If a notification failed to be processed, it remains in the PENDING state. In this case, the processing attempt is increased, and the error is recorded. The cronjob will then try to pick it up again and attempt to process it in the cronjob's next run.
- We only send a push notification for the lab result if there are any affected rows in the notification processing.
- We only send an e-mail if a notification reaches 10 failed attempts.
- The cronjob running every 5 minutes only processes the PENDING notifications; to guarantee the capturing of most if not all the results we use two other cronjobs:
  - [./api/test-notification-queue-day.php](./api/test-notification-queue-day.php): Processes ALL the notifications (PENDING/COMPLETED/MAX_ATTEMPTS_REACHED) for 25 hours back and runs at 8am and 6pm everyday.
  - [./api/test-notification-queue-week.php](./api/test-notification-queue-day.php): Processes ALL the notifications (PENDING/COMPLETED/MAX_ATTEMPTS_REACHED) at the end of the week once a week.
