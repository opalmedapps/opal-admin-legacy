# opalAdmin

OpalAdmin is an administrative tool for managing and tagging personal health information that is published to Opal.

## Getting Started

This project contains a `Dockerfile` as well as a `docker-compose.yml` to run it as a container.

Copy the `.env.sample` to `.env` and fill out the required fields (database credentials, Firebase information, and auth token).

```shell
cp .env.sample .env
```

If the database is enforcing secure transport (SSL/TLS traffic encryption), also update the values for the SSL environment variables:
`DATABASE_USE_SSL=1` and `SSL_CA=/var/www/html/certs/ca.pem` after copying the `ca.pem` file into the certs directory.

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

## Version file

The `VERSION` file in the project root is used to display environment information.
It is updated in a CI job when building the container image.
