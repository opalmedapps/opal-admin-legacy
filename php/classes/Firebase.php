<?php

use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class Firebase extends Module{
    private $firebase ;
    private $database;
    private $auth;

    public function __construct($guestStatus = false)
    {
        parent::__construct(MODULE_PATIENT, $guestStatus);
        try {
            $this->firebase = (new Factory)
                ->withServiceAccount(FIREBASE_SERVICEACCOUNT)
                ->withDatabaseUri(FIREBASE_DATABASEURL);
            $this->database = $this->firebase->createDatabase();
            $this->auth = $this->firebase->createAuth();
        } catch (FirebaseException $err){
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An error occur during firebase connection: " . $err->getMessage());
        }
    }

    public function updateEmail($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientEmailParams($post);
        $errCode = bindec($errCode);
        if ($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        try {
            $this->auth->changeUserEmail($post["uid"], $post["email"]);
        } catch (Throwable $err){
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An error occur during updating email: " . $err->getMessage());
        }
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

    public function updatePassword($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientPasswordParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        try {
            $this->auth->changeUserPassword($post["uid"], $post["password"]);
        } catch (FirebaseException $err){
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An error occur during updating password: " . $err.getMessage());
        }
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