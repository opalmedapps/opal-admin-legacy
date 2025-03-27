<!--
SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>

SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Legacy OpalAdmin

> [!CAUTION]
> This version of OpalAdmin is in maintenance mode. The functionality provided by it is going to be migrated to the [new OpalAdmin](https://github.com/opalmedapps/opal-admin) over time. See the [documentation on the future direction](https://docs.opalmedapps.com/development/architecture/#future-vision).
> New features are only added to `opal-admin`. However, we still fix issues and keep dependencies up to date.

OpalAdmin is an administrative tool for managing and tagging personal health information that is published to Opal.

## Getting Started

This project contains a `Dockerfile` as well as a `docker-compose.yml` to run it as a container.

### Step 1: Create the `.env` file

Copy the `.env.sample` to `.env` and fill out the required fields (database credentials, Firebase information, and auth token).

```shell
cp .env.sample .env
```

If the database is enforcing secure transport (SSL/TLS traffic encryption), also update the values for the SSL environment variables:
`DATABASE_USE_SSL=1` and `SSL_CA=/var/www/html/certs/ca.pem` after copying the `ca.pem` file into the certs directory.

### Step 2: Add the `.npmrc` file

This project uses [AngularJS](https://angularjs.org/) which reached end of life in January 2022.
This project uses a long-term support version of AngularJS provided by [XLTS.dev](https://www.xlts.dev/).
If you have an `npm` token to retrieve this version from their registry, place the `.npmrc` file containing the credentials in the root directory.

You can also use the [last available version](https://www.npmjs.com/package/angular) of AngularJS (version 1.8.3).
To do so, change the value for the `angular` dependency in `package.json` to `angular@1.8.3` and run `npm install` to update the lock file.

### Step 3: Start the container

You can then bring up the container:

```shell
docker compose up app
```

This brings up the app container without the separate cron container.
If you need to run periodic scripts (such as the publication ones), also start the cron container.
Either bring everything up via `docker compose up` or in a separate terminal run `docker compose up cron`.

Once the image is built and the container running, you can access opalAdmin via `http://localhost:8082/` from your browser.

You can then log in with the test user credentials:

* Username: `admin`
* Password: `123456Opal!!`

## Built With

* [Angular](https://angularjs.org) - The JS web framework used
* [Bootstrap](http://getbootstrap.com) - CSS
* [PHP](http://php.net)
* [Perl](http://perldoc.perl.org)
* [JavaScript](https://www.javascript.com)

## Testing Push Notifications

1. Make sure your Firebase service account file is available inside the container.
2. Make sure the following `.env` variables have been correctly set:
   1. `FIREBASE_ADMIN_KEY_PATH` (to the absolute path of the file inside the container).
   2. `PUSH_NOTIFICATION_URL`
   3. `PUSH_NOTIFICATION_ANDROID_URL` (if using Android)
3. Build a copy of your local Opal app and install it on a mobile device. Make sure to allow push notifications.

After the above setup, you can test push notifications as follows using the test script.

### Step 1

Log into the app so that a row in `PatientDeviceIdentifier` gets updated with your device's push notification registration ID (in the column `RegistrationId`).
This is required because the registration ID may change at any time (including each time you reinstall the app).

### Step 2

Use a database client to check the PatientDeviceIdentifier table: copy the `RegistrationId` from your latest login and keep it somewhere to be used later.

### Step 3

Run the test script in the container:

```bash
docker compose exec app php publisher/php/tests/testPushNotification.php <deviceID> <deviceType> <language>
```

* `<registrationID>`: Value from the column `RegistrationId` mentioned above
* `<deviceType>`: `0` (iOS) or `1` (Android)
* `<language>`: `en` (English) or `fr` (French)

### Step 4

Output will be printed to the terminal to indicate whether the notification was successfully sent, or if there was an error.
If successful, you’ll receive a test push notification on your device.

## Labs Design

_Please see the [sequence diagram](diagram.png) for the workflow details. The source code of the diagram can be found [here](https://gitlab.com/opalmedapps/docs/-/blob/main/docs/development/architecture/diagrams/labs.puml?ref_type=heads)._

The notification for test results arrives from the interface engine (IE) via POST request to a PHP script.

Using these notifications, one may then obtain the test results using the Oasis webservice via the Oasis [SOAP](https://en.wikipedia.org/wiki/SOAP) service:

```php
response = oasis_soap_client->getLabList(oasisPatientId, fromDate, toDate); // dates in "Y-m-d"
```
