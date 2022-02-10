<?php

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseOpal extends HelpSetup
{
    private $firebase ;
    private $database;
    private $auth;

    /* constructor that connects to the firebase */
    function __construct() {
        $this->firebase = (new Factory)
            ->withServiceAccount(FIREBASE_SERVICEACCOUNT)
            ->withDatabaseUri(FIREBASE_DATABASEURL);
        $this->database = $this->firebase->createDatabase();
        $this->auth = $this->firebase->createAuth();
    }

    function updateEmail($uid, $email){
        return $this->auth->changeUserEmail($uid, $email);
    }

    function updatePassword($uid, $password){
        return $this->auth->changeUserPassword($uid, $password);
    }
}