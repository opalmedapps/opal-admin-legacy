<?php
/**
 * Created by PhpStorm.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:45 AM
 */

class DatabaseQuestionnaire extends DatabaseAccess
{

    /*
     * This function returns the next content ID for the dictionary necessary for an insertion
     * @param   void
     * @return  next content ID (string)
     * */
    protected function _getNextContentId() {
        $nextContentId = $this->_fetch(SQL_QUESTIONNAIRE_GET_DICTIONARY_NEXT_CONTENT_ID);
        return $nextContentId["nextContentId"];
    }

    /*
     * This function translate a new type of question into the legacy one, since the app does not recognize them yet.
     * @param   $typeId (int)
     * @return  legacy type (string)
     * */
    function getLegacyType($typeId) {
        return $this->_fetch(SQL_QUESTIONNAIRE_GET_LEGACY_TYPE, array(array("parameter"=>":typeId", "variable"=>$typeId, "data_type"=>PDO::PARAM_INT)));
    }

    /*
     * This function returns the language table.
     * @param   void
     * @return  array of languages
     * */
    function getLanguageTable() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_ALL_LANGUAGE);
    }

    /* This function add entries to the dictionary. First, the contentId is calculated since it should be the same for
     * all languages. Then the entries are created in the dictionary. If there is less entries than the number of
     * languages, empty entries will be created with the mention XX_ in the content where XX is the iso-lang code in
     * upper case.
     * Entry:   associative array of language id and its content.
     *          example: array(1=>"exemple", "2"=>"example")
     *          table name where is used the entry
     * Return:  new contentID of matching all entries.
     */
    function addToDictionary($newEntries, $tableName) {
        $tableId = $this->getTableId($tableName);
        $languageTable = $this->getLanguageTable();
        $toInsert = array();
        $contentId = $this->_getNextContentId();

        foreach($languageTable as $lang) {
            $toInsert[$lang["ID"]] = array(
                "languageId"=>$lang["ID"],
                "content"=>strtoupper($lang["isoLang"]."_"),
                "contentId"=>$contentId,
                "tableId"=>$tableId,
                "creationDate"=>date("Y-m-d H:i:s"),
                "createdBy"=>$this->username,
                "updatedBy"=>$this->username,
            );
        }

        $newEntries = HelpSetup::arraySanitization($newEntries);
        foreach($newEntries as $key=>$value) {
            $toInsert[$key]["content"] = $value;
        }
        $result = $this->_insertMultipleRecordsIntoDictionary($toInsert, $contentId);
        if(intval($result) == 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot insert entry in dictionary, content ID already exists.");
        return $contentId;
    }

    /*
     * this function create a copy of an entry in the dictionary and return the new contentId.
     * Entry:   content ID in the table to duplicate
     *          table name where is used the entry
     * Return:  new contentID of matching all entries.
     * */
    function copyToDictionary($contentId, $tableName) {
        $toCopy = $this->_fetchAll(SQL_QUESTIONNAIRE_GET_DICTIONNARY_TEXT, array(array("parameter"=>":contentId", "variable"=>$contentId, "data_type"=>PDO::PARAM_INT)));
        if (!is_array($toCopy) || count($toCopy) <= 0) return false;
        $tableId = $this->getTableId($tableName);
        $newContentId = $this->_getNextContentId();

        $toInsert = array();
        foreach($toCopy as $row) {
            array_push($toInsert, array(
                "tableId"=>$tableId,
                "languageId"=>$row["languageId"],
                "content"=>$row["content"],
                "contentId"=>$newContentId,
                "creationDate"=>date("Y-m-d H:i:s"),
                "createdBy"=>$this->username,
                "updatedBy"=>$this->username,
            ));
        }

        $result = $this->_insertMultipleRecordsIntoDictionary($toInsert, $newContentId);
        if(intval($result) == 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot insert entry in dictionary, content ID already exists.");
        return $newContentId;
    }

    /*
     * This function update an entry in a specific language into the dictionary. It will update an entry only if it was
     * modified. It accepts multiples entries if necessary.
     * @params  $updatedEntries array(
     *                              array(
     *                                  "content"=>"new text",
     *                                  "languageId"=>Specified language (int),
     *                                  "contentId"=>content Id from the dictionary,
     *                              ),
     *                              array(
     *                                  "content"=>"new text",
     *                                  "languageId"=>Specified language (int),
     *                                  "contentId"=>content Id from the dictionary,
     *                              ),
     *                          );
     *          $tableName (string) name of the table associated with the specifics entries
     * @return  total of lines modified (int)
     * */
    function updateDictionary($updatedEntries, $tableName) {
        $updatedEntries = HelpSetup::arraySanitization($updatedEntries);

        $total = 0;
        $tableId = $this->getTableId($tableName);
        foreach($updatedEntries as $data) {
            $toUpdate = array(
                array("parameter"=>":content","variable"=>$data["content"]),
                array("parameter"=>":languageId","variable"=>$data["languageId"]),
                array("parameter"=>":contentId","variable"=>$data["contentId"]),
                array("parameter"=>":updatedBy","variable"=>$this->username),
                array("parameter"=>":tableId","variable"=>$tableId),
            );
            $total += $this->_execute(SQL_QUESTIONNAIRE_UPDATE_DICTIONARY, $toUpdate);
        }
        return $total;
    }

    /*
     * Update a question with a list of specific values with the username of the user stored. The update will occur
     * only if there was any modification done to the question.
     * @param   $updatedEntries (array) updated question
     * @return  total modified records
     * */
    function updateQuestion($updatedEntries) {
        $updatedEntries["updatedBy"]=$this->getUsername();
        $updatedEntries["OAUserId"]=$this->getOAUserId();
        return $this->_updateRecordIntoTable(SQL_QUESTIONNAIRE_UPDATE_QUESTION, $updatedEntries);
    }

    /*
     * Update a question type with a list of specific values with the username of the user stored. The update will occur
     * only if there was any modification done to the template.
     * @param   $updatedEntries (array) updated question
     * @return  total modified records
     * */
    function updateTemplateQuestion($updatedEntries) {
        $updatedEntries["updatedBy"]=$this->getUsername();
        $updatedEntries["OAUserId"]=$this->getOAUserId();
        return $this->_updateRecordIntoTable(SQL_QUESTIONNAIRE_UPDATE_TYPE_TEMPLATE, $updatedEntries);
    }

    /*
     * This function forces a table to be updated with the date of update and the username. It is necessary if the
     * table was not modified directly but its options were.
     * @params  id of the question to force update (int)
     * @return  total record updated (string)
     * */
    function forceUpdate($id, $tableName) {
        $sqlQuery = str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_FORCE_UPDATE_UPDATEDBY);
        $updatedEntries = array(
            "ID"=>$id,
            "updatedBy"=>$this->getUsername(),
            "OAUserId"=>$this->getOAUserId(),
        );
        return $this->_updateRecordIntoTable($sqlQuery, $updatedEntries);
    }

    /*
     * Update a questionnaire with a list of specific values with the username of the user stored. The update will occur
     * only if there was any modification done to the questionnaire.
     * @param   $updatedEntries (array) updated questionnaire
     * @return  total modified records
     * */
    function updateQuestionnaire($updatedEntries) {
        $updatedEntries["updatedBy"]=$this->getUsername();
        $updatedEntries["OAUserId"]=$this->getOAUserId();
        return $this->_updateRecordIntoTable(SQL_QUESTIONNAIRE_UPDATE_QUESTIONNAIRE, $updatedEntries);
    }

    /*
     * This function looks into the definition table of the questionnaire and returns the ID of the requested table
     * @param   string of a table name
     * @return  its table ID
     * */
    function getTableId($tableName) {
        $tableId = $this->_fetch(SQL_QUESTIONNAIRE_GET_DEFINITION_TABLE_ID,
            array(
                array("parameter"=>":tableName","variable"=>$tableName,"data_type"=>PDO::PARAM_STR),
            ));
        return $tableId["ID"];
    }

    /*
     * This function returns the names of the questionnaires
     * @params  questionnaire ID
     * @return  array of names of the questionnaire
     * */
    function getQuestionnaireNames($questionnaireId) {
        return $this->_fetch(SQL_QUESTIONNAIRE_GET_QUESTIONNAIRE_NAMES,
            array(
                array("parameter"=>":questionnaireId","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_STR),
            ));
    }

    /*
     * This function lists all the questions types a specific user can have access.
     * @param   void
     * @return  array of question types
     * */
    function getTemplatesQuestions() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTIONS,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function returns the details of a specific question type.
     * @param   ID of the question type (int)
     * @return  array of details of the question type
     * */
    function getTemplateQuestionDetails($templateQuestionId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_DETAILS,
            array(
                array("parameter"=>":ID","variable"=>$templateQuestionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function lists all the options of a specific question types from its table.
     * @param   ID of the question type, name of the table options
     * @return  all the options available for the specified question type
     * */
    function getTemplateQuestionsOptions($tableId, $tableName, $subTableName) {
        $mainSql = str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_ID_FROM_TEMPLATE_TYPES_OPTION);
        $mainId = $this->_fetch($mainSql, array(
            array("parameter"=>":ID","variable"=>$tableId,"data_type"=>PDO::PARAM_INT),
        ));
        $mainId = $mainId["ID"];
        $subSql = str_replace("%%SUBTABLENAME%%", strip_tags($subTableName), SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_OPTIONS);
        return $this->_fetchAll($subSql, array(
            array("parameter"=>":subTableId","variable"=>$mainId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * This function returns all the general categories types of question (slider, checkbox, etc)
     * @param   void
     * @return  array of types
     * */
    function getTemplateQuestionList() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTIONS_CATEGORIES);
    }

    /*
     * This function lists all the questions to be displayed in a listing
     * @param   void
     * @return  array of questions
     * */
    function fetchAllQuestions() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONS,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function fetch all the libraries to which a question is attached if the user is authorized to list
     * @param   Id of a question (int)
     * @return  list of libraries (array)
     * */
    function fetchLibrariesQuestion($questionId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_FETCH_LIBRARIES_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function fetch all the questionnaires ID to which a question is attached if the user is authorized to list
     * @param   ID of a question (int)
     * @return  list of questionnaires (array)
     * */
    function fetchQuestionnairesIdQuestion($questionId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_FETCH_QUESTIONNAIRES_ID_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function returns the last time a specific table was updated.
     * @param   string of the table name
     * @return  array of last time it was updated.
     * */
    function getLastTimeTableUpdated($tableName, $idFromTable) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_QUESTIONNAIRE_GET_LAST_TIME_TABLE_UPDATED);
        return $this->_fetch($sql, array(
            array("parameter"=>":ID","variable"=>$idFromTable,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * This function look if a specific record was updated since a specific time before another operation of
     * modification can occur. It is done to prevent two users who tried to update the same record on the same table
     * on the same time to erase the modification, based on a FIFO priority.
     *
     * @param   Table name to look at (string)
     *          ID of the table (BIGINT)
     *          last time the record was updated (string)
     *          name of the person who updated the record (string)
     * @return  total of modification made: should be 0 or 1 (array)
     * */
    function canRecordBeUpdated($tableName, $tableId, $lastUpdated, $updatedBy) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_QUESTIONNAIRE_CAN_RECORD_BE_UPDATED);
        return $this->_fetch($sql, array(
            array("parameter"=>":tableId","variable"=>$tableId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":lastUpdated","variable"=>$lastUpdated,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$updatedBy,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * This function add a new question type to the type template table of the questionnaire DB.
     * @param   array of a new question type. It also adds the username of the user who made the request
     * @return  ID of the record
     * */
    function addToTypeTemplateTable($newTemplateQuestion) {
        $newTemplateQuestion["OAUserId"] = $this->OAUserId;
        $newTemplateQuestion["creationDate"] = date("Y-m-d H:i:s");
        $newTemplateQuestion["createdBy"] = $this->username;
        $newTemplateQuestion["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(TEMPLATE_QUESTION_TABLE, $newTemplateQuestion);
    }

    /*
     * This function add to the correct templateQuestion option table its values.
     * @params  name of the type template table where to do the insert (string)
     *          lists of options to insert (array) in the dependant table of templateQuestion. If it is a slider for
     *          example, it will contains, the min and max answers with captions. If it is a checkbox type, it will be
     *          the minimum and maximum number of answers.
     * @returns ID of the record
     * */
    function addToTypeTemplateTableType($tableName, $optionToInsert) {
        $result = $this->_replaceRecordIntoTable($tableName, $optionToInsert);
        return $result;
    }

    /*
     * This function creates a new library, and adds the username of the creator.
     * @params  Settings to create a new library.
     *          array(
     *              "name"=>content ID from the dictionary,
     *              "private"=>int if the library is private or not,
     *          );
     * @return  ID of the record
     * */
    function addToLibraryTable($toInsert) {
        $toInsert["OAUserId"] = $this->OAUserId;
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["createdBy"] = $this->username;
        $toInsert["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(LIBRARY_TABLE, $toInsert);
    }

    /*
     * This function add to the correct templateQuestion option in the suv table its values. For example, all the possible
     * options for checkboxes or radio buttons.
     * @params  name of the type template table where to do the insert (string)
     *          lists of options to insert (array) in the dependant table of the option. If it is a checkbox or radio
     *          button type, it will be the description, order for example
     * @returns ID of the record
     * */
    function addToTypeTemplateTableTypeOptions($tableName, $optionToInsert) {
        $result = $this->_replaceMultipleRecordsIntoTable($tableName, $optionToInsert);
        return $result;
    }

    /*
     * This function fetch all possible libraries a user can see.
     * @params  void
     * @returns array of libraries
     * */
    function fetchAllLibraries() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_ALL_LIBRARIES,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * this function lists all the options of a specific checkbox template
     * @params  Id of the template checkbox
     * @return  all the possible options of the checkbox template (array)
     * */
    function getTypeTemplateCheckboxOption($ttcId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_CHECKBOX_OPTION,
            array(
                array("parameter"=>":parentTableID","variable"=>$ttcId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * this function lists all the options of a specific radio button template
     * @params  Id of the template radio button
     * @return  all the possible options of the radio button template (array)
     * */
    function getTypeTemplateRadioButtonOption($ttrId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_RADIO_BUTTON_OPTION,
            array(
                array("parameter"=>":parentTableID","variable"=>$ttrId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function returns a list of libraries based on a list of libraries IDs requested
     * @params  array of libraries ID
     * @return  array of libraries requested if available and visible
     * */
    function getLibrariesByIds($listIds) {
        $sqlFetchAll = str_replace("%%LISTOFIDS%%", $listIds, SQL_QUESTIONNAIRE_GET_USER_LIBRARIES);
        return $this->_fetchAll($sqlFetchAll,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function validate and return a question type for the user. If there is any extra options, (like for
     * checkboxes or radio buttons), it will append them to the array before returning it to the calling functioné
     * @params  ID of the requested question type
     * @returns array with all the question type information and options
     * */
    function getTypeTemplate($templateQuestionID){
        $result = $this->_fetchAll(SQL_QUESTIONNAIRE_GET_TYPE_TEMPLATE,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":ID","variable"=>$templateQuestionID,"data_type"=>PDO::PARAM_INT),
            ));

        if(!is_array($result) || count($result) != 1) return false;
        $result = $result[0];
        if($result["ttcID"] != "")
            $result["options"] = $this->getTypeTemplateCheckboxOption($result["ttcID"]);
        else if($result["ttrID"] != "")
            $result["options"] = $this->getTypeTemplateRadioButtonOption($result["ttrID"]);
        return $result;
    }

    /*
     * Get a list of specific libraries based on an list of IDs and if the user is authorized to see them
     * @params  array of ids of libraries
     * @return  array of libraries
     * */
    function getLibraries($arrLib) {
        return $this->_fetchAll(str_replace("%%LIBRARIES_ID%%", implode(", ", $arrLib),SQL_QUESTIONNAIRE_GET_LIBRARIES),array(
            array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Insert a question into the question table, and adding the username of the creator.
     * @params  array of settings for the question
     * @returns ID of the question.
     * */
    function insertQuestion($toInsert) {
        $toInsert["OAUserId"] = $this->OAUserId;
        $toInsert["createdBy"] = $this->username;
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(QUESTION_TABLE, $toInsert);
    }

    /*
     * Insert a questionnaire into the questionnaire table, and adding the username of the creator.
     * @params  array of settings for the questionnaire
     * @returns ID of the questionnaire.
     * */
    function insertQuestionnaire($toInsert) {
        $toInsert["OAUserId"] = $this->OAUserId;
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["createdBy"] = $this->username;
        $toInsert["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(QUESTIONNAIRE_TABLE, $toInsert);
    }

    /*
     * Insert a section of a questionnaire into the section table, and adding the username of the creator.
     * @params  array of settings for the section
     * @returns ID of the section.
     * */
    function insertSection($toInsert) {
        $toInsert["creationDate"] = date("Y-m-d H:i:s");
        $toInsert["createdBy"] = $this->username;
        $toInsert["updatedBy"] = $this->username;
        return $this->_replaceRecordIntoTable(SECTION_TABLE, $toInsert);
    }

    /*
     * Insert the options of a specific question to the correct table
     * @params  table name options where to do the insert (string)
     *          array of options to insert in the table mentionned above
     * @returns ID of the record.
     * */
    function insertQuestionOptions($tableName, $toInsert) {
        return $this->_replaceRecordIntoTable($tableName, $toInsert);
    }

    /*
     * Insert all the options of a checkbox question
     * @params  array of options to insert in the checkbox option tables
     * @returns void
     * */
    function insertCheckboxOption($toInsert) {
        $this->_replaceMultipleRecordsIntoTable(CHECKBOX_OPTION_TABLE, $toInsert);
    }

    /*
     * Insert all the options of a radio button question
     * @params  array of options to insert in the radio button
     * @returns void
     * */
    function insertRadioButtonOption($toInsert) {
        $this->_replaceMultipleRecordsIntoTable(RADIO_BUTTON_OPTION_TABLE, $toInsert);
    }

    /*
     * Returns a list of questions a user is authorized to access based on a list of IDs
     * @params  array of IDs
     * @returns array of questions the user is authorized to access
     * */
    function fetchQuestionsByIds($idList) {
        $sqlFetch = str_replace("%%LISTIDS%%", implode(", " , $idList), SQL_QUESTIONNAIRE_FETCH_QUESTIONS_BY_ID);
        return $this->_fetchAll($sqlFetch,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Count the number of questions marked as private in the array of questions. This is useful to determine if a
     * questionnaire has be be considered as private if at least one question is private.
     * @params  arrays of IDs
     * @return total of questions marked as private (array)
     * */
    function countPrivateQuestions($idList) {
        $sqlFetch = str_replace("%%LISTIDS%%", implode(", " , $idList), SQL_QUESTIONNAIRE_COUNT_PRIVATE_QUESTIONS);
        return $this->_fetch($sqlFetch, array());
    }

    /*
     * Delete into the library question intersection table. It will remove all libraries not in the list of IDs passed
     * @params  question ID of the question (int), array of libraries to keep
     * @returns total records affected (int)
     * */
    function removeLibrariesForQuestion($questionId, $libraries) {
        $sanitizedArray = array();
        foreach($libraries as $library) {
            $library = strip_tags($library);
            if ($library != "")
                array_push($sanitizedArray, $library);
        }
        $sqlToDelete = str_replace("%%LIBRARYIDS%%", implode(", ", $sanitizedArray), SQL_QUESTIONNAIRE_DELETE_LIBRARY_QUESTION);
        return $this->_execute($sqlToDelete,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function removes all libraries associated to a specific question. Used when marking a question as being
     * deleted.
     * @params  question ID (int)
     * @return  number of records affected (int)
     * */
    function removeAllLibrariesForQuestion($questionId) {
        return $this->_execute(SQL_QUESTIONNAIRE_DELETE_ALL_LIBRARIES_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function removes all sections associated to a specific question. Used when marking a question as being
     * deleted.
     * @params  question ID (int)
     * @return  number of records affected (int)
     * */
    function removeAllSectionForQuestion($questionId) {
        return $this->_execute(SQL_QUESTIONNAIRE_DELETE_ALL_SECTIONS_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Returns all the options of a question that needs to be deleted
     * @params  table name where to look at (string), parent table name of the option (string), ID of the parent table
     *          (int), IDs of options to keep (array of int)
     * @return  array of records found
     * */
    function fetchTemplateQuestionOptionsToBeDeleted($tableName, $parentTable, $parentTableId, $idsToBeKept) {
        $sqlSelect = str_replace("%%GRANDPARENTFIELDNAME%%", "templateQuestionId", str_replace("%%GRANDPARENTTABLE%%", TEMPLATE_QUESTION_TABLE, str_replace("%%OPTIONIDS%%", implode(", ", $idsToBeKept),str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_SELECT_OPTIONS_TO_BE_DELETED)))));

        return $this->_fetchAll($sqlSelect,
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Returns all the options of a question that needs to be deleted
     * @params  table name where to look at (string), parent table name of the option (string), ID of the parent table
     *          (int), IDs of options to keep (array of int)
     * @return  array of records found
     * */
    function fetchQuestionOptionsToBeDeleted($tableName, $parentTable, $parentTableId, $idsToBeKept) {
        $sqlSelect = str_replace("%%GRANDPARENTFIELDNAME%%", "questionId", str_replace("%%GRANDPARENTTABLE%%", QUESTION_TABLE, str_replace("%%OPTIONIDS%%", implode(", ", $idsToBeKept),str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_SELECT_OPTIONS_TO_BE_DELETED)))));

        return $this->_fetchAll($sqlSelect,
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function removes options associated to a specific question. Used when updating a question.
     * @params  table name where to look at (string), parent table name of the option (string), ID of the parent table
     *          (int), IDs of options to keep (array of int)
     * @return  array of records deleted
     * */
    function deleteOptionsForQuestion($tableName, $parentTable, $parentTableId, $idsToBeKept) {
        $sqlSelect = str_replace("%%GRANDPARENTFIELDNAME%%", "questionId", str_replace("%%GRANDPARENTTABLE%%", QUESTION_TABLE, str_replace("%%OPTIONIDS%%", implode(", ", $idsToBeKept),str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_DELETE_QUESTION_OPTIONS)))));

        return $this->_execute($sqlSelect,
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function removes options associated to a specific template question. Used when updating a template question.
     * @params  table name where to look at (string), parent table name of the option (string), ID of the parent table
     *          (int), IDs of options to keep (array of int)
     * @return  array of records deleted
     * */
    function deleteOptionsForTemplateQuestion($tableName, $parentTable, $parentTableId, $idsToBeKept) {
        $sqlSelect = str_replace("%%GRANDPARENTFIELDNAME%%", "templateQuestionId", str_replace("%%GRANDPARENTTABLE%%", TEMPLATE_QUESTION_TABLE, str_replace("%%OPTIONIDS%%", implode(", ", $idsToBeKept),str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_DELETE_QUESTION_OPTIONS)))));

        return $this->_execute($sqlSelect,
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function removes sections associated to a specific question. Used when deleting a question.
     * @params  table name where to look at (string), parent table name of the option (string), ID of the parent table
     *          (int), IDs of options to keep (array of int)
     * @return  array of records deleted
     * */
    function deleteQuestionsFromSection($sectionId, $idsToBeKept) {
        $sqlSelect = str_replace("%%OPTIONIDS%%", implode(", ", $idsToBeKept), SQL_QUESTIONNAIRE_DELETE_QUESTION_SECTION);

        return $this->_execute($sqlSelect,
            array(
                array("parameter"=>":sectionId","variable"=>$sectionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function updates the  options associated to a specific question. Used when updating a question.
     * @params  table name where to look at (string) ID of the parent table (int), options to update (array)
     * @return  total records executed
     * */
    function updateOptionsForQuestion($tableName, $id, $option) {
        $sqlOptionsToUpdate = array();
        $sqlOptionsWereUpdated = array();
        $optionsToUpdate = array();
        foreach ($option as $key=>$value) {
            array_push($optionsToUpdate, array(
                "parameter"=>":$key",
                "variable"=>$value,
            ));
            array_push($sqlOptionsToUpdate, "`$key` = :$key");
            array_push($sqlOptionsWereUpdated, "`$key` != :$key");
        }

        $sqlSelect = str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_UPDATE_QUESTION_OPTIONS);
        $sqlSelect = str_replace("%%OPTIONSTOUPDATE%%", implode(", ", $sqlOptionsToUpdate), $sqlSelect);
        $sqlSelect = str_replace("%%OPTIONSWEREUPDATED%%", implode(" OR ", $sqlOptionsWereUpdated), $sqlSelect);

        array_push($optionsToUpdate, array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT));

        return $this->_execute($sqlSelect, $optionsToUpdate);
    }

    /*
     * This function updates the options associated to a specific template question. Used when updating a template
     * question.
     * @params  table name where to look at (string) ID of the parent table (int), options to update (array)
     * @return  total records executed
     * */
    function updateOptionsForTemplateQuestion($tableName, $id, $option) {
        $sqlOptionsToUpdate = array();
        $sqlOptionsWereUpdated = array();
        $optionsToUpdate = array();
        foreach ($option as $key=>$value) {
            array_push($optionsToUpdate, array(
                "parameter"=>":$key",
                "variable"=>$value,
            ));
            array_push($sqlOptionsToUpdate, "`$key` = :$key");
            array_push($sqlOptionsWereUpdated, "`$key` != :$key");
        }

        $sqlSelect = str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_UPDATE_TEMPLATE_QUESTION_OPTIONS);
        $sqlSelect = str_replace("%%OPTIONSTOUPDATE%%", implode(", ", $sqlOptionsToUpdate), $sqlSelect);
        $sqlSelect = str_replace("%%OPTIONSWEREUPDATED%%", implode(" OR ", $sqlOptionsWereUpdated), $sqlSelect);

        array_push($optionsToUpdate, array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT));

        return $this->_execute($sqlSelect, $optionsToUpdate);
    }

    /*
     * This function updates the list of multiple options associated to a specific question. Used when updating a
     * question.
     * @params  table name where to look at (string), ID of the parent table (int), options to update (array)
     * @return  total records executed
     * */
    function updateSubOptionsForQuestion($tableName, $parentTable, $id, $option) {
        $sqlOptionsToUpdate = array();
        $sqlOptionsWereUpdated = array();
        $optionsToUpdate = array();
        foreach ($option as $key=>$value) {
            array_push($optionsToUpdate, array(
                "parameter"=>":$key",
                "variable"=>$value,
            ));
            array_push($sqlOptionsToUpdate, "`$key` = :$key");
            array_push($sqlOptionsWereUpdated, "`$key` != :$key");
        }

        $sqlSelect = str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_UPDATE_QUESTION_SUB_OPTIONS));
        $sqlSelect = str_replace("%%OPTIONSTOUPDATE%%", implode(", ", $sqlOptionsToUpdate), $sqlSelect);
        $sqlSelect = str_replace("%%OPTIONSWEREUPDATED%%", implode(" OR ", $sqlOptionsWereUpdated), $sqlSelect);

        array_push($optionsToUpdate, array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT));

        return $this->_execute($sqlSelect, $optionsToUpdate);
    }

    /*
     * This function updates the list of multiple options associated to a specific template question. Used when
     * updating a template question.
     * @params  table name where to look at (string), ID of the parent table (int), options to update (array)
     * @return  total records executed
     * */
    function updateSubOptionsForTemplateQuestion($tableName, $parentTable, $id, $option) {
        $sqlOptionsToUpdate = array();
        $sqlOptionsWereUpdated = array();
        $optionsToUpdate = array();
        foreach ($option as $key=>$value) {
            array_push($optionsToUpdate, array(
                "parameter"=>":$key",
                "variable"=>$value,
            ));
            array_push($sqlOptionsToUpdate, "`$key` = :$key");
            array_push($sqlOptionsWereUpdated, "`$key` != :$key");
        }

        $sqlSelect = str_replace("%%PARENTTABLE%%", $parentTable, str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_UPDATE_TEMPLATE_QUESTION_SUB_OPTIONS));
        $sqlSelect = str_replace("%%OPTIONSTOUPDATE%%", implode(", ", $sqlOptionsToUpdate), $sqlSelect);
        $sqlSelect = str_replace("%%OPTIONSWEREUPDATED%%", implode(" OR ", $sqlOptionsWereUpdated), $sqlSelect);

        array_push($optionsToUpdate, array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT));

        return $this->_execute($sqlSelect, $optionsToUpdate);
    }

    /*
     * This function updates the section associated with a question. Used when updating a question.
     * @params  table name where to look at (string), ID of the parent table (int), options to update (array)
     * @return  total records executed
     * */
    function updateQuestionSection($sectionId, $questionId, $option) {
        $sqlOptionsToUpdate = array();
        $sqlOptionsWereUpdated = array();
        $optionsToUpdate = array();
        foreach ($option as $key=>$value) {
            array_push($optionsToUpdate, array(
                "parameter"=>":$key",
                "variable"=>$value,
            ));
            array_push($sqlOptionsToUpdate, "`qst`.`$key` = :$key");
            array_push($sqlOptionsWereUpdated, "`qst`.`$key` != :$key");
        }

        $sqlSelect = str_replace("%%OPTIONSTOUPDATE%%", implode(", ", $sqlOptionsToUpdate), SQL_QUESTIONNAIRE_UPDATE_QUESTION_SECTION);
        $sqlSelect = str_replace("%%OPTIONSWEREUPDATED%%", implode(" OR ", $sqlOptionsWereUpdated), $sqlSelect);

        array_push($optionsToUpdate,
            array("parameter"=>":sectionId","variable"=>$sectionId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT)
        );
        return $this->_execute($sqlSelect, $optionsToUpdate);
    }

    function updateLastCheckboxOption($tableName, $parentTableId) {
        $result = 0;
        $hardCodedLastOption = array(
            array("id"=>1,"content"=>"Aucune de ces réponses"),
            array("id"=>2,"content"=>"None of the above"),
        );

        foreach($hardCodedLastOption as $option) {
            $sqlUpdate = str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_UPDATE_LAST_CHECKBOX_OPTION);
            $optionsToUpdate = array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":content","variable"=>$option["content"],"data_type"=>PDO::PARAM_STR),
                array("parameter"=>":languageID","variable"=>$option["id"],"data_type"=>PDO::PARAM_INT)
            );

            $result += $this->_execute($sqlUpdate, $optionsToUpdate);
        }
        return $result;
    }

    /*
     * This function removes all tags associated with a question. Used when deleting a question.
     * @params  Id the question (int)
     * @return  total records executed
     * */
    function removeAllTagsForQuestion($questionId) {
        return $this->_execute(SQL_QUESTIONNAIRE_DELETE_ALL_TAGS_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":OAUserId","variable"=>$this->getOAUserId(),"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Insert into the library question intersection table. It will associate a question with a list of libraries
     * @params  array of options to insert in the table mentioned above
     * @returns void
     * */
    function insertLibrariesForQuestion($records) {
        return $this->_replaceMultipleRecordsIntoTableConditional(LIBRARY_QUESTION_TABLE, $records);
    }

    /*
     * Insert multiple options to a question.
     * @params  table name of the options (string), array of records to insert
     * @return number of records created
     * */
    function insertOptionsQuestion($tableName, $records) {
        return $this->_replaceMultipleRecordsIntoTableConditional($tableName, $records);
    }

    /*
     * Insert multiple options to a template question.
     * @params  table name of the options (string), array of records to insert
     * @return number of records created
     * */
    function insertOptionsTemplateQuestion($tableName, $records) {
        return $this->_replaceMultipleRecordsIntoTableConditional($tableName, $records);
    }

    /*
     * Insert multiple questions to a section.
     * @params  records to insert into the intersection table
     * @return  number of records created
     * */
    function insertQuestionsIntoSection($records) {
        return $this->_replaceMultipleRecordsIntoTableConditional(QUESTION_SECTION_TABLE, $records);
    }

    /*
     * Get all the details of a specific questions.
     * @params  Question id (int)
     * @return  array of details of the question itself
     * */
    function getQuestionDetails($questionId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_QUESTION_DETAILS,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":ID","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get details of all questions associated with a specific section of a questionnaire
     * @params  Section id (int)
     * @return  array of questions
     * */
    function getQuestionsBySectionId($sectionId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_QUESTIONS_BY_SECTION_ID,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":sectionId","variable"=>$sectionId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get lists of all finalized questionnaires, used to display questions ready for a questionnaire for example
     * @params  Section id (int)
     * @return  array of questions
     * */
    function getFinalizedQuestions() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_FINALIZED_QUESTIONS,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get all the details of a specific questionnaire.
     * @params  Questionnaire id (int)
     * @return  array of details of the question itself
     * */
    function getQuestionnaireDetails($questionnaireId) {
        $result = $this->_fetchAll(SQL_QUESTIONNAIRE_GET_QUESTIONNAIRE_DETAILS,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":ID","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            ));
        return $result;
    }

    /*
     * Get all sections of a specific questionnaire.
     * @params  questionnaire id (int)
     * @return  array of sections
     * */
    function getSectionsByQuestionnaireId($questionnaireId) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_SECTION_BY_QUESTIONNAIRE_ID,
            array(
                array("parameter"=>":questionnaireId","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get options of a question (except slider)
     * @params  question id (int) and table name of the option (string)
     * @returns array of options
     * */
    function getQuestionOptionsDetails($questionId, $tableName, $fieldName = "questionId") {
        $sqlFetch = str_replace("%%FIELDNAME%%", $fieldName, str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_QUESTION_OPTIONS));
        return $this->_fetchAll($sqlFetch,
            array(
                array("parameter"=>":fieldId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get slider options of a question
     * @params  question id (int) and table name of the option (string)
     * @returns array of options
     * */
    function getQuestionSliderDetails($questionId, $tableName, $fieldName = "questionId") {
        $sqlFetch = str_replace("%%FIELDNAME%%", $fieldName, str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_QUESTION_SLIDER_OPTIONS));
        return $this->_fetchAll($sqlFetch,
            array(
                array("parameter"=>":fieldId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Get lisr of multiple options of a question (for checkbox or radio button for example)
     * @params  parent table id (int) and table name of the  list of option (string)
     * @returns array of options
     * */
    function getQuestionSubOptionsDetails($parentTableId, $tableName) {
        return $this->_fetchAll(str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_QUESTION_SUB_OPTIONS),
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * returns the total number of multiple options for a specific question
     * @params  ID of the parent table (ID), table name (string)
     * @return  total of options (array)
     * */
    function getQuestionTotalSubOptions($parentTableId, $tableName) {
        return $this->_fetch(str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_QUESTION_TOTAL_SUB_OPTIONS),
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * returns the total number of multiple options for a specific question
     * @params  ID of the parent table (ID), table name (string)
     * @return  total of options (array)
     * */
    function getTemplateQuestionTotalSubOptions($parentTableId, $tableName) {
        return $this->_fetch(str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_TOTAL_SUB_OPTIONS),
            array(
                array("parameter"=>":parentTableId","variable"=>$parentTableId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function marks all records for a specific text as deleted in the dictionary.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY after after verifications. Not following the proper procedure will have some serious impact
     * on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param   contentId (integer)
     * @return  result of deletion (boolean)
     * */
    public function markAsDeletedInDictionary($contentId) {
        return $this->_execute(SQL_QUESTIONNAIRE_MARK_DICTIONARY_RECORD_AS_DELETED, array(
            array("parameter"=>":username","variable"=>$this->username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":contentId","variable"=>$contentId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
        ));

    }

    /*
     * This function marks a specific record in a specific table as deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param   Table name (string)
     *          record to mark as deleted in the table (BIGINT)
     * @return  result of deletion (boolean)
     * */
    function markAsDeleted($tableName, $recordId) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName),SQL_QUESTIONNAIRE_MARK_RECORD_AS_DELETED);
        return $this->_execute($sql, array(
            array("parameter"=>":username","variable"=>$this->username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * This function marks a specific record in a specific table as deleted when there is no user to check.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param   Table name (string)
     *          record to mark as deleted in the table (BIGINT)
     * @return  result of deletion (boolean)
     * */
    function markAsDeletedNoUSer($tableName, $recordId) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName),SQL_QUESTIONNAIRE_MARK_RECORD_AS_DELETED_NO_USER);
        return $this->_execute($sql, array(
            array("parameter"=>":username","variable"=>$this->username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Returns the list of all questionnaires an user can access
     * @params  void
     * @return  list of questionnaires (array)
     * */
    function fetchAllQuestionnaires() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONNAIRES,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * Returns the list of all finalized questionnaires an user can access
     * @params  void
     * @return  list of finalized questionnaires (array)
     * */
    function fetchAllFinalQuestionnaires() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_FETCH_ALL_FINAL_QUESTIONNAIRES,
            array(
                array("parameter"=>":OAUserId","variable"=>$this->OAUserId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function replace the regular insert by a special one for the dictionary. To avoid duplicates in the contentId
     * that may cause a cardinality violation and break down the questionnaire and thus all the system, we have to
     * add a condition to the insert.
     * */
    protected function _insertMultipleRecordsIntoDictionary($records, $controlContentId) {
        $sqlInsert = str_replace("%%TABLENAME%%", DICTIONARY_TABLE, SQL_GENERAL_REPLACE_INTERSECTION_TABLE);
        $sqlConditional = array();
        $multiples = array();
        $cpt = 0;
        $ready = array();
        foreach ($records as $data) {
            $cpt++;
            $fields = array();
            $params = array();
            foreach($data as $key=>$value) {
                array_push($fields, $key);
                array_push($params, ":".$key.$cpt);
                array_push($ready, array("parameter"=>":".$key.$cpt,"variable"=>$value));
            }
            array_push($sqlConditional, str_replace("%%VALUES%%", implode(", ", $params), SQL_QUESTIONNAIRE_CONDITIONAL_INSERT));
            $sqlFieldNames = "`".implode("`, `", $fields)."`";
            array_push($multiples, implode(", ", $params));
        }
        array_push($ready, array("parameter"=>":controlContentId","variable"=>$controlContentId));

        $sqlInsert = str_replace("%%FIELDS%%", $sqlFieldNames, $sqlInsert) . implode(" UNION ALL ", $sqlConditional);
        return $this->_queryInsertReplace($sqlInsert, $ready);
    }

    /*
     * Returns questionnaire info (including answers) from a questionnaire
     * @params  int : $patientQuestionnaireSer - serial number of the particular questionnaire-patient relation 
     * @return  questionnaire results (array)
     * */
    function getQuestionnaireResults($patientQuestionnaireSer, $language) {
        return $this->_fetchAllStoredProcedure(SQL_QUESTIONNAIRE_GET_QUESTIONNAIRE_INFO, array(
            array("parameter"=>":pqser","variable"=>$patientQuestionnaireSer,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":language","variable"=>$language,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * Returns questionnaire info (including answers) from a questionnaire
     * @params  int : $questionnaireId - id of the particular questionnaire 
     * @params  int : $patientSerNum - serial of the patient 
     * @return  questionnaire details (array)
     * */
    function getLastAnsweredQuestionnaire($questionnaireId, $patientSerNum) {
        return $this->_fetchAllStoredProcedure(SQL_QUESTIONNAIRE_GET_PREV_QUESTIONNAIRE, array(
            array("parameter"=>":questionnaireid","variable"=>$questionnaireId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":ptser","variable"=>$patientSerNum,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * List all available purposes.
     * @params  void
     * @return array - list of purposes
     * */
    function getPurposes() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_PURPOSES, array());
    }

    /*
     * List all available respondents.
     * @params  void
     * @return array - list of respondents
     * */
    function getRespondents() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_RESPONDENTS, array());
    }

    /*
     * Get a purpose details
     * @params  $id - int : purpose ID
     * @return  array - details of the purpose
     * */
    function getPurposeDetails($id) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_PURPOSE_DETAILS, array(
            array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get a respondent details
     * @params  $id - int : respondent ID
     * @return  array - details of the respondent
     * */
    function getRespondentDetails($id) {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_RESPONDENT_DETAILS, array(
            array("parameter"=>":ID","variable"=>$id,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * Get the list of questionnaires associated to research and patient
     * */
    function getResearchPatient() {
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_RESEARCH_PATIENT, array(
        ));
    }

    /**
     *  Get the title of a consent form questionnaire given the consentQuestionnaireId
     */
    function getStudyConsentFormTitle($consentId){
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_CONSENT_FORM_TITLE, array(
            array("parameter"=>":consentId","variable"=>$consentId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /**
     * Get the list of questionnaire found based on an array of IDs
     * @param $list - list of questionnaire ID to verify
     * @return array - list of the questionnaires found
     */
    function getQuestionnairesListByIds($list) {
        $sql = str_replace("%%LISTIDS%%", implode(", ", $list), SQL_QUESTIONNAIRE_GET_QUESTIONNAIRES_BY_ID);
        return $this->_fetchAll($sql, array());
    }

    /**
     * Get the list of questionnaires consent form
     * @return array - results
     */
    function getConsentForms(){
        return $this->_fetchAll(SQL_QUESTIONNAIRE_GET_CONSENT_FORMS, array(
        ));
    }

    /**
     * Get the list of questionnaires status, visualization form, and completion date for a specific patient on a site
     * @param $mrn - patient identification
     * @param $site - code of the site
     * @return array - results found
     */
    function getQuestionnaireListOrms($mrn, $site){
        return $this->_fetchAll(SQL_GET_QUESTIONNAIRE_LIST_ORMS, array(
            array("parameter"=>":Hospital_Identifier_Type_Code","variable"=>$site,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":MRN","variable"=>$mrn,"data_type"=>PDO::PARAM_STR),
        ));
    }
}