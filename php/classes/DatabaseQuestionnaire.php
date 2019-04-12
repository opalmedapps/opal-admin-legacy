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
     * @param   nothing
     * @return  next content ID
     * */
    protected function getNextContentId() {
        $nextContentId = $this->fetch(SQL_QUESTIONNAIRE_GET_DICTIONARY_NEXT_CONTENT_ID);
        return $nextContentId["nextContentId"];
    }

    /*
     * This function returns an array that contains
     * @param   nothing
     * @return  array of languages
     * */
    function getLanguageTable() {
        return $this->fetchAll(SQL_QUESTIONNAIRE_GET_ALL_LANGUAGE);
    }

    /* This function add entries to the dictionary. First, the contentId is calculated since it should be the same for
     * all languages. Then the entries are created in the dictionary. If there is less entries than the number of
     * languages, empty entries will be created with the mention XX_ in the content where XX is the iso-lang code in
     * upper case.
     * Entry:   associative array of language id and its content.
     *          example: array(1=>"exemple", "2"=>"example")
     * Return:  new contentID of matching all entries.
     */
    function addToDictionary($newEntries, $tableName) {
        $tableId = $this->getTableId($tableName);
        $languageTable = $this->getLanguageTable();
        $toInsert = array();
        $contentId = $this->getNextContentId();

        foreach($languageTable as $lang) {
            $toInsert[$lang["ID"]] = array(
                "languageId"=>$lang["ID"],
                "content"=>strtoupper($lang["isoLang"]."_"),
                "contentId"=>$contentId,
                "tableId"=>$tableId,
                "createdBy"=>$this->username,
                "updatedBy"=>$this->username,
            );
        }
        foreach($newEntries as $key=>$value) {
            $toInsert[$key]["content"] = $value;
        }
        $this->insertMultipleRecordsIntoTable(DICTIONARY_TABLE, $toInsert);
        return $contentId;
    }

    /*
     * This function looks into the definition table of the questionnaire and returns the ID of the requested table
     * @param   string of a table name
     * @return  its table ID
     * */
    function getTableId($tableName) {
        $tableId = $this->fetch(SQL_QUESTIONNAIRE_GET_DEFINITION_TABLE_ID,
            array(
                array("parameter"=>":tableName","variable"=>$tableName,"data_type"=>PDO::PARAM_STR),
            ));
        return $tableId["ID"];
    }

    /*
     * This fucntion lists all the questions types a specific user can have access.
     * @param   none
     * @return  array of question types
     * */
    function getQuestionTypes() {
        return $this->fetchAll(SQL_QUESTIONNAIRE_GET_QUESTION_TYPES,
            array(
                array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function lists all the options of a specific question types from its table.
     * @param   ID of the question type, name of the table options
     * @return  all the options available for the specified question type
     * */
    function getQuestionTypesOptions($tableId, $subTableName) {
        $subSql = str_replace("%%SUBTABLENAME%%", strip_tags($subTableName), SQL_QUESTIONNAIRE_GET_QUESTION_TYPE_OPTIONS);
        return $this->fetchAll($subSql, array(
            array("parameter"=>":subTableId","variable"=>$tableId,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * This function returns all the general categories types of question (slider, checkbox, etc)
     * @param   Nothing
     * @return  array of types
     * */
    function getQuestionTypeCategories() {
        return $this->fetchAll(SQL_QUESTIONNAIRE_GET_QUESTION_TYPES_CATEGORIES);
    }

    /*
     * This function lists all the questions to be displayed in a listing
     * @param   none
     * @return  array of questions
     * */
    function fetchAllQuestions() {
        return $this->fetchAll(SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONS,
            array(
                array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function fetch all the libraries to which a question is attached if the user is authorized to list
     * @param   Id of a question (int)
     * @return  list of libraries (array)
     * */
    function fetchLibrariesQuestion($questionId) {
        return $this->fetchAll(SQL_QUESTIONNAIRE_FETCH_LIBRARIES_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function fetch all the questionnaires ID to which a question is attached if the user is authorized to list
     * @param   ID of a question (int)
     * @return  list of questionnaires (array)
     * */
    function fetchQuestionnairesIdQuestion($questionId) {
        return $this->fetchAll(SQL_QUESTIONNAIRE_FETCH_QUESTIONNAIRES_ID_QUESTION,
            array(
                array("parameter"=>":questionId","variable"=>$questionId,"data_type"=>PDO::PARAM_INT),
                array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
            ));
    }

    /*
     * This function returns the last time a specific table was updated.
     * @param   string of the table name
     * @return  array of last time it was updated.
     * */
    function getLastTimeTableUpdated($tableName, $idFromTable) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_QUESTIONNAIRE_GET_LAST_TIME_TABLE_UPDATED);
        return $this->fetch($sql, array(
            array("parameter"=>":ID","variable"=>$idFromTable,"data_type"=>PDO::PARAM_INT),
        ));
    }

    /*
     * This function look if a specific record was updated since a specific time before another operation of
     * modification can occur. It is done to prevent two users who tried to update the same record on the same table
     * on the same time to erase the modification, based on a FIFO priority.
     * @param   Table name to look at (string)
     *          ID of the table (BIGINT)
     *          last time the record was updated (string)
     *          name of the person who updated the record (string)
     * @return  total of modification made: should be 0 or 1 (array)
     * */
    function canRecordBeUpdated($tableName, $tableId, $lastUpdated, $updatedBy) {
        $sql = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_QUESTIONNAIRE_CAN_RECORD_BE_UPDATED);
        return $this->fetch($sql, array(
            array("parameter"=>":tableId","variable"=>$tableId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":lastUpdated","variable"=>$lastUpdated,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$updatedBy,"data_type"=>PDO::PARAM_STR),
        ));
    }

    /*
     * This function add a new question type to the type template table of the questionnaire DB.
     * @param
     * */
    function addQuestionType($newQuestionType) {
        $this->execute(DEACTIVATE_FOREIGN_KEY_CONSTRAINT);
        $newQuestionType["createdBy"] = $this->username;
        $newQuestionType["updatedBy"] = $this->username;
        $result = $this->insertRecordIntoTable(TYPE_TEMPLATE_TABLE, $newQuestionType);
        $this->execute(ACTIVATE_FOREIGN_KEY_CONSTRAINT);
        return $result;
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
        return $this->execute($sql, array(
            array("parameter"=>":username","variable"=>$this->username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
        ));
    }
}