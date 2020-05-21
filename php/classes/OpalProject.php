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
    public function __construct($OAUserId = false, $sessionId = false, $guestStatus = false) {
        if(!$guestStatus) {
            $this->opalDB = new DatabaseOpal(
                OPAL_DB_HOST,
                OPAL_DB_NAME,
                OPAL_DB_PORT,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                false,
                $OAUserId
            );
        } else {
            $this->opalDB = new DatabaseOpal(
                OPAL_DB_HOST,
                OPAL_DB_NAME,
                OPAL_DB_PORT,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                false,
                $OAUserId,
                true
            );
        }
        $this->opalDB->setSessionId($sessionId);
    }

    protected function _connectAsMain($OAUserId) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $OAUserId
        );
    }

    protected function _connectAsGuest($OAUserId) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $OAUserId,
            true
        );
    }

    /*
     * gets the list of modules availables
     * @params  void
     * @return  array of modules
     * */
    public function getPublicationModulesUser() {
        return $this->opalDB->getPublicationModulesUser();
    }
}