<?php

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;

class Firebase
{
    private $firebase ;
    private $database;
    private $auth;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount( __DIR__.'/opal-f7ddc-42dc386426ff.json')
            ->withDatabaseUri('https://opal-f7ddc.firebaseio.com/');
        $this->database = $this->firebase->createDatabase();
        $this->auth = $this->firebase->createAuth();
    }

    public function changeEmail($uid, $newEmail){
        $update = $this->auth->changeUserEmail($uid, $newEmail);
        return $update;
    }

    public function changePassword($uid, $newPassword){
        $update = $this->auth->changeUserPassword($uid, $newPassword);
        return $update;
    }
}