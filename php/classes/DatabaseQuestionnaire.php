<?php
/**
 * Created by PhpStorm.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:45 AM
 */

class DatabaseQuestionnaire extends DatabaseAccess
{

    /* This function add french and english entries to the dictionary. First, the contentId is calulated since it should
     * be the same for both french and english entries. Then the entries are created.
     * Entry:   frenchText(String) and englishText(String)
     * Return:  contentID of matching both entries
     */
    function addToDictionary($frenchText, $englishText, $tableId = "-1") {
        try {
            $stmt = $this->connection->prepare("SELECT COALESCE(MAX(contentId) + 1, 1) AS nextContentId FROM dictionary;");
            $stmt->execute();
            $newValue = $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $newValue = $stmt->fetchAll();
            $newValue = $newValue[0]["nextContentId"];
        }
        catch(PDOException $e) {
            echo "Query to dictionary failed.\r\nError : " . $e->getMessage();
            die();
        }

        $sanitizedFrench = str_replace("'", "\'", $frenchText);
        $sanitizedEnglish = str_replace("'", "\'", $englishText);

        if ($sanitizedFrench == "") $sanitizedFrench = "FR_";
        if ($sanitizedEnglish == "") $sanitizedEnglish = "EN_";

        $toInsert = array(
            "tableId"=>$tableId,
            "languageId"=>FRENCH_LANGUAGE,
            "contentId"=>$newValue,
            "content"=>$sanitizedFrench,
            "createdBy"=>DEFAULT_NAME,
            "updatedBy"=>DEFAULT_NAME,

        );
        $this->insertTableLine(DICTIONARY_TABLE, $toInsert);

        $toInsert = array(
            "tableId"=>$tableId,
            "languageId"=>ENGLISH_LANGUAGE,
            "contentId"=>$newValue,
            "content"=>$sanitizedEnglish,
            "createdBy"=>DEFAULT_NAME,
            "updatedBy"=>DEFAULT_NAME,
        );
        $this->insertTableLine(DICTIONARY_TABLE, $toInsert);
        return $newValue;
    }

    /*
     * This function looks into the definition table of the questionnaire and returns the ID of the requested table
     * @param   string of a table name
     * @return  its table ID
     * */
    function getTableId($tableName) {
        $tableId = $this->fetch("SELECT ID FROM ".DEFINITION_TABLE." WHERE name = '".$tableName."'");
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
        $subTableName = strip_tags($subTableName);
        $subSql = str_replace("%%SUBTABLENAME%%", $subTableName, SQL_QUESTIONNAIRE_GET_QUESTION_TYPE_OPTIONS);
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
    function getLastTimeTableUpdated($tableName, $idFromTable)
    {
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
     *          name of the persion who updated the record (string)
     * @return  total of modification made: should be 0 or 1 (array)
     * */
    function canRecordBeUpdated($tableName, $tableId, $lastUpdated, $updatedBy) {
        $sql = str_replace("%%TABLENAME%%", $tableName, SQL_QUESTIONNAIRE_CAN_RECORD_BE_UPDATED);
        return $this->fetch($sql, array(
            array("parameter"=>":tableId","variable"=>$tableId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":lastUpdated","variable"=>$lastUpdated,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":updatedBy","variable"=>$updatedBy,"data_type"=>PDO::PARAM_STR),
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
        $sql = str_replace("%%TABLENAME%%", $tableName,SQL_QUESTIONNAIRE_MARK_RECORD_AS_DELETED);
        return $this->execute($sql, array(
            array("parameter"=>":username","variable"=>$this->username,"data_type"=>PDO::PARAM_STR),
            array("parameter"=>":recordId","variable"=>$recordId,"data_type"=>PDO::PARAM_INT),
            array("parameter"=>":userId","variable"=>$this->userId,"data_type"=>PDO::PARAM_INT),
        ));
    }
}