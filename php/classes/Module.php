<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:44 AM
 */

abstract class Module extends OpalProject
{
    protected $moduleId;
    protected $access;

    /*
     * constructor of the class
     * */
    public function __construct($moduleId, $guestStatus = false) {
        parent::__construct($_SESSION["ID"], $guestStatus);
        $this->opalDB->setSessionId($_SESSION["sessionId"]);
        $this->moduleId = $moduleId;

        if(!$guestStatus) {

            /*
             * If the session expire, force the front end to display the login page. Otherwise, update the timer.
             * */
            if (isset($_SESSION['lastActivity']) && (time() - $_SESSION['lastActivity'] > PHP_SESSION_TIMEOUT))
                HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "not authenticated");
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

}