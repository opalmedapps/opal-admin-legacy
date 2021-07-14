<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:44 AM
 */

abstract class Module
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

    protected function _insertAudit($module, $method, $arguments, $access, $username = false) {
        $toInsert = array(
            "module"=>$module,
            "method"=>$method,
            "argument"=>json_encode($arguments),
            "access"=>$access,
            "ipAddress"=>HelpSetup::getUserIP(),
        );
        if($username) {
            $toInsert["createdBy"] = $username;
            $this->opalDB->insertAuditForceUser($toInsert);
        }
        else
            $this->opalDB->insertAudit($toInsert);
    }

    /*
     * Connect to the DB as a main user and not as a guest
     * @params  void
     * @return void
     * */
    protected function _connectAsMain($userId = false) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            (!$userId ? $_SESSION["ID"] : $userId),
            false
        );
    }

    /*
     * Validate the read access requested by the user is authorized. If not, returns an error 403. It also
     * @params  void
     * @return  false or error 403
     * */
    public function checkReadAccess($arguments = array()) {
        $arguments = HelpSetup::arraySanitization($arguments);
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        if(!(($this->access >> 0) & 1)) {
            $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_DENIED);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        }
        $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_GRANTED);
        return false;
    }

    /*
     * Validate the write access requested by the user is authorized. If not, returns an error 403
     * @params  void
     * @return  false or error 403
     * */
    public function checkWriteAccess($arguments = array()) {
        $arguments = HelpSetup::arraySanitization($arguments);
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        if(!(($this->access >> 1) & 1)) {
            $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_DENIED);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        }
        $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_GRANTED);
        return false;
    }

    /*
     * Validate the delete access requested by the user is authorized. If not, returns an error 403
     * @params  void
     * @return  false or error 403
     * */
    public function checkDeleteAccess($arguments = array())
    {
        $arguments = HelpSetup::arraySanitization($arguments);
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        if(!(($this->access >> 2) & 1)) {
            $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_DENIED);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        }
        $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_GRANTED);
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

    /*
     * Get the list of educational materials. Protected function so any module can call it the same way when needed
     * without having to call the module educational materials itself, but cannot be called from outside.
     * @params  void
     * @return  $result - array - list of educational materials
     * */
    protected function _getListEduMaterial() {
        $results = $this->opalDB->getEducationalMaterial();
        foreach($results as &$row) {
            $row["tocs"] = $this->opalDB->getTocsContent($row["serial"]);
        }

        return $results;
    }

    /*
     * Get the details of aneducational material. Protected function so any module can call it the same way when needed
     * without having to call the module educational materials itself, but cannot be called from outside.
     * @params  void
     * @return  $result - array - list of educational materials
     * */
    protected function _getEducationalMaterialDetails($eduId) {
        $results = $this->opalDB->getEduMaterialDetails($eduId);
        $results["tocs"] = $this->opalDB->getTocsContent($results["serial"]);
        return $results;
    }

    /*
     * Get the activate source database (Aria, ORMS, local, etc...)
     * @params  void
     * @return  $assignedDB : array - source database ID
     * */
    protected function _getActiveSourceDatabase(){
        $assigned = $this->opalDB->getActiveSourceDatabase();
        $assigned = HelpSetup::arraySanitization($assigned);
        $assignedDB = array();
        foreach($assigned as $item) {
            array_push($assignedDB, $item["SourceDatabaseSerNum"]);
        }
        return $assignedDB;
    }

    /**
     * Validate basic information info of patient and site and make sure they exist
     * @param $post - contain MRN and site to validate
     * @param $patientSite - hospital site info
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit informations
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     * @return string - validation code in binary
     */
    protected function _validateBasicPatientInfo(&$post, &$patientSite) {
        $errCode = "";

        // 1st bit - MRN
        if(!array_key_exists("mrn", $post) || $post["mrn"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 2nd bit - Site
        if(!array_key_exists("site", $post) || $post["site"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 3rd bit - MRN and site combo must exists
        if(bindec($errCode) != 0) {
            $patientSite = array();
            $errCode = "1" . $errCode;
        } else {
            $patientSite = $this->opalDB->getPatientSite($post["mrn"], $post["site"]);
            if(count($patientSite) != 1) {
                $patientSite = array();
                $errCode = "1" . $errCode;
            }
            else {
                $patientSite = $patientSite[0];
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }
}