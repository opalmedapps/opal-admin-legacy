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
    protected $access;

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

        if(!$guestStatus) {
            if (!$_SESSION["userAccess"][$moduleId])
                HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Module session cannot be found. Please contact your administrator.");
            $this->access = intval($_SESSION["userAccess"][$moduleId]["access"]);
        }
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

    public function getModuleId()
    {
        return $this->moduleId;
    }

    public function checkReadAccess()
    {
        if(!(($this->access >> 0) & 1))
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        return false;
    }

    public function checkWriteAccess()
    {
        if(!(($this->access >> 1) & 1))
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        return false;
    }

    public function checkDeleteAccess()
    {
        if(!(($this->access >> 2) & 1))
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        return false;
    }

    public function getRead() {
        return (($this->access >> 0) & 1);
    }

    public function getWrite() {
        return (($this->access >> 1) & 1);
    }

    public function getDelete() {
        return (($this->access >> 2) & 1);
    }

    /*
     * gets the list of modules availables
     * @params  void
     * @return  array of modules
     * */
    public function getPublicationModulesUser() {
        return $this->opalDB->getPublicationModulesUser();
    }

    protected function _getListEduMaterial() {
        $results = $this->opalDB->getEducationalMaterial();
        foreach($results as &$row) {
            $row["tocs"] = $this->opalDB->getTocsContent($row["serial"]);
        }

        return $results;
    }

    protected function _getEducationalMaterialDetails($eduId) {
        $results = $this->opalDB->getEduMaterialDetails($eduId);
        $results["tocs"] = $this->opalDB->getTocsContent($results["serial"]);

        return $results;
    }

    protected function _getEducationalMaterialListLogs($eduIds) {
        return $this->opalDB->getEduMaterialLogs($eduIds);
    }
}