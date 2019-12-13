<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:44 AM
 */

class OpalProject
{
    protected $opalDB;

    /*
     * constructor of the class
     * */
    public function __construct($OAUserId = false, $sessionId = false) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $OAUserId
        );
        $this->opalDB->setSessionId($sessionId);
    }

    public function getPublicationModulesUser() {
        return $this->opalDB->getPublicationModulesUser();
    }
    public function getPublicationModuleUserDetails($moduleId) {
        return $this->opalDB->getPublicationModuleUserDetails();
    }
}