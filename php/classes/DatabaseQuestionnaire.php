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
        if (!$this->isUserSetUp) {
            echo "Fetching Questions Types failed.\r\nNo User ID specified.";
            die();
        }
        $sql = "SELECT
        tt.ID AS serNum, t.ID as typeSerNum,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
        tt.private,
        (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
        (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR,
        tts.minValue,
        tts.maxValue,
        tts.increment,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS minCaption_EN,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS minCaption_FR,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS maxCaption_EN,
        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS maxCaption_FR,
        dt1.name AS tableName,
        dt2.name AS subTableName,
        tt.OAUserId AS created_by
        FROM typeTemplate tt
        LEFT JOIN type t ON t.ID = tt.typeId
        LEFT JOIN definitionTable dt1 ON dt1.ID = t.templateTableId
        LEFT JOIN definitionTable dt2 ON dt2.ID = t.templateSubTableId
        LEFT JOIN typeTemplateSlider tts ON tts.typeTemplateId = tt.ID
        WHERE tt.private = 0 OR tt.OAUserId = :userId;";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Fetching Questions Types failed.\r\nError : " . $e->getMessage();
            die();
        }
    }

    /*
     * This function lists all the options of a specific question types from its table.
     * @param   ID of the question type, name of the table options
     * @return  all the options available for the specified question type
     * */
    function getQuestionTypesOptions($tableId, $subTableName) {
        $subTableName = strip_tags($subTableName);
        $subSql = "SELECT st.*,
        (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
        (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR
        FROM ".$subTableName." st WHERE parentTableId = :subTableId ORDER BY st.order;";

        try {
            $stmt = $this->connection->prepare($subSql);
            $stmt->bindParam(':subTableId', $tableId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Fetching options of the question type failed.\r\nError : " . $e->getMessage();
            die();
        }
    }
}