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

    /*
     * Get the details of a module
     * @param   $moduleId (int) ID of the module
     * @return  array of details of the module
     * */
    public function getPublicationModuleUserDetails($moduleId) {
        return $this->opalDB->getPublicationModuleUserDetails();
    }

    /*
     * Recursive function that sanitize the data
     * @params  array to sanitize
     * @return  array sanitized
     * */
    function arraySanitization($arrayForm) {
        $sanitizedArray = array();
        foreach($arrayForm as $key=>$value) {
            $key = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $key);
            if(is_array($value))
                $value = $this->arraySanitization($value);
            else
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);
            $sanitizedArray[$key] = $value;
        }
        return $sanitizedArray;
    }
}