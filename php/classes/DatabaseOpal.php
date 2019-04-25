<?php
/**
 * Database Opal access class
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 1:00 PM
 */

class DatabaseOpal extends DatabaseAccess {

    function getUserInfo($userId) {
        $defaultUSer = array("userId"=>-1, "username"=>"PROBLEM_NO_USER_FOUND", "language"=>"en");
        if ($userId == "")
            return $defaultUSer;

        $result = $this->fetchAll(SQL_OPAL_SELECT_USER_INFO,
            array(
                array("parameter"=>":userId","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
            ));

        if (count($result) <=0) {
            return $defaultUSer;
        }
        else
            return $result[0];
    }

    function countLockedQuestionnaires($questionnairesList) {
        return $this->fetch(SQL_OPAL_LIST_QUESTIONNAIRES_FROM_QUESTIONNAIRE_CONTROL,
            array(
                array("parameter"=>":questionnaireList","variable"=>$questionnairesList,"data_type"=>PDO::PARAM_STR),
            ));
    }

    function getUserRole($userId) {
        return $this->fetchAll(SQL_OPAL_SELECT_USER_ROLE,
            array(
                array("parameter"=>":userId","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
            ));
    }
}