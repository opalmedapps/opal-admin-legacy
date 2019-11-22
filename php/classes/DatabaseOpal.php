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

    function getPublications() {
        $sqlModule = array();
        $moduleSQLCode = $this->_fetchAll(SQL_OPAL_BUILD_PUBLICATION_VIEW, array());
        foreach ($moduleSQLCode as $module)
            if (strip_tags($module["sqlPublicationList"]) != "")
                array_push($sqlModule, $module["sqlPublicationList"]);
        $sqlModule = implode(SQL_GENERAL_UNION_ALL, $sqlModule);
        return $this->_fetchAll($sqlModule, array());
    }

    function getPublicationsPerModule($moduleId) {
        $result = array();
        if($moduleId == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Module cannot be found. Access denied.");
        $module = $this->_fetch(SQL_OPAL_GET_MODULE_BY_ID, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
        $sqlFetchPerModule = $module["unique"] == 1 ? $module["sqlPublicationUnique"] : $module["sqlPublicationMultiple"];
        if($sqlFetchPerModule == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing code. Access denied.");
        $result["publications"] = $this->_fetchAll($sqlFetchPerModule,  array(array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT)));
        $result["triggers"] = $this->_fetchAll(SQL_OPAL_GET_TRIGGERS_PER_MODULE, array(array("parameter"=>":moduleId","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
        return $result;
    }

    function getPublicationChartLogs() {
        $sqlModule = array();
        $moduleSQLCode = $this->_fetchAll(SQL_OPAL_BUILD_PUBLICATION_VIEW, array());
        foreach ($moduleSQLCode as $module) {
            if (strip_tags($module["sqlPublicationChartLog"]) != "")
                array_push($sqlModule, $module["sqlPublicationChartLog"]);
        }
        $sqlModule = "SELECT * FROM (" . implode( SQL_GENERAL_UNION_ALL, $sqlModule) . ") AS i ORDER BY moduleId, ID, x";
        return $this->_fetchAll($sqlModule, array());
    }

    function getPublicationModules() {
        return $this->_fetchAll(SQL_OPAL_GET_ALL_PUBLICATION_MODULES, array());
    }

    function getQuestionnaireListLogs($ids) {
        $sqlToFetch = str_replace("%%IDS%%", implode(", ", $ids), SQL_OPAL_GET_QUESTIONNAIRE_LIST_LOGS);
        return $this->_fetchAll($sqlToFetch, array());
    }

    /*
     * Get all the details of a specific published questionnaire.
     * @params  Questionnaire serial number (int)
     * @return  array of details of the published questionnaire itself
     * */
    function getPublishedQuestionnaireDetails($questionnaireId) {
        return $this->_fetchAll(SQL_OPAL_GET_QUESTIONNAIRE_CONTROL_DETAILS,
            array(
                array("parameter"=>":QuestionnaireControlSerNum","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get all the triggers of a specific published questionnaire.
     * @params  Questionnaire serial number (int)
     * @return  array of details of the published questionnaire itself
     * */
    function getPublishedQuestionnaireTriggers($questionnaireId) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS_QUESTIONNAIRE_CONTROL,
            array(
                array("parameter"=>":QuestionnaireControlSerNum","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get all the triggers of a specific published questionnaire.
     * @params  Questionnaire serial number (int)
     * @return  array of details of the published questionnaire itself
     * */
    function getPublishedQuestionnaireFrequencyEvents($questionnaireId) {
        return $this->_fetchAll(SQL_OPAL_GET_FREQUENCY_EVENTS_QUESTIONNAIRE_CONTROL,
            array(
                array("parameter"=>":ControlTableSerNum","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            ));
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

    /*
     * insert multiple frequency events
     * @params  array of records to insert
     * @return  number of records affected
     * */
    function insertMultipleFrequencyEvents($toInsert) {
        $this->_insertMultipleRecordsIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $toInsert);
    }

    /*
     * Delete a specific frequency event.
     * @params  SerNum from the QuestionnaireControl to delete in frequency event table
     * @return  number of records affected
     * */
    function deleteFrequencyEvent($controlTableSerNum) {
        $toDelete = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
        );
        $this->_execute(SQL_OPAL_DELETE_FREQUENCY_EVENTS_TABLE, $toDelete);
    }

    /*
     * Returns the filters for a specific questionnaire control
     * @params  questionnaire control ID
     * @return  array of filters
     * */
    function getFilters($questionnaireControlSerNum) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS,
            array(
                array("parameter"=>":QuestionnaireControlSerNum","variable"=>$questionnaireControlSerNum,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Returns the filters with a specific control table ser num
     * @params  questionnaire control ID
     * @return  array of filters
     * */
    function getFiltersByControlTableSerNum($controlTableSerNum) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS_BY_CONTROL_TABLE_SERNUM,
            array(
                array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Delete a specific frequency event trigger when doing an update
     * @params  filterId, filterType and SerNum from the QuestionnaireControl
     * @return  Total of records modified.
     * */
    function deleteFilters($filterId, $filterType, $controlTableSerNum) {
        $toDelete = array(
            array("parameter"=>":FilterId","variable"=>$filterId),
            array("parameter"=>":FilterType","variable"=>$filterType),
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum),
        );
        return $this->_execute(SQL_OPAL_DELETE_FILTERS, $toDelete);
    }

    /*
     * update the publication flag of a questionnaire.
     * @params  id of questionnaire, and value of the status (both integers)
     * @return  number of record affected
     * */
    function updatePublicationFlags($id, $value) {

        $sqlToUpdate = SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS;
        $toInsert = array(
            array("parameter"=>":QuestionnaireControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":PublishFlag","variable"=>$value,"data_type"=>PDO::PARAM_INT),
        );
        if($value == 1) {
            $sqlToUpdate = SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS_LAST_PUBLISHED;
            array_push($toInsert, array("parameter"=>":LastPublished","variable"=>date("Y-m-d H:i:s")));
        }

        return $this->_execute($sqlToUpdate, $toInsert);
    }

    /*
     * update the publication flag of a specific module.
     * @params  id of questionnaire, and value of the status (both integers)
     * @return  number of record affected
     *
     *     SET PublishFlag = :PublishFlag, LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
     *
     * */
    function updatePublicationFlag($tableName, $primaryKey, $publishFlag, $primaryId) {
        $sqlToUpdate = str_replace("%%ID_FIELD%%", $primaryKey, str_replace("%%TABLE_NAME%%", $tableName, SQL_OPAL_UPDATE_PUBLICATION_STATUS_FLAG));
        $toUpdate = array(
            array("parameter"=>":PublishFlag","variable"=>$publishFlag,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ID","variable"=>$primaryId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SessionId","variable"=>$this->getSessionId(),"data_type"=>PDO::PARAM_STR),
        );
        return $this->_execute($sqlToUpdate, $toUpdate);
    }

    function getPublicationModulesUser(){
        return $this->_fetchAll(SQL_OPAL_GET_ALL_PUBLICATION_MODULES_USER, array());
    }

    function getPublicationModuleUserDetails($moduleId){
        return $this->_fetch(SQL_OPAL_GET_PUBLICATION_MODULES_USER_DETAILS, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
    * Update questionnaireControl table with changes made by user
    * @params  SerNum of the QuestionnaireControl table updated
    * @return  Total of records modified.
    * */
    function updateQuestionnaireControl($record) {
        return $this->_updateRecordIntoTable(SQL_OPAL_UPDATE_QUESTIONNAIRE_CONTROL, $record);
    }

    /*
     * Update the modification history filter table with new changes made
     * @params  record to insert
     * @return  total records updated
     * */
    function updateFiltersModificationHistory($record) {
        return $this->_updateRecordIntoTable(SQL_OPAL_UPDATE_FILTERSMH, $record);
    }

    /*
     * Insert new trigger events
     * @params  record to insert
     * @return  ID of the insertion
     * */
    function insertReplaceFrequencyEvent($record) {
        return $this->_insertRecordIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $record);
    }

    /*
     * Delete the end date of repeat frequency eventswhen doing an update
     * @params  SerNum of the QuestionnaireControl table updated
     * @return  Total of records modified.
     * */
    function deleteRepeatEndFromFrequencyEvents($controlTableSerNum) {
        $toInsert = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(SQL_OPAL_DELETE_REPEAT_END_FROM_FREQUENCY_EVENTS, $toInsert);
    }

    /*
     * Delete all other meta tags not required in Frequency Events, when doing an update
     * @params  SerNum of the QuestionnaireControl table updated
     * @return  Total of records modified.
     * */
    function deleteOtherMetasFromFrequencyEvents($controlTableSerNum) {
        $toInsert = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(SQL_OPAL_DELETE_OTHER_METAS_FROM_FREQUENCY_EVENTS, $toInsert);
    }

    /*
     * Returns all the non deleted posts
     * TODO implement pagination/lazy loading system
     * @params  void
     * @returns array of post
     * */
    function getPosts() {
        return $this->_fetchAll(SQL_OPAL_GET_POSTS);
    }

    /*
     * Inserts a new post with the info of the user/sessionId and datetime.
     * @params  array with post info
     * @returns new id of the post
     * */
    function insertPost($toInsert) {
        $toInsert["LastUpdatedBy"] = $this->getOAUserId();
        $toInsert["SessionId"] = $this->getSessionId();
        $toInsert["DateAdded"] = date("Y-m-d H:i:s");
        return $this->_insertRecordIntoTable(OPAL_POST_TABLE, $toInsert);
    }

    /*
     * Gets the post details with a single ID/Serial
     * @params  ID/serial of the post
     * @returns array with details of the post
     * */
    function getPostDetails($postId) {
        return $this->_fetch(SQL_OPAL_GET_POST_DETAILS,
            array(array("parameter"=>":PostControlSerNum","variable"=>$postId,"data_type"=>PDO::PARAM_INT))
        );
    }

    /*
     * Updates a post details into the database after they were validated/sanitized
     * @params  array with post details (sanitized/validated)
     * @returns number of lines modified.
     * */
    function updatePost($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();
        return $this->_updateRecordIntoTable(SQL_OPAL_UPDATE_POST, $toUpdate);
    }

    /*
     * This function marks a specific record in a specific table as deleted.
     *
     * WARNING!!! No record should be EVER be removed from the opalDB database! It should only being marked as
     * being deleted ONLY after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param   Table name (string)
     *          record to mark as deleted in the table (BIGINT)
     * @return  result of deletion (boolean)
     * */
    function markAsDeleted($tableName, $primaryKey, $recordId) {
        $sql = str_replace("%%PRIMARY_KEY%%", strip_tags($primaryKey),str_replace("%%TABLENAME%%", strip_tags($tableName),SQL_OPAL_MARK_RECORD_AS_DELETED));
        return $this->_execute($sql, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SessionId","variable"=>$this->getSessionId(),"data_type"=>PDO::PARAM_STR),
        ));
    }
}