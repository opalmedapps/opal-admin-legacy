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
    public function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $dsn = false, $newOAUserId = false) {
        parent::__construct($newServer, $newDB, $newPort, $newUserDB, $newPass, $dsn, $newOAUserId);
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
        $sqlModule = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_TABLE, $sqlModule);
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
        $sqlModule = str_replace("%%MASTER_SOURCE_DIAGNOSTIC%%", OPAL_MASTER_SOURCE_DIAGNOSTIC_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_TEST_RESULT%%", OPAL_MASTER_SOURCE_TEST_RESULT_TABLE, $sqlModule);
        $sqlModule = str_replace("%%ALIAS_EXPRESSION%%", OPAL_ALIAS_EXPRESSION_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MODULE%%", OPAL_MODULE_TABLE, $sqlModule);

        return $this->_fetchAll($sqlModule, array());
    }

    function markCustomCodeAsDeleted($id, $moduleId) {
        $details = $this->getCustomCodeDetails($id, $moduleId);
        if($details["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");

        $toDelete = array(
            "ID"=>$id,
            "deletedBy"=>$this->getUsername(),
            "updatedBy"=>$this->getUsername(),
        );

        $sql = str_replace("%%MASTER_SOURCE_TABLE%%", $details["masterSource"], SQL_OPAL_MARK_AS_DELETED_MASTER_SOURCE);
        return $this->_updateRecordIntoTable($sql, $toDelete);
    }

    function getCustomCodeDetails($customCodeId, $moduleId) {
        $module = $this->getModuleSettings($moduleId);
        if($module["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");

        $sqlModule = $module["sqlCustomCodeDetails"];
        $sqlModule = str_replace("%%MASTER_SOURCE_ALIAS%%", OPAL_MASTER_SOURCE_ALIAS_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_DIAGNOSTIC%%", OPAL_MASTER_SOURCE_DIAGNOSTIC_TABLE, $sqlModule);
        $sqlModule = str_replace("%%MASTER_SOURCE_TEST_RESULT%%", OPAL_MASTER_SOURCE_TEST_RESULT_TABLE, $sqlModule);
        $sqlModule = str_replace("%%ALIAS_EXPRESSION%%", OPAL_ALIAS_EXPRESSION_TABLE, $sqlModule);

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
        $sqlFetchPerModule = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_TABLE, $sqlFetchPerModule);
        $sqlFetchPerModule = str_replace("%%PHASEINTREATMENT%%", OPAL_PHASE_IN_TREATMENT_TABLE, $sqlFetchPerModule);

        $result["publications"] = $this->_fetchAll($sqlFetchPerModule,  array(array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT)));
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
        $sqlFetchDetails = str_replace("%%EDUCATIONALMATERIAL%%", OPAL_EDUCATION_MATERIAL_TABLE, $sqlFetchDetails);
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
        $sqlPublicationListLog = str_replace("%%EDUCATION_MATERIAL_CONTROL_TABLE%%", OPAL_EDUCATION_MATERIAL_TABLE, $sqlPublicationListLog);
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
    function deleteFrequencyEvent($controlTableSerNum, $controlTable) {
        $toDelete = array(
            array("parameter"=>":ControlTableSerNum","variable"=>$controlTableSerNum,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ControlTable","variable"=>$controlTable,"data_type"=>PDO::PARAM_INT),
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
                $tableToInsert = OPAL_MASTER_SOURCE_DIAGNOSTIC_TABLE;
                break;
            default:
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");
        }
        return $this->_insertRecordIntoTable($tableToInsert, $toInsert);
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
                $tableToUpdate = OPAL_MASTER_SOURCE_DIAGNOSTIC_TABLE;
                break;
            default:
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");
        }

        $sql = str_replace("%%MASTER_TABLE%%", $tableToUpdate, OPAL_UPDATE_MASTER_SOURCE);

        return $this->_updateRecordIntoTable($sql, $toUpdate);
    }

    /*
     * Returns the details of a publication module
     * @params  $moduleId (int) Id of the module
     * @return  array of records found
     * */
    function getPublicationModuleUserDetails($moduleId){
        return $this->_fetch(SQL_OPAL_GET_PUBLICATION_MODULES_USER_DETAILS, array(array("parameter"=>":ID","variable"=>$moduleId,"data_type"=>PDO::PARAM_INT)));
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
        return $this->_insertRecordIntoTable(OPAL_FREQUENCY_EVENTS_TABLE, $record);
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
        return $this->_insertMultipleRecordsIntoTable(OPAL_MASTER_SOURCE_ALIAS_TABLE, $toInsert);
    }

    /*
     * Get all the patients triggers
     * @params  void
     * @return  patient triggers found (array)
     * */
    function getPatientsTriggers() {
        return $this->_fetchAll(OPAL_GET_PATIENTS_TRIGGERS, array());
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
}