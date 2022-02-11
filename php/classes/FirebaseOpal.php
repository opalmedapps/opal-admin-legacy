<?php

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseOpal extends HelpSetup
{
    private $firebase ;
    private $database;
    private $auth;

    /**
     * Constructor of the class
     */
    function __construct() {
        $this->firebase = (new Factory)
            ->withServiceAccount(FIREBASE_SERVICEACCOUNT)
            ->withDatabaseUri(FIREBASE_DATABASEURL);
        $this->database = $this->firebase->createDatabase();
        $this->auth = $this->firebase->createAuth();
    }

    /**
     * Update the email address of a given patient in firebase.
     * @param $uid string - patient user name
     * @param $email string - new email
     * @return array - user information
     */
    function updateEmail($uid, $email) {
        return $this->auth->changeUserEmail($uid, $email);
    }

    /**
     * Update the password of a given patient in firebase.
     * @param $uid string - patient user name
     * @param $password string - new password
     * @return array - user information
     */
    function updatePassword($uid, $password) {
        return $this->auth->changeUserPassword($uid, $password);
    }
}