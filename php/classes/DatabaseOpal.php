<?php
/**
 * Database Opal access class
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 1:00 PM
 */

class DatabaseOpal extends DatabaseAccess {

    public function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $newUserId = false) {
        parent::__construct($newServer, $newDB, $newPort, $newUserDB, $newPass, $newUserId);
        $newUserId = strip_tags($newUserId);
        $userInfo = $this->_getUserInfoFromDB($newUserId);
        $this->userId = $userInfo["userId"];
        $this->username = $userInfo["username"];
        $this->userRole = $userInfo["userRole"];
    }

    protected function _getUserInfoFromDB($newUserId) {
        $newUserId = strip_tags($newUserId);
        if($newUserId == "" || $newUserId <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
        $result = $this->_fetchAll(SQL_OPAL_SELECT_USER_INFO,
            array(
                array("parameter"=>":userId","variable"=>$newUserId,"data_type"=>PDO::PARAM_INT),
            ));

        if (count($result) != 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
        }

        $resultRole = $this->_fetchAll(SQL_OPAL_SELECT_USER_ROLE,
            array(
                array("parameter"=>":userId","variable"=>$newUserId,"data_type"=>PDO::PARAM_INT),
            ));
        if(count($resultRole) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");

        $result = $result[0];
        $tempRole = array();
        foreach($resultRole as $role)
            array_push($tempRole, $role["RoleSerNum"]);
        $result["userRole"] = $tempRole;
        return $result;
    }

    function countLockedQuestionnaires($questionnairesList) {
        return $this->_fetch(SQL_OPAL_LIST_QUESTIONNAIRES_FROM_QUESTIONNAIRE_CONTROL,
            array(
                array("parameter"=>":questionnaireList","variable"=>$questionnairesList,"data_type"=>PDO::PARAM_STR),
            ));
    }

}