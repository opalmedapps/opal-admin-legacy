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
                $this->username = $_SESSION["username"];
                $this->userRole = $_SESSION["roleId"];
            }
            else {
                $userInfo = $this->_getUserInfoFromDB($newOAUserId);
                $this->OAUserId = $userInfo["OAUserId"];
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
        $result = $this->_fetchAll(SQL_OPAL_SELECT_USER_INFO,
            array(
                array("parameter"=>":OAUserSerNum","variable"=>$newOAUserId,"data_type"=>PDO::PARAM_INT),
            ));

        if (!is_array($result) || count($result) != 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");
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
        $lastId = $this->_insertRecordIntoTable($tableToInsert, $toInsert);
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
        return $this->_insertRecordIntoTable(OPAL_USER_ACTIVITY_LOG_TABLE, $toInsert);
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

    /*
     * insert a new user and the date of adding
     * @params  $username (string) username (duh!)
     *          $password (string) encrypted password
     *          $language (string) preferred language
     * @return  array with the result of the insert
     * */
    function insertUser($username, $password, $language, $roleId) {
        $toInsert = array(
            "Username"=>$username,
            "Password"=>$password,
            "Language"=>$language,
            "oaRoleId"=>$roleId,
            "DateAdded"=>date("Y-m-d H:i:s"),
        );
        return $this->_insertRecordIntoTable(OPAL_OAUSER_TABLE, $toInsert);
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
        return $this->_insertRecordIntoTable(OPAL_OAUSER_TABLE, $toInsert);
    }

    /*
     * insert into the intersection table of role-user to give a role to an user
     * @params  $userId (int) ID of the user
     *          $roleId (int) ID of the role
     * @return  array with the result of the insert
     * */
    function insertUserRole($userId, $roleId) {
        return $this->_insertRecordIntoTable(OPAL_OAUSER_ROLE_TABLE, array("OAUserSerNum"=>$userId, "RoleSerNum"=>$roleId));
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
        return $this->_insertRecordIntoTable(OPAL_STUDY_TABLE, $newStudy);
    }

    /*
     * Get the details of a study by its ID
     * @params  $studyId (int) ID of the study
     * @returns (array) details of the study
     * */
    function getStudyDetails($studyId) {
        return $this->_fetch(OPAL_GET_STUDY_DETAILS,
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
        return $this->_insertRecordIntoTable(OPAL_OA_ROLE_TABLE, $toInsert);
    }

    /*
     * Insert operations linked for a new role and a series of module.
     * @params  $toInsert : array - operation for each module for a specific roles
     * @returns int : ID of the entry
     * */
    function insertRoleModule($toInsert) {
        return $this->_insertMultipleRecordsIntoTable(OPAL_OA_ROLE_MODULE_TABLE, $toInsert);
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
        return $this->_insertMultipleRecordsIntoTable(OPAL_OA_ROLE_MODULE_TABLE, $multipleUpdates);
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

    /*
     * Get access level for a specific combo or role/module
     * */
    function getUserAccess($roleId) {
        return $this->_fetchAll(OPAL_GET_USER_ACCESS, array(
            array("parameter"=>":oaRoleId","variable"=>$roleId,"data_type"=>PDO::PARAM_INT),
        ));
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
        return $this->_insertRecordIntoTable(OPAL_ALERT_TABLE, $toSubmit);
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
        return $this->_insertRecordIntoTable(OPAL_AUDIT_TABLE, $toInsert);
    }

    /*
     * Insert user's action in the audit table
     * @params  $toSubmit : array - Contains the user's info
     * @return  int - latest ID created
     * */
    function insertAuditForceUser($toInsert) {
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        return $this->_insertRecordIntoTable(OPAL_AUDIT_TABLE, $toInsert);
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

        return $this->_insertRecordIntoTable(OPAL_DIAGNOSIS_TRANSLATION_TABLE, $toInsert);
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
        return $this->_insertMultipleRecordsIntoTable(OPAL_DIAGNOSIS_CODE_TABLE, $toInsert);
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
}