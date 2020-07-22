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

            /*
             * If the session expire, force the front end to display the login page. Otherwise, update the timer.
             * */
            if (isset($_SESSION['lastActivity']) && (time() - $_SESSION['lastActivity'] > PHP_SESSION_TIMEOUT))
                HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "config error");
            else
                $_SESSION['lastActivity'] = time(); // update last activity time stamp

            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > PHP_SESSION_TIMEOUT) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }

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

    /*
     * gets the list of available modules
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
}