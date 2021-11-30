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
    public function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $dsn = false, $newOAUserId = false, $guestAccess = false) {
        parent::__construct($newServer, $newDB, $newPort, $newUserDB, $newPass, $dsn);
        if (!$guestAccess) {
            $newOAUserId = strip_tags($newOAUserId);

            if($_SESSION["ID"] && $_SESSION["ID"] == $newOAUserId) {
                $this->OAUserId = $_SESSION["ID"];
                $this->type = $_SESSION["type"];
                $this->username = $_SESSION["username"];
                $this->userRole = $_SESSION["roleId"];
            }
            else {
                $userInfo = $this->_getUserInfoFromDB($newOAUserId);
                if(count($userInfo) == 1)
                    $userInfo = $userInfo[0];
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "User not authenticated.");
                $this->OAUserId = $userInfo["OAUserId"];
                $this->type = $userInfo["type"];
                $this->username = $userInfo["username"];
                $this->userRole = $userInfo["userRole"];
            }
        }
    }

    /*
     * Get the user information based on the user ID
     * @params  user ID (int)
     * @return  array of the user informations and roles
     * */
    protected function _getUserInfoFromDB($newOAUserId) {
        $newOAUserId = strip_tags($newOAUserId);
        if($newOAUserId == "" || $newOAUserId <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "User not authenticated.");
        $result = $this->_fetchAll(SQL_OPAL_SELECT_USER_INFO,
            array(
                array("parameter"=>":OAUserSerNum","variable"=>$newOAUserId,"data_type"=>PDO::PARAM_INT),
            ));

        if (!is_array($result) || count($result) != 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "User not authenticated.");
        }

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
     * This function fetches the SQL list of the different modules associated to the publication. It replaces the flags
     * with the correct table names, and fetch all the publications.
     * @params  none
     * @returns array of results
     * */
    function getPublications() {
        $sqlModule = array();
        $moduleSQLCode = $this->_fetchAll(SQL_OPAL_BUILD_PUBLICATION_VIEW, array());
        foreach ($moduleSQLCode as $module)
            if (strip_tags($module["sqlPublicationList"]) != "")
                array_push($sqlModule, $module["sqlPublicationList"]);
        $sqlModule = implode(SQL_GENERAL_UNION_ALL, $sqlModule);
        $sqlModule = str_replace("%%QUESTIONNAIRE_DB%%", QUESTIONNAIRE_DB_2019_NAME, $sqlModule);
        $sqlModule = str_replace("%%DICTIONARY%%", DICTIONARY_TABLE, $sqlModule);
        $sqlModule = str_replace("%%QUESTIONNAIRE%%", QUESTIONNAIRE_TABLE, $sqlModule);
        $sqlModule = str_replace("%%FILTERS%%", OPAL_FILTERS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%QUESTIONNAIRECONTROL%%", OPAL_QUESTIONNAIRE_CONTROL_TABLE, $sqlModule);
        $sqlModule = str_replace("%%POSTCONTROL%%", OPAL_POST_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MODULE%%", OPAL_MODULE_TABLE, $sqlModule);
        $sqlModule = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_CONTROL_TABLE, $sqlModule);
        $sqlModule = str_replace("%%PHASEINTREATMENT%%", OPAL_PHASE_IN_TREATMENT_TABLE, $sqlModule);

        return $this->_fetchAll($sqlModule, array());
    }

    /*
     * This function fetches the SQL list of the different modules associated to the custom codes. It replaces the flags
     * with the correct table names, and fetch all the custom codes.
     * @params  none
     * @returns array of results
     * */
    function getCustomCodes() {
        $sqlModule = array();
        $moduleSQLCode = $this->_fetchAll(SQL_OPAL_BUILD_CUSOM_CODE_VIEW, array());
        foreach ($moduleSQLCode as $module)
            if (strip_tags($module["sqlCustomCode"]) != "")
                array_push($sqlModule, $module["sqlCustomCode"]);
        $sqlModule = implode(SQL_GENERAL_UNION_ALL, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_ALIAS%%", OPAL_MASTER_SOURCE_ALIAS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_DIAGNOSTIC%%", OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_TEST_RESULT%%", OPAL_MASTER_SOURCE_TEST_RESULT_TABLE, $sqlModule);
        $sqlModule = str_replace("%%ALIAS_EXPRESSION%%", OPAL_ALIAS_EXPRESSION_TABLE, $sqlModule);
        $sqlModule = str_replace("%%TEST_RESULT_EXPRESSION%%", OPAL_TEST_RESULT_EXPRESSION_TABLE, $sqlModule);
        $sqlModule = str_replace("%%DIAGNOSIS_CODE%%", OPAL_DIAGNOSIS_CODE_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MODULE%%", OPAL_MODULE_TABLE, $sqlModule);
        return $this->_fetchAll($sqlModule, array());
    }

    function markCustomCodeAsDeleted($id, $masterSource) {
        $toDelete = array(
            "ID"=>$id,
            "deletedBy"=>$this->getUsername(),
            "updatedBy"=>$this->getUsername(),
        );

        $sql = str_replace("%%MASTER_SOURCE_TABLE%%", $masterSource, SQL_OPAL_MARK_AS_DELETED_MASTER_SOURCE);
        return $this->_updateRecordIntoTable($sql, $toDelete);
    }

    function getCustomCodeDetails($customCodeId, $moduleId) {
        $module = $this->getModuleSettings($moduleId);
        if($module["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");

        $sqlModule = $module["sqlCustomCodeDetails"];
        $sqlModule = str_replace("%%MASTER_SOURCE_ALIAS%%", OPAL_MASTER_SOURCE_ALIAS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_DIAGNOSTIC%%", OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_TEST_RESULT%%", OPAL_MASTER_SOURCE_TEST_RESULT_TABLE, $sqlModule);
        $sqlModule = str_replace("%%ALIAS_EXPRESSION%%", OPAL_ALIAS_EXPRESSION_TABLE, $sqlModule);
        $sqlModule = str_replace("%%TEST_RESULT_EXPRESSION%%", OPAL_TEST_RESULT_EXPRESSION_TABLE, $sqlModule);
        $sqlModule = str_replace("%%DIAGNOSIS_CODE%%", OPAL_DIAGNOSIS_CODE_TABLE, $sqlModule);

        $results = $this->_fetch($sqlModule,  array(
            array("parameter"=>":ID","variable"=>$customCodeId,"data_type"=>PDO::PARAM_INT),
        ));
        $results["masterSource"] = $module["masterSource"];
        $results["module"]["ID"] = $module["ID"];
        $results["module"]["name_EN"] = $module["name_EN"];
        $results["module"]["name_FR"] = $module["name_FR"];
        if($module["subModule"] != "") {
            $sub = json_decode($module["subModule"], true);
            foreach($sub as $row) {
                if($row["ID"] == $results["type"]) {
                    $results["module"]["subModule"] = $row;
                    break;
                }
            }
        }
        else
            $results["module"]["subModule"] = "";

        return $results;
    }

    /*
     * This function fetches the SQL list of a specific module associated to the publication. It replaces the flags
     * with the correct table names, and fetch all the publications for the module.
     * @params  $moduleId (int) ID of the specific module
     * @returns array of results
     * */
    function getPublicationsPerModule($moduleId) {
        $result = array();
        $module = $this->_fetch(SQL_OPAL_GET_MODULE_BY_ID, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
        $sqlFetchPerModule = $module["unique"] == 1 ? $module["sqlPublicationUnique"] : $module["sqlPublicationMultiple"];
        if($sqlFetchPerModule == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing code. Access denied.");

        $sqlFetchPerModule = str_replace("%%QUESTIONNAIRE_DB%%", QUESTIONNAIRE_DB_2019_NAME, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%DICTIONARY%%", DICTIONARY_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%QUESTIONNAIRE%%", QUESTIONNAIRE_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%FILTERS%%", OPAL_FILTERS_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%QUESTIONNAIRECONTROL%%", OPAL_QUESTIONNAIRE_CONTROL_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%POSTCONTROL%%", OPAL_POST_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%TXTEAMMESSAGE%%", OPAL_TX_TEAM_MESSAGE_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%ANNOUNCEMENT%%", OPAL_ANNOUNCEMENT_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%PATIENTSFORPATIENTS%%", OPAL_PATIENTS_FOR_PATIENTS_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_CONTROL_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%PHASEINTREATMENT%%", OPAL_PHASE_IN_TREATMENT_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace(":OAUserId", intval($this->getOAUserId()), $sqlFetchPerModule);

        $result["publications"] = $this->_fetchAll($sqlFetchPerModule, array());
        $result["triggers"] = $this->getPublicationSettingsPerModule($moduleId);
        $result["unique"] = $module["unique"];
        $result["subModule"] = $module["subModule"];

        return $result;
    }

    /*
     * This function fetches the data of the specified module of a publication, to get the specific SQL query for the
     * details. It then replaces the flags with the table names, fetch the details and return the result.
     * @params  $moduleId (int) ID of the specific module
     * @params  $publicationId (int) ID of the publication
     * @returns array with details of the publication
     * */
    function getPublicationDetails($moduleId, $publicationId) {
        $module = $this->_fetch(SQL_OPAL_GET_MODULE_BY_ID, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
        $sqlFetchDetails = $module["sqlDetails"];
        if($sqlFetchDetails == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing code. Access denied.");

        $sqlFetchDetails = str_replace("%%QUESTIONNAIRE_DB%%", QUESTIONNAIRE_DB_2019_NAME, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%MODULE%%", OPAL_MODULE_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%DICTIONARY%%", DICTIONARY_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%QUESTIONNAIRE%%", QUESTIONNAIRE_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%FILTERS%%", OPAL_FILTERS_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%QUESTIONNAIRECONTROL%%", OPAL_QUESTIONNAIRE_CONTROL_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%POSTCONTROL%%", OPAL_POST_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%TXTEAMMESSAGE%%", OPAL_TX_TEAM_MESSAGE_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%ANNOUNCEMENT%%", OPAL_ANNOUNCEMENT_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%PATIENTSFORPATIENTS%%", OPAL_PATIENTS_FOR_PATIENTS_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_CONTROL_TABLE, $sqlFetchDetails);
        $sqlFetchDetails = str_replace("%%PHASEINTREATMENT%%", OPAL_PHASE_IN_TREATMENT_TABLE, $sqlFetchDetails);

        return $this->_fetch($sqlFetchDetails,  array(array("parameter"=>":ID","variable"=>$publicationId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * Gets the triggers of a specific module
     * @params  $moduleId (int) ID of a module
     * @returns array with details of the settings for the module
     * */
    function getPublicationSettingsIDsPerModule($moduleId) {
        return $this->_fetchAll(SQL_OPAL_GET_PUBLICATION_SETTINGS_ID_PER_MODULE, array(array("parameter"=>":moduleId","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * Returns the list of publication settings for a specific module
     * @params  $moduleId (int) ID of the module
     * @returns array of publication settings
     * */
    function getPublicationSettingsPerModule($moduleId) {
        return $this->_fetchAll(SQL_OPAL_GET_PUBLICATION_SETTINGS_PER_MODULE, array(array("parameter"=>":moduleId","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * Returns the list of module settings for a specific module
     * @params  $moduleId (int) ID of the module
     * @returns array of module settings
     * */
    function getModuleSettings($moduleId) {
        return $this->_fetch(SQL_OPAL_GET_MODULE_BY_ID, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * Get a publish date and time from a specific table. The name of the field should be PublishDate to make it work.
     * @params  $tableName (string) name of the table where to get the data
     *          $primaryKey (string) name of the primary key
     *          $id (int) ID of the record we need the date.
     * @return  (string) publish date
     * */
    function getPublishDateTime($tableName, $primaryKey, $id) {
        $sqlFetch = str_replace("%%TABLE_NAME%%", $tableName,SQL_OPAL_GET_PUBLISH_DATE_TIME);
        $sqlFetch = str_replace("%%PRIMARY_KEY%%", $primaryKey, $sqlFetch);
        $result = $this->_fetch($sqlFetch, array(array("parameter"=>":primaryKey","variable"=>$id,"data_type"=>PDO::PARAM_INT)));
        return $result["PublishDate"];
    }

    /*
     * returns the list of triggers details for a specific publication.
     * @params  $publicationId (int) ID of the publication
     *          $controlTableName (string) name of the table (module) we want the data
     * @return  array of triggers
     * */
    function getTriggersDetails($publicationId, $controlTableName) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS_DETAILS, array(
            array("parameter"=>":ControlTableSerNum","variable"=>$publicationId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ControlTable","variable"=>$controlTableName,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /*
     * This function get the appropriate chart log sql query. It fills the tables name, locate the sql query and runn
     * it before returning the results.
     * @params  $moduleId (int) ID of the module to get the logs
     *          $publicationId (int) ID of the publication of the module to get the chart logs
     * @return  (array) list of the chart logs found
     * */
    function getPublicationChartLogs($moduleId, $publicationId) {
        $sqlModule = array();
        $queryChartLog = $this->_fetch(SQL_GET_QUERY_CHART_LOG, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));

        if(!$queryChartLog)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Chart Logs error. Invalid module.");

        $sqlPublicationChartLog = str_replace("%%ANNOUNCEMENT_MH_TABLE%%", OPAL_ANNOUNCEMENT_MH_TABLE, $queryChartLog["sqlPublicationChartLog"]);
        $sqlPublicationChartLog = str_replace("%%CRON_LOG_TABLE%%", OPAL_CRON_LOG_TABLE, $sqlPublicationChartLog);
        $sqlPublicationChartLog = str_replace("%%TXT_TEAM_MSG_MH_TABLE%%", OPAL_TXT_TEAM_MSG_MH_TABLE, $sqlPublicationChartLog);
        $sqlPublicationChartLog = str_replace("%%PATIENTS_FOR_PATIENTS_MH_TABLE%%", OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE, $sqlPublicationChartLog);
        $sqlPublicationChartLog = str_replace("%%EDUCATION_MATERIAL_MH_TABLE%%", OPAL_EDUCATION_MATERIAL_MH_TABLE, $sqlPublicationChartLog);
        $sqlPublicationChartLog = str_replace("%%QUESTIONNAIRE_MH_TABLE%%", OPAL_QUESTIONNAIRE_MH_TABLE, $sqlPublicationChartLog);
        $sqlPublicationChartLog = json_decode($sqlPublicationChartLog, true);

        if($moduleId == MODULE_POST) {
            $postDetails = $this->getPostDetails($publicationId);
            if (!array_key_exists($postDetails["type"], $sqlPublicationChartLog))
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Chart Logs error. No sub-module found.");
            $sqlPublicationChartLog = $sqlPublicationChartLog[$postDetails["type"]];
        }
        else
            $sqlPublicationChartLog = $sqlPublicationChartLog[0];

        return $this->_fetchAll($sqlPublicationChartLog, array(array("parameter"=>":cron_serial","variable"=>$publicationId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * This function get the appropriate list log sql query. It fills the tables name, locate the sql query and runn
     * it before returning the results.
     * @params  $moduleId (int) ID of the module to get the logs
     *          $publicationId (int) ID of the publication of the module to get the chart logs
     *          $cronIds (array of int) list of IDs from the cron log we want details
     * @return  (array) list of the chart logs found
     * */
    function getPublicationListLogs($moduleId, $publicationId, $cronIds) {
        $sqlModule = array();
        $queryListLog = $this->_fetch(SQL_GET_QUERY_CHART_LOG, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));

        if(!$queryListLog)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Chart Logs error. Invalid module.");

        $sqlPublicationListLog = str_replace("%%ANNOUNCEMENT_MH_TABLE%%", OPAL_ANNOUNCEMENT_MH_TABLE, $queryListLog["sqlPublicationListLog"]);
        $sqlPublicationListLog = str_replace("%%POST_CONTROL_TABLE%%", OPAL_POST_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%TXT_TEAM_MSG_MH_TABLE%%", OPAL_TXT_TEAM_MSG_MH_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%PATIENTS_FOR_PATIENTS_MH_TABLE%%", OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%EDUCATION_MATERIAL_MH_TABLE%%", OPAL_EDUCATION_MATERIAL_MH_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%EDUCATION_MATERIAL_CONTROL_TABLE%%", OPAL_EDUCATION_MATERIAL_CONTROL_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%QUESTIONNAIRE_MH_TABLE%%", OPAL_QUESTIONNAIRE_MH_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%QUESTIONNAIRE_CONTROL_TABLE%%", OPAL_QUESTIONNAIRE_CONTROL_TABLE, $sqlPublicationListLog);
        $sqlPublicationListLog = str_replace("%%CRON_LOG_IDS%%", implode(", ", $cronIds), $sqlPublicationListLog);
        $sqlPublicationListLog = json_decode($sqlPublicationListLog, true);

        if($moduleId == MODULE_POST) {
            $postDetails = $this->getPostDetails($publicationId);
            if (!array_key_exists($postDetails["type"], $sqlPublicationListLog))
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Chart Logs error. No sub-module found.");
            $sqlPublicationListLog = $sqlPublicationListLog[$postDetails["type"]];
        }
        else
            $sqlPublicationListLog = $sqlPublicationListLog[0];

        return $this->_fetchAll($sqlPublicationListLog, array(array("parameter"=>":cron_serial","variable"=>$publicationId,"data_type"=>PDO::PARAM_INT)));
    }

    /*
     * get the list of publication modules
     * @params  void
     * @return  array of modules
     * */
    function getPublicationModules() {
        return $this->_fetchAll(SQL_OPAL_GET_ALL_PUBLICATION_MODULES, array());
    }

    /*
     * Get the list of logs for a specific array of ids of questionnaires
     * @params  $ids (array of int) cron ids
     * @return  array of data
     * */
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
     * Get all the triggers of a specific publication.
     * @params  Questionnaire serial number (int)
     * @return  array of details of the published questionnaire itself
     * */
    function getFrequencyEvents($publicationId, $controlTableName) {
        return $this->_fetchAll(SQL_OPAL_GET_FREQUENCY_EVENTS,
            array(
                array("parameter"=>":ControlTableSerNum","variable"=>$publicationId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":ControlTable","variable"=>$controlTableName,"data_type"=>PDO::PARAM_STR),
            ));
    }

    /*
     * Insert a new published questionnaire in questionnaire control table
     * @params  array of the published questionnaire
     * @return  ID of the entry
     * */
    function insertPublishedQuestionnaire($toInsert) {
        return $this->_replaceRecordIntoTable(OPAL_QUESTIONNAIRE_CONTROL_TABLE, $toInsert);
    }

    /*
     * Insert filters in the filter table
     * @params  array of the published questionnaire
     * @return  ID of the entry
     * */
    function insertMultipleFilters($toInsert) {
        $this->_replaceMultipleRecordsIntoTable(OPAL_FILTERS_TABLE, $toInsert);
    }

    /*
     * insert multiple frequency events
     * @params  array of records to insert
     * @return  number of records affected
     * */
    function insertMultipleFrequencyEvents($toInsert) {
        $this->_replaceMultipleRecordsIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $toInsert);
    }

    /*
     * Delete a specific frequency event.
     * @params  SerNum from the QuestionnaireControl to delete in frequency event table
     * @return  number of records affected
     * */
    function deleteFrequencyEvent($controlTableSerNum, $controlTable) {
        $toDelete = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ControlTable","variable"=>$controlTable,"data_type"=>PDO::PARAM_INT),
        );
        $this->_execute(SQL_OPAL_DELETE_FREQUENCY_EVENTS_TABLE, $toDelete);
    }

    /*
     * Returns the filters with a specific control table ser num
     * @params  questionnaire control ID
     * @return  array of filters
     * */
    function getFiltersByControlTableSerNum($controlTableSerNum, $controlTable) {
        return $this->_fetchAll(SQL_OPAL_GET_FILTERS_BY_CONTROL_TABLE_SERNUM,
            array(
                array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":ControlTable","variable"=>$controlTable,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Delete a specific frequency event trigger when doing an update
     * @params  filterId, filterType and SerNum from the QuestionnaireControl
     * @return  Total of records modified.
     * */
    function deleteFilters($filterId, $filterType, $controlTableSerNum, $controlTable) {
        $toDelete = array(
            array("parameter"=>":FilterId","variable"=>$filterId),
            array("parameter"=>":FilterType","variable"=>$filterType),
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum),
            array("parameter"=>":ControlTable","variable"=>$controlTable),
        );
        return $this->_execute(SQL_OPAL_DELETE_FILTERS, $toDelete);
    }

    /*
     * update the publication flag of a questionnaire.
     * @params  id of questionnaire, and value of the status (both integers)
     * @return  number of record affected
     * */
    function updatePublicationFlags($id, $value) {
        $toInsert = array(
            array("parameter"=>":QuestionnaireControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":PublishFlag","variable"=>$value,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS, $toInsert);
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

    /*
     * Returns the list of modules.
     * @params  void
     * @returns array of modules found and active
     * */
    function getPublicationModulesUser(){
        return $this->_fetchAll(SQL_OPAL_GET_ALL_PUBLICATION_MODULES_USER, array());
    }

    /*
     * Returns the list of modules.
     * @params  void
     * @returns array of modules found and active
     * */
    function getAvailableModules(){
        return $this->_fetchAll(SQL_OPAL_GET_ALL_CUSTOM_CODE_MODULES_USER, array());
    }

    /*
     * Insert a custom code to the appropriate table with the username and the creation date.
     * @params  $toInsert (array) contains all the details of the custom code.
     * @returns int number of records created
     * */
    function insertCustomCode($toInsert, $moduleId) {
        $toInsert["createdBy"] = $this->username;
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["updatedBy"] = $this->username;

        switch ($moduleId) {
            case MODULE_ALIAS:
                $tableToInsert = OPAL_MASTER_SOURCE_ALIAS_TABLE;
                break;
            case MODULE_TEST_RESULTS:
                $tableToInsert = OPAL_MASTER_SOURCE_TEST_RESULT_TABLE;
                break;
            case MODULE_DIAGNOSIS_TRANSLATION:
                $tableToInsert = OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE;
                break;
            default:
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");
        }
        $lastId = $this->_replaceRecordIntoTable($tableToInsert, $toInsert);
        $externalId = -1 * abs(intval($lastId));

        $sql = str_replace("%%MASTER_TABLE%%", $tableToInsert, OPAL_UPDATE_EXTERNAL_ID_MASTER_SOURCE);
        return $this->_updateRecordIntoTable($sql, array("ID"=>$lastId, "externalId"=>$externalId));
    }

    /*
     * Insert a custom code to the appropriate table with the username and the creation date.
     * @params  $toInsert (array) contains all the details of the custom code.
     * @returns int number of records created
     * */
    function updateCustomCode($toUpdate, $moduleId) {
        $toUpdate["updatedBy"] = $this->username;

        switch ($moduleId) {
            case MODULE_ALIAS:
                $tableToUpdate = OPAL_MASTER_SOURCE_ALIAS_TABLE;
                break;
            case MODULE_TEST_RESULTS:
                $tableToUpdate = OPAL_MASTER_SOURCE_TEST_RESULT_TABLE;
                break;
            case MODULE_DIAGNOSIS_TRANSLATION:
                $tableToUpdate = OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE;
                break;
            default:
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");
        }

        $sql = str_replace("%%MASTER_TABLE%%", $tableToUpdate, OPAL_UPDATE_MASTER_SOURCE);

        return $this->_updateRecordIntoTable($sql, $toUpdate);
    }

    /*
     * Get all the chart logs for a list of announcements
     * @params  $ids (array) list of IDs of the announcements
     * @return  array of records found
     * */
    function getAnnouncementChartLogsByIds($ids) {
        $sqlFetch = str_replace("%%CRON_LOG_IDS%%", implode(", ", $ids), SQL_OPAL_GET_ANNOUNCEMENT_CHART_PER_IDS);
        return $this->_fetchAll($sqlFetch, array());
    }

    /*
     * Get all the chart logs for a list of treatment team messages
     * @params  $ids (array) list of IDs of the treatment team messages
     * @return  array of records found
     * */
    function getTTMChartLogsByIds($ids) {
        $sqlFetch = str_replace("%%CRON_LOG_IDS%%", implode(", ", $ids), SQL_OPAL_GET_TTM_CHART_PER_IDS);
        return $this->_fetchAll($sqlFetch, array());
    }

    /*
     * Get all the chart logs for a specific educational material
     * @params  $postControlSerNum (int) ID of the educational material
     * @return  array of records found
     * */
    function getEducationalChartLogs($educationalMaterialControlSerNum) {
        return $this->_fetchAll(SQL_OPAL_GET_EDUCATIONAL_CHART,
            array(
                array("parameter"=>":EducationalMaterialControlSerNum","variable"=>$educationalMaterialControlSerNum,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get all the chart logs for a list of patients for patients
     * @params  $ids (array) list of IDs of the patients for patients
     * @return  array of records found
     * */
    function getPFPChartLogsByIds($ids) {
        $sqlFetch = str_replace("%%CRON_LOG_IDS%%", implode(", ", $ids), SQL_OPAL_GET_PFP_CHART_PER_IDS);
        return $this->_fetchAll($sqlFetch, array());
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
    * Update questionnaireControl table with changes made by user
    * @params  SerNum of the QuestionnaireControl table updated
    * @return  Total of records modified.
    * */
    function updatePostControl($record) {
        return $this->_updateRecordIntoTable(SQL_OPAL_UPDATE_POST_CONTROL, $record);
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
        return $this->_replaceRecordIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $record);
    }

    /*
     * Delete the end date of repeat frequency eventswhen doing an update
     * @params  SerNum of the QuestionnaireControl table updated
     * @return  Total of records modified.
     * */
    function deleteRepeatEndFromFrequencyEvents($controlTableSerNum, $controlTable) {
        $toInsert = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ControlTable","variable"=>$controlTable,"data_type"=>PDO::PARAM_INT),
        );
        return $this->_execute(SQL_OPAL_DELETE_REPEAT_END_FROM_FREQUENCY_EVENTS, $toInsert);
    }

    /*
     * Delete all other meta tags not required in Frequency Events, when doing an update
     * @params  SerNum of the QuestionnaireControl table updated
     * @return  Total of records modified.
     * */
    function deleteOtherMetasFromFrequencyEvents($controlTableSerNum, $controlTable) {
        $toInsert = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ControlTable","variable"=>$controlTable,"data_type"=>PDO::PARAM_INT),
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
        return $this->_replaceRecordIntoTable(OPAL_POST_TABLE, $toInsert);
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
     * Gets the publication settings (non trigger) of a specific module
     * @params  $moduleId (int) ID of a module
     * @returns array with details of the settings for the module
     * */
    function getPublicationNonTriggerSettingsPerModule($moduleId) {
        return $this->_fetchAll(SQL_OPAL_GET_PUBLICATION_NON_TRIGGERS_SETTINGS_PER_MODULE,
            array(array("parameter"=>":moduleId","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT))
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
     * Updates a post publishing date into the database after they were validated/sanitized
     * @params  array with post details (sanitized/validated)
     * @returns number of lines modified.
     * */
    function updatePostPublishDateTime($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();
        return $this->_updateRecordIntoTable(SQL_OPAL_UPDATE_POST_PUBLISH_DATE, $toUpdate);
    }

    function getSettings($settingId) {
        return $this->_fetch(SQL_OPAL_GET_SETTINGS,
            array(array("parameter"=>":ID","variable"=>$settingId,"data_type"=>PDO::PARAM_INT))
        );
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
    function markPostAsDeleted($tableName, $primaryKey, $recordId) {
        $sql = str_replace("%%PRIMARY_KEY%%", strip_tags($primaryKey),str_replace("%%TABLENAME%%", strip_tags($tableName),SQL_OPAL_MARK_RECORD_AS_DELETED));
        return $this->_execute($sql, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SessionId","variable"=>$this->getSessionId(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert an alias into the alias master source table
     * @params  $toInsert (array) data to insert
     * @returns total results inserted (int)
     * */
    function insertAliases($toInsert) {
        return $this->_replaceMultipleRecordsIntoTable(OPAL_MASTER_SOURCE_ALIAS_TABLE, $toInsert);
    }

    /*
     * Get all the patients triggers
     * @params  void
     * @return  patient triggers found (array)
     * */
    function getPatientsTriggers() {
        $results = $this->_fetchAll(OPAL_GET_PATIENTS_TRIGGERS, array());
        foreach($results as &$item) {
            $temp = $this->_fetchAll(OPAL_GET_MRN_PATIENT_SERNUM, array(array("parameter"=>":PatientSerNum","variable"=>$item["id"],"data_type"=>PDO::PARAM_INT)));
            $mrnList = array();
            foreach ($temp as $mrn)
                array_push($mrnList, $mrn["MRN"] . " (".$mrn["hospital"].")");
            if(count($mrnList) > 0)
                $item["name"] .= " (MRN: " . implode(", ", $mrnList) . ")";
        }
        return $results;
    }

    /*
     * Get all the diagnosis triggers
     * @params  void
     * @return  diagnosis triggers found (array)
     * */
    function getDiagnosisTriggers() {
        return $this->_fetchAll(OPAL_GET_DIAGNOSIS_TRIGGERS, array());
    }

    /*
     * Get all the appointments triggers
     * @params  void
     * @return  appointments triggers found (array)
     * */
    function getAppointmentsTriggers() {
        return $this->_fetchAll(OPAL_GET_APPOINTMENTS_TRIGGERS, array());
    }

    /*
     * Get all the appointment status triggers
     * @params  void
     * @return  appointment status triggers found (array)
     * */
    function getAppointmentsStatusTriggers() {
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_STATUS_TRIGGERS, array());
    }

    /*
     * Get all the doctors triggers
     * @params  void
     * @return  doctors triggers found (array)
     * */
    function getDoctorsTriggers() {
        return $this->_fetchAll(OPAL_GET_DOCTORS_TRIGGERS, array());
    }

    /*
     * Get all the treatment machine triggers
     * @params  void
     * @return  treatment machine triggers found (array)
     * */
    function getTreatmentMachinesTriggers() {
        return $this->_fetchAll(OPAL_GET_TREATMENT_MACHINES_TRIGGERS, array());
    }

    /*
     * Get all the treatment machine triggers
     * @params  void
     * @return  treatment machine triggers found (array)
     * */
    function getStudiesTriggers() {
        return $this->_fetchAll(OPAL_GET_STUDIES_TRIGGERS, array());
    }

    /*
     * Count the total iteration of a custom code.
     * @params  $tableName (string) name of the table where to count
     *          $code (string) name of the custom code to count
     *          $description (string) description of the custom code to count
     * @return  total found (array)
     * */
    function getCountCustomCodes($tableName, $code, $description) {
        $sql = str_replace("%%MASTER_SOURCE_TABLE%%", $tableName, OPAL_COUNT_CODE_MASTER_SOURCE);
        $toQuery = array(
            array("parameter"=>":description","variable"=>$description,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
        );
        return $this->_fetch($sql, $toQuery);
    }

    /*
     * Authenticate a username and a password of an user in opalDB (legacy system)
     * @params  $username (string)
     *          $password (string) already encrypted
     * @return  array with the results found
     * */
    function authenticateUserLegacy($username, $password) {
        return $this->_fetchAll(SQL_OPAL_VALIDATE_OAUSER_LOGIN, array(
            array("parameter"=>":username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":password","variable"=>$password,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Authenticate a username and a password of an user in opalDB (legacy system)
     * @params  $username (string)
     *          $password (string) already encrypted
     * @return  array with the results found
     * */
    function authenticateUserAccess($username) {
        return $this->_fetchAll(SQL_OPAL_VALIDATE_OAUSER_ACCESS, array(
            array("parameter"=>":Username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Authenticate a username and a password of an user in opalDB (legacy system)
     * @params  $username (string)
     *          $password (string) already encrypted
     * @return  array with the results found
     * */
    function authenticateSystemUser($username, $password) {
        return $this->_fetchAll(SQL_OPAL_VALIDATE_SYSTEM_OAUSER_LOGIN, array(
            array("parameter"=>":username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":password","variable"=>$password,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Authenticate a username of an user in opalDB. The complete authentication should use an AD system
     * @params  $username (string)
     * @return  array with the results found
     * */
    function authenticateUserAD($username) {
        return $this->_fetchAll(SQL_OPAL_VALIDATE_OAUSER_LOGIN_AD, array(
            array("parameter"=>":username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert an activity log into the OAUserActivityLog with the date.
     * @params  $toInsert (array) data to insert
     * @return  array of results of insertion
     * */
    function insertUserActivity($toInsert) {
        $toInsert["DateAdded"] = date("Y-m-d H:i:s");
        return $this->_replaceRecordIntoTable(OPAL_USER_ACTIVITY_LOG_TABLE, $toInsert);
    }

    /*
     * Update a password of an user in opalDB
     * @params  $userId (int)
     *          $encryptedPassword (string)
     * @return  array with the result of the update
     * */
    function updateUserPassword($userId, $encryptedPassword) {
        return $this->_execute(OPAL_UPDATE_PASSWORD, array(
            array("parameter"=>":OAUserSerNum","variable"=>$userId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Password","variable"=>$encryptedPassword,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Update user info in opalDB
     * @params  $userId (int)
     *          $language (string)
     * @return  array with the result of the update
     * */
    function updateUserInfo($userId, $language, $roleId) {
        return $this->_execute(OPAL_UPDATE_USER_INFO, array(
            array("parameter"=>":OAUserSerNum","variable"=>$userId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Update user info in opalDB
     * @params  $userId (int) ID of the user
     *          $language (string)
     * @return  array with the result of the update
     * */
    function updateUserLanguage($userId, $language) {
        return $this->_execute(OPAL_UPDATE_LANGUAGE, array(
            array("parameter"=>":OAUserSerNum","variable"=>$userId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get user details
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch
     * */
    function getUserDetails($userId) {
        return $this->_fetch(OPAL_GET_USER_DETAILS, array(
            array("parameter"=>":OAUserSerNum","variable"=>$userId,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get role details
     * @params  $roleId (int) ID of the role
     * @return  array with the result of the fetch
     * */
    function geRoleDetails($roleId) {
        return $this->_fetch(OPAL_GET_ROLE_DETAILS, array(
            array("parameter"=>":RoleSerNum","variable"=>$roleId,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the list of all non deleted users
     * @params  void
     * @return  array with the result of the fetch all
     * */
    function getUsersList() {
        return $this->_fetchAll(OPAL_GET_USERS_LIST, array());
    }

    /*
     * Count the number of time an username is in use
     * @params  $username (string) username to count
     * @return  array with the result of the count
     * */
    function countUsername($username) {
        return $this->_fetch(OPAL_COUNT_USERNAME, array(
            array("parameter"=>":Username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Check if the user exists already or not
     * @param $username
     * @return array - records found
     */
    function isUserExists($username) {
        return $this->_fetchAll(OPAL_IS_USER_EXISTS, array(
            array("parameter"=>":Username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Update a specific user informations and reactivate the account.
     * @param $type int - type of account (user or system)
     * @param $username string - username of the account to update
     * @param $password string - encrypted password
     * @param $language string - preferred language of the account (en/fr)
     * @param $roleId int - ID of the role of the user
     * @return int - number of records affected
     */
    function updateUser($type, $username, $password, $language, $roleId) {
        return $this->_execute(OPAL_UPDATE_USER, array(
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":type","variable"=>$type,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":Language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Username","variable"=>$username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Password","variable"=>$password,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * insert a new user and the date of adding
     * @param $type string - type of user (human or system)
     * @param $username string - username to insert
     * @param $password string - encrypted password
     * @param $language string - preferred language (en/fr)
     * @param $roleId int - ID of the role for the user
     * @return int - new created ID of the user
     */
    function insertUser($type, $username, $password, $language, $roleId) {
        $toInsert = array(
            "Username"=>$username,
            "Password"=>$password,
            "oaRoleId"=>$roleId,
            "type"=>$type,
            "Language"=>$language,
            "DateAdded"=>date("Y-m-d H:i:s"),
        );
        return $this->_replaceRecordIntoTable(OPAL_OAUSER_TABLE, $toInsert);
    }

    /*
     * insert a new user and the date of adding
     * @params  $username (string) username (duh!)
     *          $password (string) encrypted password
     *          $language (string) preferred language
     * @return  array with the result of the insert
     * */
    function insertUserAD($username, $language) {
        $toInsert = array(
            "Username"=>$username,
            "Language"=>$language,
            "DateAdded"=>date("Y-m-d H:i:s"),
        );
        return $this->_replaceRecordIntoTable(OPAL_OAUSER_TABLE, $toInsert);
    }

    /*
     * mark of user as being deleted
     * @params  $recordId (int) ID of the record to delete
     * @return  array with the result of the execution
     * */
    function markUserAsDeleted($recordId) {
        return $this->_execute(OPAL_MARK_USER_AS_DELETED, array(
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the roles list of users
     * @params  void
     * @return  array with the result of the fetch all
     * */
    function getRolesList() {
        return $this->_fetchAll(OPAL_GET_ROLES_LIST, array());
    }

    /*
     * Get all the user login details
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserLoginDetails($userId) {
        return $this->_fetchAll(OPAL_GET_USER_LOGIN_DETAILS, array(
            array("parameter"=>":OAUserSerNum","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user alias manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserAliasDetails($userId) {
        return $this->_fetchAll(OPAL_GET_USER_ALIAS_DETAILS, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user alias expressions manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserAliasExpressions($userId) {
        return $this->_fetchAll(OPAL_GET_USER_ALIAS_EXPRESSIONS, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user diagnosis translations manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserDiagnosisTranslations($userId) {
        return $this->_fetchAll(OPAL_GET_USER_DIAGNOSIS_TRANSLATIONS, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user diagnosis code manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserDiagnosisCode($userId) {
        return $this->_fetchAll(OPAL_GET_USER_DIAGNOSIS_CODE, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user email manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserEmail($userId) {
        return $this->_fetchAll(OPAL_GET_USER_EMAIL, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user filter manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserFilter($userId) {
        return $this->_fetchAll(OPAL_GET_USER_TRIGGER, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user hospital maps manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserHospitalMap($userId) {
        return $this->_fetchAll(OPAL_GET_USER_HOSPITAL_MAP, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user posts manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserPost($userId) {
        return $this->_fetchAll(OPAL_GET_USER_POST, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user notifications manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserNotification($userId) {
        return $this->_fetchAll(OPAL_GET_USER_NOTIFICATION, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user questionnaires manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserQuestionnaire($userId) {
        return $this->_fetchAll(OPAL_GET_USER_QUESTIONNAIRE, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user test results manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserTestResult($userId) {
        return $this->_fetchAll(OPAL_GET_USER_TEST_RESULT, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the user test results expressions manipulations
     * @params  $userId (int) ID of the user
     * @return  array with the result of the fetch all
     * */
    function getUserTestResultExpression($userId) {
        return $this->_fetchAll(OPAL_GET_USER_TEST_RESULT_EXP, array(
            array("parameter"=>":LastUpdatedBy","variable"=>$userId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the studies list
     * @params  void
     * @return  studies found (array)
     * */
    function getStudiesList() {
        return $this->_fetchAll(OPAL_GET_STUDIES_LIST, array());
    }

    /*
     * Insert new study with the username of the user who created it.
     * @param   $newStudy (array) new study to add
     * @return  number of record inserted (should be one) or a code 500
     * */
    function insertStudy($newStudy) {
        $newStudy["createdBy"] = $this->getUsername();
        $newStudy["creationDate"] = date("Y-m-d H:i:s");
        $newStudy["updatedBy"] = $this->getUsername();
        return $this->_replaceRecordIntoTable(OPAL_STUDY_TABLE, $newStudy);
    }

    /*
     * Get the details of a study by its ID
     * @params  $studyId (int) ID of the study
     * @returns (array) details of the study
     * */
    function getStudyDetails($studyId) {
        return $this->_fetchAll(OPAL_GET_STUDY_DETAILS,
            array(array("parameter"=>":ID","variable"=>$studyId,"data_type"=>PDO::PARAM_INT))
        );
    }

    /*
     * Updates a specific study with user request info and username.
     * @params  $study (array) study to update
     * @return  (int) total record updated (should be one only!)
     * */
    function updateStudy($study) {
        $study["updatedBy"] = $this->getUsername();
        return $this->_updateRecordIntoTable(OPAL_UPDATE_STUDY, $study);
    }

    /**
     * Update a specific patient consent status (invited, opalConsented, otherConsented, declined)
     * @params $studyID (int) study id
     *         $patId (int) patient Id
     *         $patConsent (int) new patient consent
     */
    function updateStudyConsent($studyID, $patId, $patConsent){
        return $this->_fetchAll(OPAL_UPDATE_STUDY_CONSENT, array(
            array("parameter"=>":studyId","variable"=>$studyID,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":patientId","variable"=>$patId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":patientConsent","variable"=>$patConsent,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Marks a specified study as deleted.
     * @param   int : $studyId (ID of the study to mark as deleted)
     * @return  int : number of record deleted or error 500.
     * */
    function markStudyAsDeleted($studyId) {
        return $this->_updateRecordIntoTable(OPAL_MARK_STUDY_AS_DELETED, array(
            "ID"=>$studyId,
            "updatedBy"=>$this->getUsername(),
        ));
    }

    /*
     * Returns the list of roles
     * @param   void
     * @return  array : list of module
     * */
    function getRoles() {
        return $this->_fetchAll(OPAL_GET_ROLES, array());
    }

    /*
     * Returns the list of active modules with their authorized operations
     * @params  void
     * @return  array: list of modules with name (en and fr) and their operations associated
     * */
    function getAvailableRolesModules() {
        return $this->_fetchAll(OPAL_GET_AVAILABLE_ROLES_MODULES, array());
    }

    /*
     * Returns the operations for a series of modules requested.
     * @params  $modulesId : array - IDs of the modules
     * @return  array - list of operations of modules
     * */
    function getModulesOperations($modulesId) {
        $sql = str_replace("%%MODULESID%%", implode(", ", $modulesId),OPAL_GET_MODULES_OPERATIONS);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Returns the access level for the role module for a specific role. Used to prevent a user
     * to modify the access to the role module of his own role.
     * @params  $oaRoleId : int - ID of the role
     * @return  array - Access of the role specified for the role module.
     * */
    function getUserRoleModuleAccess($oaRoleId) {
        return $this->_fetchAll(OPAL_GET_USER_ROLE_MODULE_ACCESS, array(
            array("parameter"=>":oaRoleId","variable"=>$oaRoleId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Insert a new role with the username of the creator and the creation date. Returns the ID of the new role.
     * @params  $toInsert : array - contains french and english name of the role
     * @return  int - ID of the new role
     * */
    function insertRole($toInsert) {
        $toInsert["createdBy"] = $this->getUsername();
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["updatedBy"] = $this->getUsername();
        return $this->_replaceRecordIntoTable(OPAL_OA_ROLE_TABLE, $toInsert);
    }

    /*
     * Insert operations linked for a new role and a series of module.
     * @params  $toInsert : array - operation for each module for a specific roles
     * @returns int : ID of the entry
     * */
    function insertRoleModule($toInsert) {
        return $this->_replaceMultipleRecordsIntoTable(OPAL_OA_ROLE_MODULE_TABLE, $toInsert);
    }

    /*
     * Get the details of a specific role.
     * @params  $roleId : int - ID of the role
     * @return  array - details from the role table
     * */
    function getRoleDetails($roleId) {
        return $this->_fetchAll(OPAL_GET_OA_ROLE_DETAILS, array(
            array("parameter"=>":ID","variable"=>$roleId,"data_type"=>PDO::PARAM_INT)
        ));
    }

    /*
     * Get the operations available from a specific role
     * @params  $roleId : int - ID of the role
     * @return  array - list of operations from the role-module table
     * */
    function getRoleOperations($roleId) {
        return $this->_fetchAll(OPAL_GET_OA_ROLE_MODULE, array(
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_INT)
        ));
    }

    /*
     * Update the details of a specific role. Mostly the french and/or english name.
     * @params  $updatedEntries : array - contains the french and english name of the role to update
     * @return  int - number of records updated
     * */
    function updateRole($updatedEntries) {
        $updatedEntries["updatedBy"]=$this->getUsername();
        return $this->_updateRecordIntoTable(OPAL_UPDATE_ROLE, $updatedEntries);
    }

    /*
     * Delete any access operation for a specific role excluding a list of module ID
     * @params  $roleID : int - ID of the role to delete the access
     *          $idsToBeKept : array - ID of module to be excluded from the deletion
     * @return  int - number of records deleted
     * */
    function deleteOARoleModuleOptions($roleID, $idsToBeKept) {
        $toDelete = array(
            array("parameter"=>":oaRoleId","variable"=>$roleID,"data_type"=>PDO::PARAM_INT),
        );
        $sql = str_replace("%%MODULEIDS%%", implode(", ", $idsToBeKept), OPAL_DELETE_OA_ROLE_MODULE_OPTIONS);
        return $this->_execute($sql, $toDelete);
    }

    /*
     * Insert a number of new access privilege for a role into oaRoleModule.
     * @params  $multipleUpdayes : array - data on the new access
     * @return  int - number of records updated
     * */
    function insertOARoleModule($multipleUpdates) {
        return $this->_replaceMultipleRecordsIntoTable(OPAL_OA_ROLE_MODULE_TABLE, $multipleUpdates);
    }

    /*
     * Update an operation access in the table oaRoleModule associated to one role and one module.
     * @params  $toUpdated : array - contain the access level and the ID.
     * @return  int - number of records updated
     * */
    function updateOARoleModule($toUpdate) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_ROLE_MODULE, $toUpdate);
    }

    /*
     * For the oaRole table to update a specific line with the username. This is to mark down an user update operations
     * of a role.
     * @params  $id : int - ID of the role to update
     * @return  int - number of records updated
     * */
    function forceUpdateOaRoleTable($id) {
        $sqlQuery = str_replace("%%TABLENAME%%", OPAL_OA_ROLE_TABLE, OPAL_FORCE_UPDATE_UPDATEDBY);
        $updatedEntries = array(
            "ID"=>$id,
            "updatedBy"=>$this->getUsername()
        );
        return $this->_updateRecordIntoTable($sqlQuery, $updatedEntries);
    }

    /*
     * Marks a specified role as deleted.
     * @param   int : $roleId (ID of the role to mark as deleted)
     * @return  int : number of record deleted or error 500.
     * */
    function markRoleAsDeleted($roleId) {
        return $this->_updateRecordIntoTable(OPAL_MARK_ROLE_AS_DELETED, array(
            "ID"=>$roleId,
            "deletedBy"=>$this->getUsername(),
            "updatedBy"=>$this->getUsername(),
        ));
    }

    /**
     * Get access level for a specific combo or role/module. If ORMS is not active or present, deactivate SMS if it's
     * not already.
     * @param $roleId
     * @return array - access levels for a specific role
     */
    function getUserAccess($roleId) {
        $result = $this->_fetchAll(OPAL_GET_USER_ACCESS, array(
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_INT),
        ));
        if(!WRM_DB_ENABLED)
            foreach ($result as &$item)
                if($item["ID"] == MODULE_SMS && $item["access"] != USER_ACCESS_DENIED)
                    $item["access"] =  USER_ACCESS_DENIED;
        return $result;
    }

    /*
     * Get access level for a specific combo or role/module
     * */
    function getUserAccessRegistration($roleId) {
        return $this->_fetchAll(OPAL_GET_USER_ACCESS_REGISTRATION, array(
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the list of educational material
     * @params  void
     * @return  array - list of educational material
     * */
    function getEducationalMaterial() {
        return $this->_fetchAll(OPAL_GET_EDUCATIONAL_MATERIAL, array());
    }

    function getPublishedEducationalMaterial() {
        return $this->_fetchAll(OPAL_GET_EDUCATIONAL_MATERIAL, array());
    }

    /*
     * Get the list of table of contents for educational materials
     * @params  void
     * @return  array - table of contents
     * */
    function getTocsContent($eduId) {
        return $this->_fetchAll(OPAL_GET_TOCS_EDU_MATERIAL, array(
            array("parameter"=>":ParentSerNum","variable"=>$eduId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the list of educational material details
     * @params  void
     * @return  array - list of educational material details
     * */
    function getEduMaterialDetails($eduId) {
        return $this->_fetch(OPAL_GET_EDU_MATERIAL_DETAILS, array(
            array("parameter"=>":EducationalMaterialControlSerNum","variable"=>$eduId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the list of educational material logs
     * @params  void
     * @return  array - list of educational material logs
     * */
    function getEduMaterialLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_EDU_MATERIAL_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of tasks logs
     * @params  void
     * @return  array - list of tasks logs
     * */
    function getTasksLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_TASK_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of documents logs
     * @params  void
     * @return  array - list of documents logs
     * */
    function getDocumentsLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_DOCUMENT_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of appointments logs
     * @params  void
     * @return  array - list of appointments logs
     * */
    function getAppointmentsLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_APPOINTMENT_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of aliases logs
     * @params  void
     * @return  array - list of aliases logs
     * */
    function getAliasesLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_ALIAS_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of emails logs
     * @params  void
     * @return  array - list of emails logs
     * */
    function getEmailsLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_EMAILS_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of notifications logs
     * @params  void
     * @return  array - list of notifications logs
     * */
    function getNotificationsLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_NOTIFICATIONS_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of test results logs
     * @params  void
     * @return  array - list of test results logs
     * */
    function getTestResultsLogs($listIds) {
        $sql = str_replace("%%LIST_IDS%%", implode(", ", $listIds), OPAL_GET_TEST_RESULTS_MH);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of hospital maps details
     * @params  void
     * @return  array - list of hospital maps details
     * */
    function getHospitalMapDetails($hpId) {
        return $this->_fetch(OPAL_GET_HOSPITAL_MAP_DETAILS, array(
            array("parameter"=>":HospitalMapSerNum","variable"=>$hpId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the cron log appointments
     * @params  void
     * @return  array - list of cron log appointments
     * */
    function getCronLogAppointments() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_APPOINTMENTS, array());
    }

    /*
     * Get the cron log documents
     * @params  void
     * @return  array - list of cron log documents
     * */
    function getCronLogDocuments() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_DOCUMENTS, array());
    }

    /*
     * Get the cron log tasks
     * @params  void
     * @return  array - list of cron log tasks
     * */
    function getCronLogTasks() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_TASKS, array());
    }

    /*
     * Get the cron log announcements
     * @params  void
     * @return  array - list of cron log announcements
     * */
    function getCronLogAnnouncements() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_ANNOUNCEMENTS, array());
    }

    /*
     * Get the cron log treatment team msgs
     * @params  void
     * @return  array - list of cron log treatment team msgs
     * */
    function getCronLogTTMs() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_TTMS, array());
    }

    /*
     * Get the cron log patients for patients
     * @params  void
     * @return  array - list of cron log patients for patients
     * */
    function getCronLogPFP() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_PFP, array());
    }

    /*
     * Get the cron log educational materials
     * @params  void
     * @return  array - list of cron log educational materials
     * */
    function getCronLogEduMaterials() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_EDU_MATERIALS, array());
    }

    /*
     * Get the cron log notifications
     * @params  void
     * @return  array - list of cron log notifications
     * */
    function getCronLogNotifications() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_NOTIFICATIONS, array());
    }

    /*
     * Get the cron log test results
     * @params  void
     * @return  array - list of cron log test results
     * */
    function getCronLogTestResults() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_TEST_RESULTS, array());
    }

    /*
     * Get the cron log emails
     * @params  void
     * @return  array - list of cron log emails
     * */
    function getCronLogEmails() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_EMAILS, array());
    }

    /*
     * Get the cron log questionnaires
     * @params  void
     * @return  array - list of cron log questionnaires
     * */
    function getCronLogQuestionnaires() {
        return $this->_fetchAll(OPAL_GET_CRON_LOG_QUESTIONNAIRES, array());
    }

    /*
     * Get the cron log hospital maps
     * @params  void
     * @return  array - list of cron log hospital maps
     * */
    function getHospitalMaps() {
        return $this->_fetchAll(OPAL_GET_HOSPITAL_MAPS, array());
    }

    /*
     * Get the categories of the navigation meny
     * @params  void
     * @return  array - list of categories
     * */
    function getCategoryNavMenu() {
        return $this->_fetchAll(OPAL_GET_CATEGORY_MENU, array());
    }

    /*
     * Get the navigation menu options of a particular category
     * @params  $categoryMenuId - int - ID of the category
     * @return  array - navigation menu
     * */
    function getNavMenu($categoryMenuId) {
        return $this->_fetchAll(OPAL_GET_NAV_MENU, array(
            array("parameter"=>":categoryModuleId","variable"=>$categoryMenuId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the alerts list
     * @params  void
     * @return  alerts found (array)
     * */
    function getAlertsList() {
        return $this->_fetchAll(OPAL_GET_ALERTS_LIST, array());
    }

    /*
     * Get all the alerts list
     * @params  void
     * @return  alerts found (array)
     * */
    function updateAlertActivationFlag($id, $active) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_ALERT_ACTIVATION_FLAG, array(
            "active"=>$active,
            "updatedBy"=>$this->username,
            "ID"=>$id
        ));
    }

    /*
     * Insert a new alert that was validated
     * @params  $toSubmit : array - new allert to insert.
     * @return int - ID of the record inserted
     * */
    function insertAlert($toSubmit) {
        $toSubmit["creationDate"] = date("Y-m-d H:i:s");
        $toSubmit["createdBy"] = $this->username;
        $toSubmit["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(OPAL_ALERT_TABLE, $toSubmit);
    }

    /*
     * Get the details of a specific alert.
     * @params  $alertId : int - ID of the alert to get the details
     * @return  array - details of the alert
     * */
    function getAlertDetails($alertId) {
        return $this->_fetchAll(OPAL_GET_ALERT_DETAILS, array(
            array("parameter"=>":ID","variable"=>$alertId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Update a specific alert
     * @params  $toSubmit : array - contains the details of the alert to update
     * @return  int : number of records updated
     * */
    function updateAlert($updatedEntries) {
        $updatedEntries["updatedBy"]=$this->getUsername();
        return $this->_updateRecordIntoTable(OPAL_UPDATE_ALERT, $updatedEntries);
    }

    /*
     * Marks a specified alert as deleted.
     * @params   int : $alertId (ID of the alert to mark as deleted)
     * @return  int : number of record deleted or error 500.
     * */
    function markAlertAsDeleted($alertId) {
        return $this->_updateRecordIntoTable(OPAL_MARK_ALERT_AS_DELETED, array(
            "ID"=>$alertId,
            "deletedBy"=>$this->getUsername(),
            "updatedBy"=>$this->getUsername(),
        ));
    }

    /*
     * Insert user's action in the audit table
     * @params  $toSubmit : array - Contains the user's info
     * @return  int - latest ID created
     * */
    function insertAudit($toInsert) {
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["createdBy"] = ($this->username != null ? $this->username : UNKNOWN_USER);
        if($this->type == HUMAN_USER)
            $sqlTable = OPAL_AUDIT_TABLE;
        else
            $sqlTable = OPAL_AUDIT_SYSTEM_TABLE;
        return $this->_replaceRecordIntoTable($sqlTable, $toInsert);
    }

    /*
     * Insert user's action in the audit table
     * @params  $toSubmit : array - Contains the user's info
     * @return  int - latest ID created
     * */
    function insertAuditForceUser($toInsert) {
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        if($this->type == HUMAN_USER)
            $sqlTable = OPAL_AUDIT_TABLE;
        else
            $sqlTable = OPAL_AUDIT_SYSTEM_TABLE;
        return $this->_replaceRecordIntoTable($sqlTable, $toInsert);
    }

    /*
     * Get the list of audit. Because the front end does not support pagination or lazy loading, limit to the latest
     * 10,000 records.
     * @params  void
     * @return  array - latest entries in the audit table
     * */
    function getAudits() {
        return $this->_fetchAll(OPAL_GET_AUDITS, array());
    }

    /*
     * Get the details of a specific audit
     * @params  $auditId : int - ID of the audit
     * @return  array - details of the audit
     * */
    function getAuditDetails($auditId) {
        return $this->_fetchAll(OPAL_GET_AUDIT_DETAILS, array(
            array("parameter"=>":ID","variable"=>$auditId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the details of a specific diagnosis
     * @params  $diagnosisId : int - ID of the diagnosis
     * @return  array - details of the diagnosis
     * */
    function getDiagnosisDetails($diagnosisId) {
        return $this->_fetch(OPAL_GET_DIAG_TRANS_DETAILS, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the diagnosis codes of a specific diagnosis
     * @params  $diagnosisId : int - ID of the diagnosis
     * @return  array - list of codes of the diagnosis
     * */
    function getDiagnosisCodes($diagnosisId) {
        return $this->_fetchAll(OPAL_GET_DIAGNOSIS_CODES, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the activate source database in the system
     * @params  void
     * @return  array - List of active database
     * */
    function getActiveSourceDatabase() {
        return $this->_fetchAll(OPAL_GET_ACTIVATE_SOURCE_DB, array());
    }

    /*
     * Get the list of assigned diagnoses
     * @params  void
     * @return  array - List of assigned diagnoses
     * */
    function getAssignedDiagnoses() {
        return $this->_fetchAll(OPAL_GET_ASSIGNED_DIAGNOSES, array());
    }

    /*
     * Get the list of diagnosis based on a list of specific DB sources
     * @params  $sourceIds : array - List of IDs of available DB sources
     * @return  array - list of diagnoses
     * */
    function getDiagnoses($sourceIds) {
        $sql = str_replace("%%SOURCE_DB_IDS%%",implode(", ", $sourceIds), OPAL_GET_DIAGNOSES);
        return $this->_fetchAll($sql, array());
    }

    /*
     * Get the list of diagnosis translations
     * @params  void
     * @return  array - list diagnosis translations
     * */
    function getDiagnosisTranslations() {
        return $this->_fetchAll(OPAL_GET_DIAGNOSIS_TRANSLATIONS, array());
    }

    /*
     * Insert a diagnosis into the diagnosis translation table
     * @params  $toInsert : array - list of settings of the diagnosis
     * @return  int - last ID entered
     * */
    function insertDiagnosisTranslation($toInsert) {
        $toInsert["DateAdded"] = date("Y-m-d H:i:s");
        $toInsert["LastUpdatedBy"] = $this->getOAUserId();
        $toInsert["SessionId"] = $this->getSessionId();

        return $this->_replaceRecordIntoTable(OPAL_DIAGNOSIS_TRANSLATION_TABLE, $toInsert);
    }

    /*
     * Insert a list of diagnosis codes into the diagnosis code table
     * @params  $toInsert : array - list of diagnosis codes
     * @return  int - last ID entered
     * */
    function insertMultipleDiagnosisCodes($toInsert) {
        foreach ($toInsert as &$item) {
            $item["DateAdded"] = date("Y-m-d H:i:s");
            $item["LastUpdatedBy"] = $this->getOAUserId();
            $item["SessionId"] = $this->getSessionId();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_DIAGNOSIS_CODE_TABLE, $toInsert);
    }

    /*
     * Validate an educational material by its ID
     * @params  $eduId : int - ID of the educational material
     * @return  array - contains the total results
     * */
    function validateEduMaterialId($eduId) {
        return $this->_fetch(OPAL_VALIDATE_EDU_MATERIAL_ID, array(
            array("parameter"=>":EducationalMaterialControlSerNum","variable"=>$eduId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Update a diagnosis code
     * @params  $toUpdate : array - details of the diagnosis to update.
     * @return  int - number of row affected
     * */
    function updateDiagnosisTranslation($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();

        return $this->_updateRecordIntoTable(OPAL_UPDATE_DIAGNOSIS_TRANSLATION, $toUpdate);
    }

    /*
     * Delete a series diagnosis code based on a specific diagnosis translation ID
     * @params  $diagnosisTranslationId : int - ID of the diagnosis translation
     *          $sourceIds : array - list of source IDS
     * @return  int - number of row affected
     * */
    function deleteDiagnosisCodes($diagnosisTranslationId, $sourceIds) {
        $sql = str_replace("%%LIST_SOURCES_UIDS%%",implode(", ", $sourceIds), OPAL_DELETE_DIAGNOSIS_CODES);

        return $this->_execute($sql, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisTranslationId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Delete a all diagnosis code based on a specific diagnosis translation ID
     * @params  $diagnosisTranslationId : int - ID of the diagnosis translation
     * @return  int - number of row affected
     * */
    function deleteAllDiagnosisCodes($diagnosisTranslationId) {
        return $this->_execute(OPAL_DELETE_ALL_DIAGNOSIS_CODES, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisTranslationId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Delete a specific diagnosis translation
     * @params  $diagnosisTranslationId : int - ID of the diagnosis translation to delete
     * @return  int - number of row affected
     * */
    function deleteDiagnosisTranslation($diagnosisTranslationId) {
        return $this->_execute(OPAL_DELETE_DIAGNOSIS_TRANSLATION, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisTranslationId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get all the triggers
     * @params  int : $sourceContentId - content id the triggers are attached to
     *          int : $sourceModuleId - module id of the source content
     * @return  triggers found (array)
     * */
    function getTriggersList($sourceContentId, $sourceModuleId) {
        return $this->_fetchAll(OPAL_GET_TRIGGERS_LIST, array(
            array("parameter"=>":sourceContentId","variable"=>$sourceContentId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":sourceModuleId","variable"=>$sourceModuleId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the list patient diagnoses. MRN and site name are mandatory. If there is no source, ignore it. If there is
     * a source, add it in the SQL as = if include value is 1 or absent, and != if value is anthing else than 1. Start
     * and end date use the proper value or current date if no value.
     * @params  $mrn : string - medical record number of the patient
     *          $site : string - name of the site
     *          $source : string - source database
     *          $include : int - determines if != or =
     *          $startDate : string - format Y-m-d. Start date of research
     *          $endDate : string - format Y-m-d. End date of research
     * @return  array - List of diagnoses found.
     * */
    function getPatientDiagnoses($mrn, $site, $source, $include, $startDate, $endDate) {
        $data = array(
            array("parameter"=>":MRN","variable"=>$mrn,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":site","variable"=>$site,"data_type"=>PDO::PARAM_STR),
        );
        $sql = OPAL_GET_PATIENT_DIAGNOSIS;
        if($source == "")
            $sql = str_replace("%%SOURCE%%", "", $sql);
        else {
            $sql = str_replace("%%OPERATOR%%", $include, str_replace("%%SOURCE%%", OPAL_SOURCE_DATABASE, $sql));
            array_push($data, array("parameter"=>":SourceDatabaseName","variable"=>$source,"data_type"=>PDO::PARAM_STR));
        }

        if($startDate == SQL_CURRENT_DATE)
            $sql = str_replace(":startDate", $startDate, $sql);
        else
            array_push($data, array("parameter"=>":startDate","variable"=>$startDate,"data_type"=>PDO::PARAM_STR));

        if($endDate == SQL_CURRENT_DATE)
            $sql = str_replace(":endDate", $endDate, $sql);
        else
            array_push($data, array("parameter"=>":endDate","variable"=>$endDate,"data_type"=>PDO::PARAM_STR));

        return $this->_fetchAll($sql, $data);
    }

    /*
     * Get a pair of patient/site exists in the database by counting the total. In theory, should be unique.
     * @params  $mrn : string - medical record number of a patient.
     *          $site : string - the hospital identifier code.
     * @return  array - details of the pait of mrn/site.
     * */
    function getPatientSite($mrn, $site) {
        return $this->_fetchAll(OPAL_GET_PATIENT_SITE, array(
            array("parameter"=>":MRN","variable"=>$mrn,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Hospital_Identifier_Type_Code","variable"=>$site,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the details of an active source database based on its source name. In theory, should be unique.
     * @params  $source : string - name of the source database.
     * @return  array - details of the source database.
     * */
    function getSourceDatabaseDetails($source) {
        return $this->_fetchAll(OPAL_GET_SOURCE_DB_DETAILS, array(
            array("parameter"=>":SourceDatabaseName","variable"=>$source,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the details of a diagnosis code based on its code and its source.
     * @params  $code : string - diagnosis code
     *          $source : int - source of the database
     * @return  array - details of the diagnosis codes
     * */
    function getDiagnosisCodeDetails($code, $source, $externalId) {
        return $this->_fetchAll(OPAL_GET_DIAGNOSIS_CODE_DETAILS, array(
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":SourceDatabaseName","variable"=>$source,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":externalId","variable"=>$externalId,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert a patient diagnosis in the diagnosis table
     * @params  $toInsert : array - Contains the patient diagnosis info
     * @return  int - last insert ID
     * */
    function replacePatientDiagnosis($toInsert) {
        $toInsert["createdBy"] = $this->getUsername();
        $toInsert["updatedBy"] = $this->getUsername();
        return $this->_replaceRecordIntoTable(OPAL_DIAGNOSIS_TABLE, $toInsert);
    }

    /*
     * Get the ID of a specific patient diagnosis.
     * @params  $patientId : string - patient sernum
     *          $source : string - source database
     *          $externalId : string - external ID from an outside source
     * @return  Diagnosis SerNum for a specific patient in a specific DB
     * */
    function getPatientDiagnosisId($patientId, $source, $diagnosisAriaSer) {
        return $this->_fetchAll(OPAL_GET_PATIENT_DIAGNOSIS_ID, array(
            array("parameter"=>":PatientSerNum","variable"=>$patientId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":DiagnosisAriaSer","variable"=>$diagnosisAriaSer,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Delete a specific patient diagnosis.
     * @params  $id : int - Diagnosis sernum
     * @return  int - number of record deleted
     * */
    function deletePatientDiagnosis($id) {
        $this->_execute(OPAL_DELETE_PATIENT_DIAGNOSIS, array(
            array("parameter"=>":DiagnosisSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the patient by their last name
     * @params  $name : string - target patient last name
     * @return  array - list of patient(s) matching search
     * */
    function getPatientName($plname) {
        return $this->_fetchAll(OPAL_GET_PATIENT_NAME, array(
            array("parameter"=>":name","variable"=>'%'.$plname.'%',"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the patient by their mrn
     * @params  $mrn : string - target patient mrn
     * @return  array - list of patient(s) matching search
     * */
    function getPatientMRN($pmrn) {
        return $this->_fetchAll(OPAL_GET_PATIENT_MRN, array(
            array("parameter"=>":MRN","variable"=>'%'.$pmrn.'%',"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the patient by their ramq
     * @params  $mrn : string - target patient ramq
     * @return  array - list of patient(s) matching search
     * */
    function getPatientRAMQ($ssn) {
        return $this->_fetchAll(OPAL_GET_PATIENT_RAMQ, array(
            array("parameter"=>":SSN","variable"=>'%'.$ssn.'%',"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient diagnosis report
     * @params $pnum : int - target patient ser num
     * @return array - patient diagnosis details
     */
    function getPatientDiagnosisReport($pnum){
        return $this->_fetchAll(OPAL_GET_DIAGNOSIS_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient appointment report
     * @params $pnum : int - target patient ser num
     * @return array - patient appointment details
     */
    function getPatientAppointmentReport($pnum){
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient questionnaire report
     * @params $pnum : int - target patient ser num
     * @return array - patient questionnaire details
     */
    function getPatientQuestionnaireReport($pnum){
        return $this->_fetchAll(OPAL_GET_QUESTIONNAIRE_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient educational material report
     * @params $pnum : int - target patient ser num
     * @return array - patient educ material details
     */
    function getPatientEducMaterialReport($pnum){
        return $this->_fetchAll(OPAL_GET_EDUCATIONAL_MATERIAL_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient legacy test results report
     * @params $pnum : int - target patient ser num
     * @return array - patient test result details
     */
    function getPatientLegacyTestReport($pnum){
        return $this->_fetchAll(OPAL_GET_LEGACY_TEST_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient test results report
     * @params $pnum : int - target patient ser num
     * @return array - patient test result details
     */
    function getPatientTestReport($pnum){
        return $this->_fetchAll(OPAL_GET_TEST_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient notifications report
     * @params $pnum : int - target patient ser num
     * @return array - patient notifiations details
     */
    function getPatientNotificationsReport($pnum){
        return $this->_fetchAll(OPAL_GET_NOTIFICATIONS_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient treatment plan report
     * @params $pnum : int - target patient ser num
     * @return array - patient tx plan details
     */
    function getPatientTreatmentPlanReport($pnum){
        return $this->_fetchAll(OPAL_GET_TREATMENT_PLAN_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient clinnotes  report
     * @params $pnum : int - target patient ser num
     * @return array - patient clinnote details
     */
    function getPatientClinNoteReport($pnum){
        return $this->_fetchAll(OPAL_GET_CLINICAL_NOTES_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient treating team report
     * @params $pnum : int - target patient ser num
     * @return array - patient tx team details
     */
    function getPatientTxTeamReport($pnum){
        return $this->_fetchAll(OPAL_GET_TREATING_TEAM_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient general report
     * @params $pnum : int - target patient ser num
     * @return array - patient gen details
     */
    function getPatientGeneralReport($pnum){
        return $this->_fetchAll(OPAL_GET_GENERAL_REPORT, array(
            array("parameter"=>":pnum","variable"=>$pnum,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get educational material options
     * @params $matType : string - material category
     * @return array - educ options
     */
    function getEducMatOptions($matType){
        return $this->_fetchAll(OPAL_GET_EDUCATIONAL_MATERIAL_OPTIONS, array(
            array("parameter"=>":matType","variable"=>$matType,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get educational material group report
     * @param $matType : string - material category
     * @param $matName : string - material name
     * @return array - educ material report
     */
    function getEducMatReport($matType, $matName){
        return $this->_fetchAll(OPAL_GET_EDUCATIONAL_MATERIAL_GROUP, array(
            array("parameter"=>":matType","variable"=>$matType,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":matName","variable"=>$matName,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get questionnaire options
     * @param void
     * @return array - questionnaire names (EN)
     */
    function getQstOptions(){
        return $this->_fetchAll(OPAL_GET_QUESTIONNAIRE_OPTIONS, array());
    }

    /**
     * Get questionnaires group report
     * @param $qName : string - questionnaire name
     * @return array - questionnaires group report
     */
    function getQstReport($qName){
        return $this->_fetchAll(OPAL_GET_QUESTIONNAIRE_REPORT_GROUP, array(
            array("parameter"=>":qName","variable"=>$qName,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get demographics group report
     * @param void
     * @return array - demographics report
     */
    function getDemoReport(){
        return $this->_fetchAll(OPAL_GET_DEMOGRAPHICS_REPORT_GROUP, array());
    }

    /*
     * Fetch all test results
     * @params  void
     * @return  array - list of test results
     * */
    function getTestResults() {
        return $this->_fetchAll(OPAL_GET_TEST_RESULTS, array());
    }

    /*
     * fetch all currently assigned test results
     * @params  void
     * @return  array - list of assigned tests
     * */
    function getAssignedTests() {
        return $this->_fetchAll(OPAL_GET_ASSIGNED_TESTS, array());
    }

    /*
     * Update the publish flag of a test result
     * @params  $id : int - primary key in test result control table
     *          $publishFlag : int - 0 (unpublished) or 1 (published)
     * @return  void
     * */
    function updateTestResultPublishFlag($id, $publishFlag) {
        $this->_execute(OPAL_UPDATE_TEST_RESULTS_PUBLISH_FLAG, array(
            array("parameter"=>":PublishFlag","variable"=>$publishFlag,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":LastUpdatedBy","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SessionId","variable"=>$this->getSessionId(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get details of a specific test result.
     * @params  $id : int - primary key in test result control table
     * @return  array - details from the test result
     * */
    function getTestResultDetails($id) {
        return $this->_fetchAll(OPAL_GET_TEST_RESULT_DETAILS, array(
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get list of expression names of a test result
     * @params  $id : int - primary key in test result control table
     * @return  array - list of expression names of the test result
     * */
    function getTestExpressionNames($id) {
        return $this->_fetchAll(OPAL_GET_TEST_EXPRESSION_NAMES, array(
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Publish a patient questionnaire
     * @params  int : $questionnaireControlSerNum - the questionnaire in OpalDB
     *          int : $patientSerNum - patient serial in OpalDB
     * @return int - ID of the record inserted
     * */
    function publishQuestionnaire($questionnaireControlSerNum, $patientSerNum) {
        $toSubmit = array(
            "QuestionnaireControlSerNum"=>$questionnaireControlSerNum,
            "PatientSerNum"=>$patientSerNum,
            "DateAdded"=>date("Y-m-d H:i:s"),
            "SessionId"=>$this->getSessionId()
        );
        return $this->_insertRecordIntoTable(OPAL_QUESTIONNAIRE_TABLE, $toSubmit);
    }

    /*
     * Get additional links of a test result
     * @params  $id : int - primary key in test result control table
     * @return  array - list of IDs of additional links
     * */
    /*    function getTestResultAdditionalLinks($id) {
            return $this->_fetchAll(OPAL_GET_TEST_RESULT_ADD_LINK, array(
                array("parameter"=>":TestResultControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            ));
        }*/

    /*
     * Get list of test results groups in french and english
     * @params  vois
     * @return  array - list of test result groups
     * */
    function getTestResultGroups() {
        return $this->_fetchAll(OPAL_GET_TEST_RESULT_GROUPS, array());
    }

    /*
     * Insert a test result in the test result control table
     * @params  $toInsert : array - Contains the test result info
     * @return  int - last insert ID
     * */
    function insertTestResult($toInsert) {
        $toInsert["DateAdded"] = date("Y-m-d H:i:s");
        $toInsert["LastPublished"] = date("Y-m-d H:i:s");
        $toInsert["LastUpdatedBy"] = $this->getOAUserId();
        $toInsert["SessionId"] = $this->getSessionId();
        return $this->_replaceRecordIntoTable(OPAL_TEST_CONTROL_TABLE, $toInsert);
    }

    /*
     * Insert a list of test expression codes into the test expression table
     * @params  $toInsert : array - list of test expression
     * @return  int - last ID entered
     * */
    function insertMultipleTestExpressions($toInsert) {
        foreach ($toInsert as &$item) {
            $item["DateAdded"] = date("Y-m-d H:i:s");
            $item["LastUpdatedBy"] = $this->getOAUserId();
            $item["SessionId"] = $this->getSessionId();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_TEST_RESULT_EXPRESSION_TABLE, $toInsert);
    }

    /*
     * Insert a list of additional links for test results
     * @params  $toInsert : array - list of test expression
     * @return  int - last ID entered
     * */
    /*    function insertTestResultAdditionalLinks($toInsert) {
            foreach ($toInsert as &$item) {
                $item["DateAdded"] = date("Y-m-d H:i:s");
            }
            return $this->_replaceMultipleRecordsIntoTable(OPAL_TEST_RESULT_ADD_LINKS_TABLE, $toInsert);
        }*/

    /*
     * Get if the educational material exists
     * @params  void
     * @return  array - list of educational material details
     * */
    function doesEduMaterialExists($id) {
        return $this->_fetchAll(OPAL_DOES_EDU_MATERIAL_EXISTS, array(
            array("parameter"=>":EducationalMaterialControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Deactivate all test results without test expression attached to.
     * @params  void
     * @return  int - number of records affected
     * */
    function sanitizeEmptyTestResults() {
        return $this->_updateRecordIntoTable(OPAL_SANITIZE_EMPTY_TEST_RESULTS, array(
            "LastUpdatedBy"=>$this->getOAUserId(),
            "SessionId"=>$this->getSessionId(),
        ));
    }

    /*
     * Update a test result control entry if the data changed.
     * @params  $toUpdate : array - contains all the details to update if necessary
     * @return  int - number of records affected.
     * */
    function updateTestControl($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();
        return $this->_updateRecordIntoTable(OPAL_UPDATE_TEST_CONTROL, $toUpdate);
    }

    /*
     * Delete test expressions from a test result that are not in use anymore.
     * @params  $id - int : ID of the test result
     * @return  $list - array : list of test expression name
     * @return  int - number of records deleted
     * */
    function removeUnusedTestExpression($id, $list) {
        $sqlUpdate = str_replace("%%LISTIDS%%", "'" . implode("', '", $list) . "'", OPAL_REMOVE_UNUSED_TEST_EXPRESSIONS);
        return $this->_execute($sqlUpdate, array(
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Count the number of the test additional links by a list of IDs.
     * @params  $ids - array : list of ids
     * @return  array: total count found
     * */
    /*    function countTestResultsAdditionalLinks($ids) {
            $sqlCount = str_replace("%%LISTIDS%%", implode(", ", $ids), OPAL_COUNT_TR_ADDITIONAL_LINKS);
            return $this->_fetch($sqlCount, array());
        }*/

    /*
     * Delete unused additionalk links that are not a list of IDS for a specific test result
     * @params  $id - int : ID of the test result
     *          $list - array : list of IDs not to delete
     * @return  int - number of records affected
     */
    /*    function deleteUnusedAddLinks($id, $list) {
            $sqlDelete = str_replace("%%LISTIDS%%", implode(", ", $list), OPAL_DELETE_UNUSED_ADD_LINKS);
            return $this->_execute($sqlDelete, array(
                array("parameter"=>":TestResultControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            ));
        }*/

    /*
     * Update a specific test restul additionnal link
     * @params  $toUpdate - array : contains all the additional links details
     * @return  int - number of records affected
     * */
    /*    function updateTestResultAdditionalLink($toUpdate) {
            return $this->_updateRecordIntoTable(OPAL_UPDATE_ADDITIONAL_LINKS, $toUpdate);
        }*/

    /*
     * Get the test result chart log
     * @params  void
     * @return  array : list of chart logs
     * */
    function getTestResultChartLog() {
        return $this->_fetchAll(OPAL_GET_TEST_RESULT_CHART_LOG, array());
    }

    /*
     * Get the test result chart log for a specific ID
     * @params  void
     * @return  array : list of chart logs
     * */
    function getTestResultChartLogById($id) {
        return $this->_fetchAll(OPAL_GET_TEST_RESULT_CHART_LOG_BY_ID, array(
            array("parameter"=>":TestResultControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Delete all expressions of a test result
     * @params  $id - int : ID of the test result
     * @return  int : number of records deleted.
     * */
    function unsetTestResultExpressions($id) {
        return $this->_execute(OPAL_UNSET_TEST_EXPRESSIONS, array(
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Delete all links of a test result
     * @params  $id - int : ID of the test result
     * @return  int : number of records deleted.
     * */
    /*    function deleteTestResultAdditionalLinks($id) {
            return $this->_execute(OPAL_DELETE_TEST_RESULT_ADDITIONAL_LINKS, array(
                array("parameter"=>":TestResultControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            ));
        }*/

    /*
     * Delete a test result
     * @params  $id - int : ID of the test result
     * @return  int : number of records deleted.
     * */
    function deleteTestResult($id) {
        return $this->_execute(OPAL_DELETE_TEST_RESULT, array(
            array("parameter"=>":TestControlSerNum","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Update the latest test result MH after deletion to update the userId and SessionId to the user who deleted it
     * @params  $id - int : user ID
     * @return  int : number of records updated.
     * */
    function updateTestResultMHDeletion($id) {
        $toUpdate = array(
            "LastUpdatedBy"=>$this->getOAUserId(),
            "SessionId"=>$this->getSessionId(),
            "TestResultControlSerNum"=>$id,
        );
        return $this->_updateRecordIntoTable(OPAL_UPDATE_TEST_RESULT_MH_DELETION, $toUpdate);
    }

    /*
     * Get all test names for test results
     * @params  void
     * @return  array : test names details
     * */
    function getTestNames() {
        return $this->_fetchAll(OPAL_GET_TEST_NAMES, array());
    }

    /*
     * count the total of test expressions that exists based on a list of IDs
     * @params  $list : array - list of IDs of test expressions
     * @return  array - total of IDs
     * */
    function countTestExpressionsIDs($list) {
        $sql = str_replace("%%LISTIDS%%", implode(", ", $list), OPAL_COUNT_TEST_IDS);
        return $this->_fetch($sql, array());
    }

    /**
     * Update a specific test expression
     * @param $testId
     * @param $testExpressionId
     * @return int - number of record updated
     */
    function updateTextExpression($testId, $testExpressionId) {
        $toUpdate = array(
            "LastUpdatedBy"=>$this->getOAUserId(),
            "SessionId"=>$this->getSessionId(),
            "TestControlSerNum"=>$testId,
            "TestExpressionSerNum"=>$testExpressionId,
        );
        return $this->_updateRecordIntoTable(OPAL_UPDATE_TEST_EXPRESSION, $toUpdate);
    }

    /*
     * Get the list of patients
     * @params  void
     * @return  array - List of patients
     * */
    function getPatientsList() {
        $results = $this->_fetchAll(OPAL_GET_PATIENTS_LIST, array());
        foreach($results as &$item) {
            $temp = $this->_fetchAll(OPAL_GET_MRN_PATIENT_SERNUM, array(array("parameter"=>":PatientSerNum","variable"=>$item["id"],"data_type"=>PDO::PARAM_INT)));
            $mrnList = array();
            foreach ($temp as $mrn)
                array_push($mrnList, $mrn["MRN"] . " (".$mrn["hospital"].")");
            if(count($mrnList) > 0)
                $item["name"] .= " (MRN: " . implode(", ", $mrnList) . ")";
        }
        return $results;
    }

    /*
     * Get a list of patients based on a list of IDs
     * @params  $list - array - Id of patients
     * @return  array - list of patients found
     * */
    function getPatientsListByIds($list) {
        $sql = str_replace("%%LISTIDS%%", implode(", ", $list), OPAL_GET_PATIENTS_LIST_BY_ID);
        return $this->_fetchAll($sql, array());
    }

    /**
     * Insert multiple patients to a study
     * @param $toInsert array - data to insert
     * @return int Last ID inserted
     */
    function insertMultiplePatientsStudy($toInsert) {
        return $this->_insertMultipleRecordsIntoTable(OPAL_PATIENT_STUDY_TABLE, $toInsert);
    }

    /**
     * Insert multiple questionnaires to a study
     * @param $toInsert - array - list of questionnaire/study ID
     * @return int Last ID inserted
     */
    function insertMultipleQuestionnairesStudy($toInsert) {
        return $this->_insertMultipleRecordsIntoTable(OPAL_QUESTIONNAIRE_STUDY_TABLE, $toInsert);
    }

    /**
     * Get the list of patients of a specific study
     * @param $studyId - ID of the study
     * @return array list of patients found
     */
    function getPatientsStudy($studyId) {
        return $this->_fetchAll(OPAL_GET_PATIENTS_STUDY, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the list of patient consents of a specific study
     * @param $studyId - ID of the study
     * @return array list of patients found
     */
    function getPatientsStudyConsents($studyId) {
        $results = $this->_fetchAll(OPAL_GET_PATIENTS_STUDY_CONSENTS, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
        foreach($results as &$item) {
            $temp = $this->_fetchAll(OPAL_GET_MRN_PATIENT_SERNUM, array(array("parameter"=>":PatientSerNum","variable"=>$item["id"],"data_type"=>PDO::PARAM_INT)));
            $mrnList = array();
            foreach ($temp as $mrn)
                array_push($mrnList, $mrn["MRN"] . " (".$mrn["hospital"].")");
            if(count($mrnList) > 0)
                $item["name"] .= " (MRN: " . implode(", ", $mrnList) . ")";
        }
        return $results;
    }

    /**
     * Check if a consent form is published
     * @param $consentId - consent form Id
     * @return array list of forms found
     */
    function checkConsentFormPublished($consentId){
        return $this->_fetchAll(OPAL_CHECK_CONSENT_FORM_PUBLISHED, array(
            array("parameter"=>":consentId","variable"=>$consentId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the study's current consent form
     * @param $studyId - int study ID
     * @return array consent form found
     */
    function getConsentFormByStudyId($studyId){
        return $this->_fetchAll(OPAL_GET_CONSENT_BY_STUDY_ID, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the list of questionnaires of a specifc study
     * @param $studyId - ID of the study
     * @return array - list of questionnaires associated to the study
     */
    function getQuestionnairesStudy($studyId) {
        return $this->_fetchAll(OPAL_GET_QUESTIONNAIRES_STUDY, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Delete all patients to a specific study EXCEPT the one provided in the array
     * @param $studyId - Study ID
     * @param $toKeep - array - List of patients to keep in the study
     * @return int - number of lines affected
     */
    function deletePatientsStudy($studyId, $toKeep) {
        $sql = str_replace("%%LISTIDS%%", implode(", ", $toKeep),OPAL_DELETE_PATIENTS_STUDY);
        return $this->_execute($sql, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Delete all questionnaires to a specific study EXCEPT the one provided in the array
     * @param $studyId - Study ID
     * @param $toKeep - array - List of questionnaires to keep in the study
     * @return int - number of lines affected
     */
    function deleteQuestionnairesStudy($studyId, $toKeep) {
        $sql = str_replace("%%LISTIDS%%", implode(", ", $toKeep),OPAL_DELETE_QUESTIONNAIRES_STUDY);
        return $this->_execute($sql, array(
            array("parameter"=>":studyId","variable"=>$studyId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Remove a specific questionnaire from all studies
     * @param $questionnaireId - ID of the questionnaire
     * @return int - number of lines affected
     */
    function purgeQuestionnaireFromStudies($questionnaireId) {
        return $this->_execute(OPAL_DELETE_QUESTIONNAIRE_FROM_STUDIES, array(
            array("parameter"=>":questionnaireId","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Update the publish flag of a specific patient
     * @param $id - Patient ID (or sernum)
     * @param $transfer - Status of the update
     * @return int - total record updated
     */
    function updatePatientPublishFlag($id, $transfer) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_PATIENT_PUBLISH_FLAG, array(
            "PatientUpdate"=>$transfer,
            "PatientSerNum"=>$id,
        ));
    }

    /**
     * Return the list of available patients
     * @return array
     */
    function getPatients() {
        $results = $this->_fetchAll(OPAL_GET_PATIENTS, array());
        foreach ($results as &$item)
            $item["MRN"] = $this->getMrnPatientSerNum($item["serial"]);
        return $results;
    }

    /**
     * Return the latest 20,000 entries of patient activity log
     * @return array
     */
    function getPatientActivityLog() {
        $results = $this->_fetchAll(OPAL_GET_PATIENT_ACTIVITY, array());
        foreach ($results as &$item) {
            $item["MRN"] = $this->getMrnPatientSerNum($item["serial"]);
            unset($item["serial"]);
        }
        return $results;
    }



    /**
     * Update specific patient demographic information
     * @params $toUpdate - array of demographics fields to be update
     *
     * @return void
     */

    function updatePatient($toUpdate) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_PATIENT, $toUpdate);
    }

    /**
     * Update patient identifiers list
     * @params $toUpdate - array of identifier information
     *
     * @return void
     */
    function updatePatientLink($toUpdate) {

        while (($identifier = array_shift($toUpdate)) !== NULL) {
            if (!empty($identifier["Patient_Hospital_Identifier_Id"])){
                $this->_updateRecordIntoTable(OPAL_UPDATE_PATIENT_HOSPITAL_IDENTIFIER,$identifier);
            } else {
                $this->_insertRecordIntoTable(OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE,$identifier);
            }
        }
        return ;
    }


    /*
     * Get the list of all undeleted master diagnoses
     * @params  void
     * @return  array - List of master diagnoses
     * */
    function getSourceTestResults() {
        return $this->_fetchAll(OPAL_GET_SOURCE_TEST_RESULTS, array());
    }

    /*
     * get the details of a source test result with the externalId and source
     * @params  $externalId - int - ID of the external source
     *          $source - int - primary key of the source itself
     * @return  array - details of the source
     * */
    function getSourceTestResultDetails($source, $code) {
        return $this->_fetchAll(OPAL_GET_SOURCE_TEST_RESULT_DETAILS, array(
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * @param $sourceDatabaseName - string - name of the source database
     * @return array
     */
    function getSourceId($sourceDatabaseName) {
        return $this->_fetchAll(OPAL_GET_SOURCE_ID, array(
            array("parameter"=>":SourceDatabaseName","variable"=>$sourceDatabaseName,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert list of test results code/description into the masterSourceTable.
     * @params  $toInsert : array - contains the list of all the diagnoses to insert.
     * @return  int - last inserted ID
     * */
    function insertSourceTestResults($toInsert) {
        foreach ($toInsert as &$item) {
            $item["createdBy"] = $this->getUsername();
            $item["updatedBy"] = $this->getUsername();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_MASTER_SOURCE_TEST_RESULT_TABLE, $toInsert);
    }

    /*
     * Replace an actual source diagnosis with a new one while keeping the primary key.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function replaceSourceTestResult($toUpdate) {
        return $this->_execute(OPAL_REPLACE_TEST_RESULT, array(
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":creationDate","variable"=>$toUpdate["creationDate"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":createdBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":ID","variable"=>$toUpdate["ID"],"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Update a source diagnosis with a new source and description. creation date and name are not affected. It is not
     * a replace.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function updateSourceTestResult($toUpdate) {
        return $this->_execute(OPAL_UPDATE_TEST_RESULT, array(
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":externalId","variable"=>$toUpdate["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$toUpdate["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * check if a specific source test results record exists by searching by code and source.
     * @params  $source - ID of the source
     *          $externalId - externalID of the record in the source
     * @returns int - list of any record found (if exists)
     * */
    function isSourceTestResultsExists($source, $code) {
        return $this->_fetchAll(OPAL_SOURCE_TEST_RESULTS_EXISTS, array(
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * mark a source test result as deleted.
     * @params  $toDelete - array - contains source and code
     * @returns int - number of records affected
     * */
    function markAsDeletedSourceTestResults($todelete) {
        return $this->_execute(OPAL_MARK_AS_DELETED_SOURCE_TEST_RESULT, array(
            array("parameter"=>":code","variable"=>$todelete["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$todelete["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":deletedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the list of all active database sources (i.e. not local)
     * @params  void
     * @return  array - list of activate database sources with ID and name
     * */
    function getExternalSourceDatabase() {
        return $this->_fetchAll(OPAL_GET_EXTERNAL_SOURCE_DB, array());
    }

    /*
     * Get the list of all undeleted master diagnoses
     * @params  void
     * @return  array - List of master diagnoses
     * */
    function getSourceDiagnoses() {
        return $this->_fetchAll(OPAL_GET_SOURCE_DIAGNOSES, array());
    }

    /*
     * get the details of a source diagnosis with the externalId and source
     * @params  $externalId - int - ID of the external source
     *          $source - int - primary key of the source itself
     * @return  array - details of the source
     * */
    function getSourceDiagnosisDetails($externalId, $source, $code) {
        return $this->_fetchAll(OPAL_GET_SOURCE_DIAGNOSIS_DETAILS, array(
            array("parameter"=>":externalId","variable"=>$externalId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert list of diagnoses code/description into the masterSourceTable.
     * @params  $toInsert : array - contains the list of all the diagnoses to insert.
     * @return  int - last inserted ID
     * */
    function insertSourceDiagnoses($toInsert) {
        foreach ($toInsert as &$item) {
            $item["createdBy"] = $this->getUsername();
            $item["updatedBy"] = $this->getUsername();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE, $toInsert);
    }

    /*
     * Replace an actual source diagnosis with a new one while keeping the primary key.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function replaceSourceDiagnosis($toUpdate) {
        return $this->_execute(OPAL_REPLACE_SOURCE_DIAGNOSIS, array(
            array("parameter"=>":externalId","variable"=>$toUpdate["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$toUpdate["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":creationDate","variable"=>$toUpdate["creationDate"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":createdBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Update a source diagnosis with a new source and description. creation date and name are not affected. It is not
     * a replace.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function updateSourceDiagnosis($toUpdate) {
        return $this->_execute(OPAL_UPDATE_SOURCE_DIAGNOSIS, array(
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":externalId","variable"=>$toUpdate["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$toUpdate["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * check if a specific record exists.
     * @params  $source - ID of the source
     *          $externalId - externalID of the record in the source
     * @returns int - list of any record found (if exists)
     * */
    function isMasterSourceDiagnosisExists($source, $externalId, $code) {
        return $this->_fetchAll(OPAL_IS_SOURCE_DIAGNOSIS_EXISTS, array(
            array("parameter"=>":externalId","variable"=>$externalId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * mark a source diagnoses as deleted.
     * @params  $toDelete - array - contains source and externalId
     * @returns int - number of records affected
     * */
    function markAsDeletedSourceDiagnoses($todelete) {
        return $this->_execute(OPAL_MARKED_AS_DELETED_SOURCE_DIAGNOSIS, array(
            array("parameter"=>":externalId","variable"=>$todelete["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$todelete["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$todelete["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":deletedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Get the list of all non deleted source aliases available
     * @params  void
     * @return  array - list of all non deleted source aliases
     * */
    function getSourceAliases(){
        return $this->_fetchAll(OPAL_GET_SOURCE_ALIASES, array());
    }

    /*
     * get the details of a source alias with the externalId and source
     * @params  $externalId - int - ID of the external source
     *          $source - int - primary key of the source itself
     * @return  array - details of the source alias
     * */
    function getSourceAliasDetails($externalId, $source, $code, $type) {
        return $this->_fetchAll(OPAL_GET_SOURCE_ALIAS_DETAILS, array(
            array("parameter"=>":externalId","variable"=>$externalId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":type","variable"=>$type,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Insert list of aliases code/description into the masterSourceTable.
     * @params  $toInsert : array - contains the list of all the aliases to insert.
     * @return  int - last inserted ID
     * */
    function insertSourceAliases($toInsert) {
        foreach ($toInsert as &$item) {
            $item["createdBy"] = $this->getUsername();
            $item["updatedBy"] = $this->getUsername();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_MASTER_SOURCE_ALIAS_TABLE, $toInsert);
    }

    /*
     * Replace an actual source alias with a new one while keeping the primary key.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function replaceSourceAlias($toUpdate) {
        return $this->_execute(OPAL_REPLACE_SOURCE_ALIAS, array(
            array("parameter"=>":externalId","variable"=>$toUpdate["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":type","variable"=>$toUpdate["type"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":source","variable"=>$toUpdate["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":createdBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Update a source alias with a new source and description. creation date and name are not affected. It is not
     * a replace.
     * @params  $toUpdate - array - contains the details of the record to replace with
     * @return  int - number of record affected
     * */
    function updateSourceAlias($toUpdate) {
        return $this->_execute(OPAL_UPDATE_SOURCE_ALIAS, array(
            array("parameter"=>":type","variable"=>$toUpdate["type"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$toUpdate["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":externalId","variable"=>$toUpdate["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$toUpdate["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":description","variable"=>$toUpdate["description"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Determined if a source alias exists already
     * @params  $source - int - ID of the source database
     *          $externalId - int - external ID of the source alias
     * @return  array - list of existing source alias
     * */
    function isMasterSourceAliasExists($source, $externalId, $code, $type) {
        return $this->_fetchAll(OPAL_IS_SOURCE_ALIAS_EXISTS, array(
            array("parameter"=>":externalId","variable"=>$externalId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":type","variable"=>$type,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$code,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * mark a source alias as deleted.
     * @params  $toDelete - array - contains source and externalId
     * @returns int - number of records affected
     * */
    function markAsDeletedSourceAliases($todelete) {
        return $this->_execute(OPAL_MARKED_AS_DELETED_SOURCE_ALIAS, array(
            array("parameter"=>":externalId","variable"=>$todelete["externalId"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":source","variable"=>$todelete["source"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":code","variable"=>$todelete["code"],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":type","variable"=>$todelete["type"],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":updatedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":deletedBy","variable"=>$this->getUsername(),"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * fetch all the available aliases in active source database
     * @return array
     */
    function getAliases() {
        return $this->_fetchAll(OPAL_GET_ALIASES, array());
    }

    /**
     * Get the list of alias expressions of an alias not yet published (or not used)
     * @param $aliasId int - ID of the alias
     * @return array - list of unpublished alias expressions
     */
    function getUnpublishedAliasExpression($aliasId) {
        return $this->_fetchAll(OPAL_GET_ALIASES_UNPUBLISHED_EXPRESSION, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasId,"data_type"=>PDO::PARAM_INT)
        ));
    }

    /**
     * Get the list of alias expressions of an alias already published (or in use)
     * @param $aliasId int - ID of the alias
     * @return array - list of published alias expressions
     */
    function getPublishedAliasExpression($aliasId) {
        return $this->_fetchAll(OPAL_GET_ALIASES_PUBLISHED_EXPRESSION, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasId,"data_type"=>PDO::PARAM_INT)
        ));
    }

    /**
     * Fetch the alias details
     * @param $aliasId int - ID of the alias
     * @return array - details
     */
    function getAliasDetails($aliasId) {
        return $this->_fetchAll(OPAL_GET_ALIAS_DETAILS, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Deactivate any alias without expressions.
     * @return int - number of records affected
     */
    function sanitizeEmptyAliases() {
        return $this->_updateRecordIntoTable(OPAL_SANITIZE_EMPTY_ALIASES, array(
            "LastUpdatedBy"=>$this->getOAUserId(),
            "SessionId"=>$this->getSessionId(),
        ));
    }

    /**
     * Update publish flag of a sepcific alias.
     * @param $aliasId - ID of the alias
     * @param $statusFlag - status of the alias
     * @return int - number of records affected
     */
    function updateAliasPublishFlag($aliasId, $statusFlag) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_ALIAS_PUBLISH_FLAG, array(
            "AliasUpdate"=>$statusFlag,
            "AliasSerNum"=>$aliasId,
            "LastUpdatedBy"=>$this->getOAUserId(),
            "SessionId"=>$this->getSessionId(),
        ));
    }

    /**
     * Get the list of source databases
     * @return array - list of source databases
     */
    function getSourceDatatabes() {
        return $this->_fetchAll(OPAL_GET_SOURCE_DATABASES, array());
    }

    /**
     * Get the list of codes for aliases based on the type and the source
     * @param $type int type of the alias (task, document or appointment)
     * @param $source int source database (aria, orms, medivisit, etc)
     * @return array
     */
    function getSourceAliasesByTypeAndSource($type, $source) {
        if ($source == ARIA_SOURCE_DB)
            $sqlQuery = OPAL_GET_ARIA_SOURCE_ALIASES;
        else
            $sqlQuery = OPAL_GET_SOURCE_ALIASES;

        return $this->_fetchAll($sqlQuery, array(
            array("parameter"=>":type","variable"=>$type,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":source","variable"=>$source,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the list of deactivated diagnoses codes for a specific diagnosis
     * @param $diagnosisTransId
     * @return array
     */
    function getdeactivatedDiagnosesCodes($diagnosisTransId) {
        return $this->_fetchAll(OPAL_GET_DEACTIVATED_DIAGNOSIS_CODES, array(
            array("parameter"=>":DiagnosisTranslationSerNum","variable"=>$diagnosisTransId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * get the list of all non deleted diagnosis codes ID
     * @param $listIds
     * @return array
     */
    function getListDiagnosisCodes($listIds) {
        return $this->_fetchAll(str_replace("%%LISTIDS%%", implode(", ", $listIds), OPAL_GET_LIST_DIAGNOSIS_CODES), array());
    }

    /**
     * Get all alias logs for one year
     * @return array
     */
    function getAliasLogs() {
        return $this->_fetchAll(OPAL_GET_ALIAS_LOGS, array());
    }

    /**
     * Get the log of a specific appointment (alias
     * @param $AliasSerNum - ID of the Alias
     * @return array
     */
    function getAppointmentLogs($aliasSerNum) {
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_LOGS, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasSerNum,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the log of a specific task (alias
     * @param $AliasSerNum - ID of the Alias
     * @return array
     */
    function getTaskLogs($aliasSerNum) {
        return $this->_fetchAll(OPAL_TASK_LOGS, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasSerNum,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the log of a specific document (alias
     * @param $AliasSerNum - ID of the Alias
     * @return array
     */
    function getDocumentLogs($aliasSerNum) {
        return $this->_fetchAll(OPAL_GET_DOCUMENT_LOGS, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasSerNum,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * count the number of educational material found with a specific ID
     * @param $eduId
     * @return array - total field counted
     */
    function countEduMaterial($eduId) {
        return $this->_fetch(OPAL_COUNT_EDU_MATERIAL, array(
            array("parameter"=>":EducationalMaterialControlSerNum","variable"=>$eduId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * count the number of hospital map found with a specific ID
     * @param $mapId
     * @return array - total field counted
     */
    function countHospitalMap($mapId) {
        return $this->_fetch(OPAL_COUNT_HOSPITAL_MAP, array(
            array("parameter"=>":HospitalMapSerNum","variable"=>$mapId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Count the number of alias expression from a list of IDs
     * @param $listIds array - list of IDs of alias expressions
     * @return array - total found
     */
    function selectAliasExpressionsToInsert($listIds) {
        return $this->_fetchAll(str_replace("%%LISTIDS%%", implode(", ", $listIds), OPAL_SELECT_ALIAS_EXPRESSIONS_TO_INSERT), array());
    }

    /**
     * Check if the source DB exists
     * @param $sourceId int - ID of the source database
     * @return array - total of source DB found
     */
    function countSourceDatabase($sourceId) {
        return $this->_fetch(OPAL_COUNT_SOURCE_DB, array(
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Insert new alias and returns latest ID created
     * @param $toInsert array - data to insert
     * @return int - new ID
     */
    function insertAlias($toInsert) {
        $toInsert["LastUpdatedBy"] = $this->getOAUserId();
        $toInsert["SessionId"] = $this->getSessionId();
        return $this->_insertRecordIntoTable(OPAL_ALIAS_TABLE, $toInsert);
    }

    /**
     * Insert a list of multiple alias expression
     * @param $toInsert array - data to insert
     * @return int - last inserted ID
     */
    function replaceAliasExpressions($toInsert) {
        foreach ($toInsert as &$item) {
            $item["LastUpdatedBy"] = $this->getOAUserId();
            $item["SessionId"] = $this->getSessionId();
        }
        return $this->_replaceMultipleRecordsIntoTable(OPAL_ALIAS_EXPRESSION_TABLE, $toInsert);
    }

    /**
     * Insert (by doing a SQL REPLACE) an appointment checkin
     * @param $toInsert
     * @return int - ID of the last insert
     */
    function replaceAppointmentCheckin($toInsert) {
        $toInsert["LastUpdatedBy"] = $this->getOAUserId();
        $toInsert["SessionId"] = $this->getSessionId();
        return $this->_replaceRecordIntoTable(OPAL_APPOINTMENT_CHECKIN_TABLE, $toInsert);
    }

    /**
     * Get a specific alias with appointment code and clinical description in the Alias table
     * @param $typeCode string - appointment type
     * @param $typeDesc string - appointment type description
     * @return array - data found if any
     */
    function getAlias($aliasType,$typeCode,$typeDesc) {
        return $this->_fetchAll(OPAL_GET_ALIAS_EXPRESSION, array(
            array("parameter"=>":AliasType","variable"=>$aliasType,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":ExpressionName","variable"=>$typeCode,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Description"   ,"variable"=>$typeDesc,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /**
     * Update an alias.
     * @param $toUpdate
     * @return int - number of row updated
     */
    function updateAlias($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();

        $sql = OPAL_UPDATE_ALIAS;
        $eduTemp = OPAL_EDU_MATERIAL_SERNUM;
        $eduTempCond = OPAL_EDU_MATERIAL_COND;
        if(empty($toUpdate["EducationalMaterialControlSerNum"])) {
            unset($toUpdate["EducationalMaterialControlSerNum"]);
            $eduTemp = str_replace(":EducationalMaterialControlSerNum", "NULL", $eduTemp);
            $eduTempCond = str_replace("!= :EducationalMaterialControlSerNum", "IS NOT NULL", $eduTempCond);
        }

        $hosTemp = OPAL_HOSP_MAP_SERNUM;
        $hosTempCond = OPAL_HOSP_MAP_COND;
        if(empty($toUpdate["HospitalMapSerNum"])) {
            unset($toUpdate["HospitalMapSerNum"]);
            $hosTemp = str_replace(":HospitalMapSerNum", "NULL", $hosTemp);
            $hosTempCond = str_replace("!= :HospitalMapSerNum", "IS NOT NULL", $hosTempCond);
        }

        $sql = str_replace("%%EDU_MATERIAL_COND%%", $eduTempCond, str_replace("%%EDU_MATERIAL%%", $eduTemp, $sql));
        $sql = str_replace("%%HOSP_MAP_COND%%", $hosTempCond, str_replace("%%HOSP_MAP%%", $hosTemp, $sql));

        return $this->_updateRecordIntoTable($sql, $toUpdate);
    }

    /**
     * Delete alias expressions of an alias except the list of aliases to keep. Will also ignore published and marked
     * as deleted source aliases.
     * @param $aliasId int - ID of the alias to delete the alias expressions
     * @param $sourceIds array - list of IDs of alias expression to keep
     * @return int - number of records affected
     */
    function deleteAliasExpressions($aliasId, $sourceIds) {
        $sql = str_replace("%%LIST_SOURCES_UIDS%%",implode(", ", $sourceIds), OPAL_DELETE_ALIAS_EXPRESSIONS);
        return $this->_execute($sql, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Update alias expression
     * @param $toUpdate array - details of the alias expression
     * @return int - number of records affected
     */
    function updateAliasExpression($toUpdate) {
        $toUpdate["LastUpdatedBy"] = $this->getOAUserId();
        $toUpdate["SessionId"] = $this->getSessionId();
        if(array_key_exists("LastTransferred", $toUpdate) && $toUpdate["LastTransferred"] != "")
            $sql = OPAL_UPDATE_ALIAS_EXPRESSION_WITH_LAST_TRANSFERRED;
        else
            $sql = OPAL_UPDATE_ALIAS_EXPRESSION;

        return $this->_updateRecordIntoTable($sql, $toUpdate);
    }

    /**
     * Get the list of deactivated alias expression where the source in masterSourceAlias is marked as deleted
     * @param $aliasId int - ID of the alias
     * @return array - list of alias expressions where the source mark tham as deleted
     */
    function getDeactivatedAliasExpressions($aliasId) {
        return $this->_fetchAll(OPAL_GET_DELETED_ALIASES_EXPRESSION, array(
            array("parameter"=>":AliasSerNum","variable"=>$aliasId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Count the total of existing alises from a list of alias ID
     * @param $listIDs - list of alias IDs
     * @return array
     */
    function getCountAliases($listIDs) {
        $sql = str_replace("%%LISTIDS%%",implode(", ", $listIDs), OPAL_GET_COUNT_ALIASES);
        return $this->_fetch($sql, array());
    }

    /**
     * Get the last completed questionnaire from a specific patient on a site.
     * @param $patientId - internal patient ID found
     * @return array - last answered questionnaire found (if any)
     */
    function getLastCompletedQuestionnaire($patientId) {
        return $this->_fetch(OPAL_GET_LAST_COMPLETED_QUESTIONNAIRE, array(
            array("parameter"=>":PatientSerNum","variable"=>$patientId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the lis of completed questionnaires of patient, grouped by MRN.
     * @param array $questionnaireList - list of questionnaire ID (optional)
     * @return array - results found
     */
    function getPatientsCompletedQuestionnaires($questionnaireList = array()) {
        $sql = str_replace(
            "%%CONDTION_OPTINAL%%",
            count($questionnaireList) > 0 ? str_replace("%%QUESTIONNAIRES_LIST%%", implode(", ", $questionnaireList), OPAL_CONDITION_QUESTIONNAIRES_OPTIONAL) : "",
            OPAL_GET_PATIENTS_COMPLETED_QUESTIONNAIRES
        );
        return $this->_fetchAll($sql, array());
    }

    /**
     * Find the list of studies associated to one questionnaire
     * @param $questionnaireId - ID of the questionnaire
     * @return array - studies found
     */
    function getStudiesQuestionnaire($questionnaireId) {
        return $this->_fetchAll(OPAL_GET_STUDIES_QUESTIONNAIRE, array(
            array("parameter"=>":questionnaireId","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * List the studies a patient consented for.
     * @param $mrn string - Medical Record Number
     * @param $site string - Code of the site
     * @return array - studies found
     */
    function getStudiesPatientConsented($mrn, $site) {
        return $this->_fetchAll(OPAL_GET_STUDIES_PATIENT_CONSENTED, array(
            array("parameter"=>":MRN","variable"=>$mrn,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Hospital_Identifier_Type_Code","variable"=>$site,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient appointment
     * @params $site : String - Patient identifier site
     * @params $mrn  : int - Patient identifier mrn
     * @return array - patient appointment details
     */
    function getAppointment($site,$mrn,$startDate,$endDate){
        return $this->_fetchAll(OPAL_GET_APPOINTMENT, array(
            array("parameter"=>":site","variable"=>$site,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":mrn","variable"=>$mrn,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":startDate","variable"=>$startDate,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":endDate","variable"=>$endDate,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Get patient appointment
     * @params $sourceSystem : String - Source System (Aria, Medivisit, etc)
     * @params $sourceId  : int - Source System Appointment Id
     * @return array - an appointment details
     */
    function findAppointment($sourceSystem,$sourceId){
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_ID, array(
            array("parameter"=>":SourceSystem","variable"=>$sourceSystem,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceId","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get Pending Appointment
     * @params $sourceSystem : String - Source System (Aria, Medivisit, etc)
     * @params $sourceId  : int - Source System Appointment Id
     * @return array - an appointment details
     */
    function findPendingAppointment($sourceSystem,$sourceId){
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_PENDING, array(
            array("parameter"=>":SourceSystem","variable"=>$sourceSystem,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceId","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get pending appointment history
     * @params $sourceSystem : String - Source System (Aria, Medivisit, etc)
     * @params $sourceId  : int - Source System Appointment Id
     * @return array - an appointment details
     */
    function findPendingMHAppointment($sourceSystem,$sourceId){
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_PENDING_MH, array(
            array("parameter"=>":SourceSystem","variable"=>$sourceSystem,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceId","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Insert an appointment only if it does not exists already.
     * @param $toInsert
     * @return int - number of row modified
     */
    function insertAppointment($toInsert) {
        return $this->_replaceRecordIntoTable(OPAL_APPOINTMENTS_TABLE, $toInsert);
    }

    /**
     * Insert pending appointment.
     * @param $toInsert
     * @return int - number of row modified
     */
    function insertPendingAppointment($toInsert) {
        return $this->_replaceRecordIntoTable(OPAL_APPOINTMENTS_PENDING_TABLE, $toInsert);
    }

    /**
     * Insert pending appointment history.
     * @param $toInsert
     * @return int - number of row modified
     */
    function insertPendingMHAppointment($toInsert) {
        return $this->_insertRecordIntoTable(OPAL_APPOINTMENTS_PENDING_MH_TABLE, $toInsert);        
    }

    /**
     * Delete a specific appointmentPending record
     * @param $id - primary key of the record in appointmentPending to delete
     * @return int - number of records affected
     */
    function deleteAppointmentPending($id) {
        $toDelete = array(
            array("parameter"=>":AppointmentSerNum","variable"=>$id),
        );
        return $this->_execute(OPAL_DELETE_APPOINTMENT_PENDING, $toDelete);
    }

    /**
     * Delete a specific appointment.
     * @params  $id : int - Diagnosis sernum
     * @return  int - number of record deleted
     */
    function deleteAppointment($toUpdate) {
        $this->_execute(OPAL_UPDATE_APPOINTMENT_STATUS, array(
            array("parameter"=>":AppointmentSerNum","variable"=>$toUpdate['AppointmentSerNum'],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$toUpdate['SourceDatabaseSerNum'],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":Status","variable"=>$toUpdate['Status'],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":State","variable"=>$toUpdate['State'],"data_type"=>PDO::PARAM_STR),            
        ));
    }

    function getMrnPatientSerNum($patientSerNum) {
        return $this->_fetchAll(OPAL_GET_MRN_PATIENT_SERNUM, array(
            array("parameter"=>":PatientSerNum","variable"=>$patientSerNum,"data_type"=>PDO::PARAM_INT)));
    }

    /**
     * Insert failed resource info into resourcePendingError table
     * @param $sourceName string - name of the source (e.g. Aria)
     * @param $appointmentId int - external Appointment Id
     * @param $resources string - list of resources
     * @param $error string - error found
     * @return int - last record ID
     */
    function insertResourcePendingError($sourceName, $appointmentId, $resources, $error) {
        $toInsert = array(
            "sourceName"=>$sourceName,
            "appointmentId"=>$appointmentId,
            "resources"=>$resources,
            "error"=>$error,
            "creationDate"=>date("Y-m-d H:i:s"),
            "createdBy"=>$this->username,
            "updatedBy"=>$this->username,
        );
        return $this->_insertRecordIntoTable(OPAL_RESOURCE_PENDING_ERROR_TABLE, $toInsert);
    }

    /**
     * Get an appointment by the externalId and sourceId if it exists
     * @param $appointmentAriaId - external ID
     * @param $sourceId - Source ID
     * @return array - data found if any
     */
    function getAppointmentForResource($appointmentAriaId, $sourceId) {
        return $this->_fetchAll(OPAL_GET_APPOINTMENT_FOR_RESOURCE, array(
            array("parameter"=>":AppointmentAriaSer","variable"=>$appointmentAriaId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get a specific resource pending from the resource pending table
     * @param $source string - source database name
     * @param $appointmentId int - appointment ID of the future appointment
     * @return array - data found if any
     */
    function getResourcePending($source, $appointmentId) {
        return $this->_fetchAll(OPAL_GET_RESOURCE_PENDING, array(
            array("parameter"=>":sourceName","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":appointmentId","variable"=>$appointmentId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Insert Insert pending resource only if they don't exist.
     * @param $toInsert array - pending resource details
     * @return int - last inserted ID
     */
    function insertPendingResource($toInsert) {
        $toInsert["createdBy"] = $this->getUsername();
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["updatedBy"] = $this->getUsername();
        return $this->_insertRecordIntoTableConditional(OPAL_RESOURCE_PENDING_TABLE, $toInsert);
    }

    /**
     * Update pending resource
     * @param $toUpdate - pending resource details
     * @return int - number of row modified
     */
    function updatePendingResource($toUpdate) {
        $toUpdate["updatedBy"] = $this->getUsername();
        return $this->_updateRecordIntoTable(OPAL_UPDATE_RESOURCE_PENDING, $toUpdate);
    }

    /**
     * Update a resource
     * @param $toUpdate - resource details
     * @return int - number of row modified
     */
    function updateResource($toUpdate) {
        return $this->_updateRecordIntoTable(OPAL_UPDATE_RESOURCE, $toUpdate);
    }

    /**
     * Insert a resource only if it does not exists already.
     * @param $toInsert
     * @return int - number of row modified
     */
    function insertResource($toInsert) {
        return $this->_insertRecordIntoTableConditional(OPAL_RESOURCE_TABLE, $toInsert);
    }

    /**
     * Get a list of resources based on the list of resource code and source and add the appointmentId to the result.
     * This result is used to update the pivot table.
     * @param $resources array - list of resources with code.
     * @param $sourceId int - source database ID
     * @param $appointmentId int - ID of the appointment to which is associated the resources
     * @return array - contains the list of internal resource ID with the appointment ID and other data to update pivot table
     */
    function getResourceIds($resources, $sourceId, $appointmentId) {
        $cpt = 0;
        $dataSQL = "SourceDatabaseSerNum = :source AND (";
        $dataToList = array(
            array("parameter"=>":source","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentSerNum","variable"=>$appointmentId,"data_type"=>PDO::PARAM_INT),
        );
        foreach ($resources as $resource) {
            $dataSQL .= "ResourceCode = :code$cpt OR ";
            array_push($dataToList,
                array("parameter"=>":code$cpt","variable"=>$resource["code"],"data_type"=>PDO::PARAM_STR),
            );
            $cpt++;
        }
        $dataSQL = substr($dataSQL, 0, -4);
        $dataSQL .= ")";
        $sql = str_replace("%%SOURCE_CODE_LIST%%", $dataSQL, OPAL_GET_RESOURCES_FOR_RESOURCE_APPOINTMENT);

        return $this->_fetchAll($sql, $dataToList);
    }

    /**
     * Delete from pivot table resourceAppointment ressource not attached to specific appointment
     * @param $appointmentId int - appointment ID
     * @param $resourceIdList array - list of resource IDs
     * @return int - number of rows affected
     */
    function deleteResourcesForAppointment($appointmentId, $resourceIdList) {
        $sql = str_replace("%%RESOURCE_ID_LIST%%", implode(", ", $resourceIdList), DELETE_FROM_RESOURCE_APPOINTMENT);
        return $this->_execute($sql, array(
            array("parameter"=>":AppointmentSerNum","variable"=>$appointmentId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Insert into pivot table resourceAppointment missing links. This is a conditional insert on the fields
     * ResourceSerNum and AppointmentSerNum if they do not exist.
     * @param $records array - records to add
     * @return int - number of records affected
     */
    function insertResourcesForAppointment($records) {
        return $this->_replaceMultipleRecordsIntoTableConditional(OPAL_RESOURCE_APPOINTMENT_TABLE, $records,
            array("ResourceSerNum", "AppointmentSerNum")
        );
    }

    /**
     * Update ever resource pending of level 1 with appointment ready to level 2.
     * @return int - number or records affected
     */
    function updateResourcePendingLevelInProcess() {
        return $this->_updateRecordIntoTable(UPDATE_RESOURCE_PENDING_LEVEL_IN_PROCESS, array("updatedBy"=>$this->getUsername()));
    }

    /**
     * Return the oldest resource pending marked as a level 2 (processing)
     * @return array - data found
     */
    function getOldestResourcePendingInProcess() {
        return $this->_fetchAll(GET_OLDEST_RESOURCE_PENDING_IN_PROCESS, array());
    }

    /**
     * Delete a specific resourcePending record
     * @param $id - primary key of the record in resourcePending to delete
     * @return int - number of records affected
     */
    function deleteResourcePendingInProcess($id) {
        $toDelete = array(
            array("parameter"=>":ID","variable"=>$id),
        );
        return $this->_execute(OPAL_DELETE_RESOURCE_PENDING, $toDelete);
    }

    /**
     * Update the check-in of an appointment to checked if it was not checked and returned the number of affected rows
     * @param $source int - source database ID
     * @param $appointment - external appointment ID
     * @return int - number of row affected
     */
    function updateCheckInForAppointment($source, $appointment) {
        return $this->_updateRecordIntoTable(UPDATE_APPOINTMENT_CHECKIN, array(
            "SourceDatabaseSerNum"=>$source,
            "AppointmentAriaSer"=>$appointment,
        ));
    }

    /**
     * Get the first site and mrn found based on its source and appointment ID. Used to send push notification.
     * @param $source int - source database ID
     * @param $appointment - external appointment ID
     * @return array - list of records found. Must be one since a LIMIT 1 is set up in the SQL
     */
    function getFirstMrnSiteBySourceAppointment($source, $appointment) {
        return $this->_fetchAll(OPAL_GET_FIRST_MRN_SITE_BY_SOURCE_APPOINTMENT, array(
            array("parameter"=>":SourceDatabaseName","variable"=>$source,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentAriaSer","variable"=>$appointment,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the list of publication settings
     * @return array - list of records found
     */
    function getPublicationSettings() {
        return $this->_fetchAll(OPAL_GET_PUBLICATION_SETTINGS, array());
    }

    /**
     * Get the list of publication settings to ignore
     * @return array - list of records found
     */
    function getPublicationSettingsToIgnore() {
        $tempResults = $this->_fetchAll(OPAL_GET_PUBLICATION_SETTINGS_TO_IGNORE, array());
        $results = array();
        foreach ($tempResults as $item) {
            $internalName = explode(",", $item["internalName"]);
            foreach ($internalName as $item2)
                array_push($results, $item2);
        }
        return $results;
    }

    /**
     * Delete the frequency event from a specific questionnaire
     * @param $questionnaireId - questionnaire from which frequency events are deleted
     * @return int - number of records deleted
     */
    function deleteQuestionnaireFrequencyEvents($questionnaireId) {
        return $this->_execute(OPAL_DELETE_QUESTIONNAIRE_FREQUENCY_EVENTS, array(
            array("parameter"=>":ControlTableSerNum","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**

     * Update ever appointment pending of level 1 with appointment ready to level 2.
     * @return int - number or records affected
     */
    function updateAppointmentPendingLevelInProcess() {
        return $this->_updateRecordIntoTable(UPDATE_APPOINTMENT_PENDING_LEVEL_IN_PROCESS, array("updatedBy"=>$this->getUsername()));
    }

    /**
     * Return the oldest appointment pending marked as a level 2 (processing)
     * @return array - data found
     */
    function getOldestAppointmentPendingInProcess() {
        return $this->_fetchAll(GET_OLDEST_APPOINTMENT_PENDING_IN_PROCESS, array());
    }

    /**
     * Update an appointment
     * @param $toUpdate - appointment details
     * @return int - number of row modified
     */
    function updateAppointment($toUpdate) {        
        $this->_execute(OPAL_UPDATE_APPOINTMENT_STATUS, array(
            array("parameter"=>":Status","variable"=>$toUpdate['Status'],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":State","variable"=>$toUpdate['State'],"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$toUpdate['SourceDatabaseSerNum'],"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":AppointmentSerNum","variable"=>$toUpdate['AppointmentSerNum'],"data_type"=>PDO::PARAM_INT),
        ));
    }

    /* Get the latest dates of entries from the audit system table
     * @return array - list of records found
     */
    function getAuditSystemLastDates() {
        return $this->_fetchAll(OPAL_GET_AUDIT_SYSTEM_LAST_DATES, array());
    }

    /**
     * Get a list of audit system entries based on their creation date
     * @param $date string - date of records to retrieve
     * @return array - list of records found
     */
    function getAuditSystemEntriesByDate($date) {
        return $this->_fetchAll(OPAL_GET_AUDIT_SYSTEM_ENTRIES_BY_DATE, array(
            array("parameter"=>":creationDate","variable"=>$date,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /**
     * Delete a list of audit system entries based on their creation date
     * @param $date string - date of records to delete
     * @return int - number of records deleted
     */
    function deleteAuditSystemByDate($date) {
        return $this->_execute(OPAL_DELETE_AUDIT_SYSTEM_BY_DATE, array(
            array("parameter"=>":creationDate","variable"=>$date,"data_type"=>PDO::PARAM_STR),
        ));
    }

    function countAuditSystemRemainingDates() {
        return $this->_fetch(OPAL_COUNT_AUDIT_SYSTEM_REMAINING_DATES, array());
    }

    /**
     * Get a specific document with ID
     * @param $documentId string - DocumentID
     * @param $sourceId string - appointment type description
     * @return array - data found if any
     */
    function getDocument($sourceId,$documentId) {
        return $this->_fetchAll(OPAL_GET_DOCUMENT, array(            
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$sourceId,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":DocumentId"   ,"variable"=>$documentId,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /**
     * Insert an appointment only if it does not exists already.
     * @param $toInsert
     * @return int - number of row modified
     */
    function insertDocument($toInsert) {
        return $this->_replaceRecordIntoTable(OPAL_DOCUMENT_TABLE, $toInsert);
    }


    /**
     * Insert a document info only if it does not exists already.
     * @param $toInsert
     * @return int - number of row modified
     */
    function updateDocument($records) {
        return $this->_replaceRecordIntoTable(OPAL_DOCUMENT_TABLE, $records);
    }


    /**
     * Get patient device 
     * @param $typeCode string - appointment type
     * @param $typeDesc string - appointment type description
     * @return array - data found if any
     */
    function getPatientDeviceIdentifiers($patientser) {
        return $this->_fetchAll(OPAL_GET_PATIENT_DEVICE_IDENTIFIERS, array(
            array("parameter"=>":Patientser","variable"=>$patientser,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /**
     * Get patient notificaton control detail
     * @param $patientser string - patient ID
     * @param $notificationType string - notification description
     * @return array - data found if any
     */
    function getNotificationControlDetails($patientser, $notificationtype){
        return $this->_fetchAll(OPAL_GET_NOTIFICATION_CONTROL_DETAILS, array(
            array("parameter"=>":Patientser","variable"=>$patientser,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":Notificationtype","variable"=>$notificationtype,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /** 
     * Insert a new pushnotification
     * @param  array of the pushnotification infos
     * @return  ID of the entry
     */
    function insertPushNotification($toInsert) {        
        return $this->_replaceRecordIntoTable(OPAL_PUSH_NOTIFICATION_TABLE, $toInsert);
    }

    /**
     *  Get patient access level
     * @param $patientser string - patient ID
     * @return array - data found if any
     */
    function getPatientAccessLevel($patientser){        
        return $this->_fetch(OPAL_GET_PATIENT_ACCESS_LEVEL, array(
            array("parameter"=>":PatientSer","variable"=>$patientser,"data_type"=>PDO::PARAM_STR)
        ));
    }

    /** 
    * Get Alias and alias expression information
    * @param $expresionId - aliasExpressionSerNum
    * @return array - data found if any
    */
    function getAliasExpressionDetail($expressionId){
        return $this->_fetch(OPAL_GET_ALIAS_EXPRESSION_DETAIL, array(
            array("parameter"=>":AliasExpressionSerNum","variable"=>$expressionId,"data_type"=>PDO::PARAM_INT)
        ));
    }

    /** 
     * Insert a new notification
     * @param  array of the notification infos
     * @return  ID of the entry
     */
    function insertNotification($toInsert) {        
        return $this->_replaceRecordIntoTable(OPAL_NOTIFICATION_TABLE, $toInsert);
    }

    /** 
     * Get Staff Serial Number
     * @param  int source database serial number
     * @param  int staff ID 
     * @return  array Staff Detail
     */
    function getStaffDetail($sourceId,$staffId){
        return $this->_fetch(OPAL_GET_STAFF_DETAIL, array(
            array("parameter"=>":SourceDatabaseSerNum","variable"=>$sourceId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":StaffId","variable"=>$staffId,"data_type"=>PDO::PARAM_STR)
        ));
    }
}