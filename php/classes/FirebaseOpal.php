<?php

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
        } catch (FirebaseException $err){
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
}