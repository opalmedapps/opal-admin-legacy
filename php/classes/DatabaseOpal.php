<?php
/**
 * Database Opal access class
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 1:00 PM
 */

class DatabaseOpal extends DatabaseAccess {

    /*
     * Constructor of the class
     * */
    public function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $newOAUserId = false) {
        parent::__construct($newServer, $newDB, $newPort, $newUserDB, $newPass, $newOAUserId);
        $newOAUserId = strip_tags($newOAUserId);
        $userInfo = $this->_getUserInfoFromDB($newOAUserId);
        $this->OAUserId = $userInfo["OAUserId"];
        $this->username = $userInfo["username"];
        $this->userRole = $userInfo["userRole"];
    }

    /*
     * Get the user information based on the user ID
     * @params  user ID (int)
     * @return  array of the user informations and roles
     * */
    protected function _getUserInfoFromDB($newOAUserId) {
        $newOAUserId = strip_tags($newOAUserId);
        if($newOAUserId == "" || $newOAUserId <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
        $result = $this->_fetchAll(SQL_OPAL_SELECT_USER_INFO,
            array(
                array("parameter"=>":OAUserId","variable"=>$newOAUserId,"data_type"=>PDO::PARAM_INT),
            ));

        if (count($result) != 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
        }

        $resultRole = $this->_fetchAll(SQL_OPAL_SELECT_USER_ROLE,
            array(
                array("parameter"=>":OAUserId","variable"=>$newOAUserId,"data_type"=>PDO::PARAM_INT),
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

    /*
     * counts the number of locked questions based on a list of questionnaire IDs
     * @params  array of questionnaire ID
     * @return  total of questionnaire locked (array)
     * */
    function countLockedQuestionnaires($questionnairesList) {
        return $this->_fetch(SQL_OPAL_LIST_QUESTIONNAIRES_FROM_QUESTIONNAIRE_CONTROL,
            array(
                array("parameter"=>":questionnaireList","variable"=>$questionnairesList,"data_type"=>PDO::PARAM_STR),
            ));
    }

    /*
     * Returns the list of published questionnaires
     * @params  void
     * @return  array of questionnaires
     * */
    function getPublishedQuestionnaires() {
        return $this->_fetchAll(SQL_OPAL_GET_PUBLISHED_QUESTIONNAIRES, array());
    }

    /*
     * Insert a new published questionnaire in questionnaire control table
     * @params  array of the published questionnaire
     * @return  ID of the entry
     * */
    function insertPublishedQuestionnaire($toInsert) {
        return $this->_insertRecordIntoTable(OPAL_QUESTIONNAIRE_CONTROL_TABLE, $toInsert);
    }

    /*
     * Insert filters in the filter table
     * @params  array of the published questionnaire
     * @return  ID of the entry
     * */
    function insertMultipleFilters($toInsert) {
        $this->_insertMultipleRecordsIntoTable(OPAL_FILTERS_TABLE, $toInsert);
    }

    function insertMultipleFrequencyEvents($toInsert) {
        $this->_insertMultipleRecordsIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $toInsert);
    }

    /*
     * Returns the filters for a specific questionnaire controll
     * @params  questionnaire control ID
     * @return  array of filters
     * */
    function getFilters($questionnaireControlId) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS,
            array(
                array("parameter"=>":questionnaireControlId","variable"=>$questionnaireControlId,"data_type"=>PDO::PARAM_STR),
            ));
    }

    /*
     * update the publication flag of a questionnaire.
     * @params  id of questionnaire, and value of the status (both integers)
     * @return  number of record affected
     * */
    function updatePublicationFlags($id, $value) {

        $sqlToUpdate = SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS;
        $toInsert = array(
            array("parameter"=>":QuestionnaireControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":PublishFlag","variable"=>$value,"data_type"=>PDO::PARAM_STR),
        );
        if($value == 1) {
            $sqlToUpdate = SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS_LAST_PUBLISHED;
            array_push($toInsert, array("parameter"=>":LastPublished","variable"=>date("Y-m-d H:i:s")));
        }

        return $this->_execute($sqlToUpdate, $toInsert);
    }
}