<?php

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;

class Firebase extends Module{
    private $firebase ;
    private $database;
    private $auth;

    public function __construct($guestStatus = false)
    {
        parent::__construct(MODULE_PATIENT, $guestStatus);
        $this->firebase = (new Factory)
            ->withServiceAccount( __DIR__.'/opal-f7ddc-42dc386426ff.json')
            ->withDatabaseUri('https://opal-f7ddc.firebaseio.com/');
        $this->database = $this->firebase->createDatabase();
        $this->auth = $this->firebase->createAuth();
    }

    public function changeEmail($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientEmailParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        return $this->auth->changeUserEmail($post["uid"], $post["email"]);
    }

    public function _validatePatientEmailParams($post){

        $errCode = "";

        if(!array_key_exists("uid", $post) || $post["uid"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("email", $post) || $post["email"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    public function changePassword($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientPasswordParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        return $this->auth->changeUserPassword($post["uid"], $post["password"]);
    }

    public function _validatePatientPasswordParams($post){

        $errCode = "";

        if(!array_key_exists("uid", $post) || $post["uid"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("password", $post) || $post["password"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }
}