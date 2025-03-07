<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

use Google\Auth\CredentialsLoader;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseOpal extends HelpSetup
{
    private $firebase ;
    private $auth;

    /**
     * Constructor of the class
     */
    function __construct() {
        try{
            $this->firebase = (new Factory)
                ->withServiceAccount(FIREBASE_SERVICEACCOUNT)
                ->withDatabaseUri(FIREBASE_DATABASEURL);
            $this->auth = $this->firebase->createAuth();
        } catch (Throwable $err){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY, "An error occur during external connection: " . $err->getMessage());
        }
    }

    /**
     * Update the email address of a given patient in firebase.
     * @param $uid string - patient user name
     * @param $email string - new email
     * @return array - user information
     */
    function updateEmail($uid, $email) {
        try {
            return $this->auth->changeUserEmail($uid, $email);
        } catch (Throwable $err) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY, "An error occur during updating email: " . $err->getMessage());
        }
    }

    /**
     * Update the password of a given patient in firebase.
     * @param $uid string - patient user name
     * @param $password string - new password
     * @return array - user information
     */
    function updatePassword($uid, $password) {
        try{
            return $this->auth->changeUserPassword($uid, $password);
        } catch (Throwable $err) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY, "An error occur during updating password: " . $err->getMessage());
        }
    }

    /**
     * Disable the given patient account in firebase.
     * @param $uid string - patient user name
     * @return array - user information
     */
    function disableUser($uid) {
        try {
            return $this->auth->disableUser($uid);
        } catch (Throwable $err) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY, "An error occur during disabling a user: " . $err->getMessage());
        }
    }

    /**
     * @description Uses Firebase credentials with the Google Auth Library to retrieve a short-lived OAuth 2.0 access token,
     *              which can be used to authorize push notifications sent with FCM (Firebase Cloud Messaging).
     *              See: https://firebase.google.com/docs/cloud-messaging/migrate-v1#use-credentials-to-mint-access-tokens
     *              See: https://github.com/googleapis/google-api-php-client/issues/1715#issuecomment-533217233
     *
     *              Requirement: FIREBASE_SERVICEACCOUNT must contain a path to a valid service account file.
     *
     * @throws Exception If the function fails to read the service account file at FIREBASE_SERVICEACCOUNT.
     */
    static function getFCMAuthToken() {
        $scope = 'https://www.googleapis.com/auth/firebase.messaging';

        // Read the Firebase service account from its file
        $serviceAccount = json_decode(file_get_contents(FIREBASE_SERVICEACCOUNT), true);
        if (is_null($serviceAccount)) throw new Exception("Failed to read Firebase service account at: " . FIREBASE_SERVICEACCOUNT);

        // Use the service account to get an authentication token
        $credentials = CredentialsLoader::makeCredentials($scope, $serviceAccount);
        $token = $credentials->fetchAuthToken();
        return $token["access_token"];
    }
}
