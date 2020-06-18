<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:44 AM
 */

class Module
{
    protected $opalDB;
    protected $moduleId;
    protected $read;
    protected $write;
    protected $delete;

    /*
     * constructor of the class
     * */
    public function __construct($moduleId, $guestStatus = false) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $_SESSION["ID"],
            $guestStatus
        );
        $this->opalDB->setSessionId($_SESSION["sessionId"]);
        $this->moduleId = $moduleId;
    }

    protected function _connectAsMain() {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $_SESSION["ID"],
            false
        );
    }

    /**
     * @return mixed
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * @return mixed
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @return mixed
     */
    public function getWrite()
    {
        return $this->write;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->delete;
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