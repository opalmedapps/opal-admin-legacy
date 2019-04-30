<?php

/**
 *
 * Questionnaire-Question class
 */
class Question {

    protected $questionnaireDB;
    protected $opalDB;
    protected $userInfo;

    public function __construct($userId = "-1") {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD
        );
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD
        );

        $this->setUserInfo($userId);
    }

    /* this function sets the user info and the database access */
    protected function setUserInfo($userId) {
        $this->userInfo = $this->opalDB->getUserInfo($userId);
        $this->opalDB->setUserId($this->userInfo["userId"]);
        $this->opalDB->setUsername($this->userInfo["username"]);
        $this->questionnaireDB->setUserId($this->userInfo["userId"]);
        $this->questionnaireDB->setUsername($this->userInfo["username"]);
    }

    /**
     *
     * Inserts a question into our database
     *
     * @param   array $questionDetails, array containing all the questions details
     * @return  ID of the new question
     */
    public function insertQuestion($questionDetails){
        $textEn = strip_tags($questionDetails['text_EN']);
        $textFr = strip_tags($questionDetails['text_FR']);
        $questionTypeId = strip_tags($questionDetails['questiontype_ID']);

        $libraries = array();
        if (count($questionDetails['libraries']) > 0)
            foreach($questionDetails['libraries'] as $library)
                array_push($libraries, strip_tags($library));

        $private = strip_tags($questionDetails['private']);
        if ($private == "1")
            $private = true;
        else
            $private = false;

        $validQuestionType = $this->questionnaireDB->getTypeTemplate($questionTypeId);
        if(!$validQuestionType)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetching question type error.");

        if(count($libraries) > 0) {
            $librariesToAdd = $this->questionnaireDB->getLibraries($libraries);
            if(count($librariesToAdd) <= 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetching library error.");
        }

        $toInsert = array(FRENCH_LANGUAGE=>$textFr, ENGLISH_LANGUAGE=>$textEn);
        $contentId = $this->questionnaireDB->addToDictionary($toInsert, QUESTION_TABLE);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $displayId = $this->questionnaireDB->addToDictionary($toInsert, QUESTION_TABLE);
        $definitionId = $this->questionnaireDB->addToDictionary($toInsert, QUESTION_TABLE);

        $legacyTypeId = $this->questionnaireDB->getLegacyType($validQuestionType["typeId"]);
        $legacyTypeId = $legacyTypeId["ID"];

        $toInsert = array(
            "question"=>$contentId,
            "typeId"=>$validQuestionType["typeId"],
            "display"=>$displayId,
            "definition"=>$definitionId,
            "private"=>$private,
            "legacyTypeId"=>$legacyTypeId,
        );

        $questionId = $this->questionnaireDB->insertQuestion($toInsert);

        if(count($librariesToAdd) > 0) {
            $multipleInserts = array();
            foreach($librariesToAdd as $lib) {
                array_push($multipleInserts, array("libraryId"=>$lib["ID"], "questionId"=>$questionId));
            }
            $this->questionnaireDB->insertMultipleLibrariesToQuestion($multipleInserts);
        }

        if ($validQuestionType["typeId"] == CHECKBOXES)
            $toInsert = array(
                "questionId"=>$questionId,
                "minAnswer"=>$validQuestionType["minAnswer"],
                "maxAnswer"=>$validQuestionType["maxAnswer"],
            );
        else if ($validQuestionType["typeId"] == RADIO_BUTTON)
            $toInsert = array(
                "questionId"=>$questionId,
            );
        else if ($validQuestionType["typeId"] == SLIDERS) {
            $newMinCaption = $this->questionnaireDB->copyToDictionary($validQuestionType["minCaption"], $validQuestionType["tableName"]);
            $newMaxCaption = $this->questionnaireDB->copyToDictionary($validQuestionType["maxCaption"], $validQuestionType["tableName"]);
            $toInsert = array(
                "questionId" => $questionId,
                "minValue" => $validQuestionType["minValue"],
                "maxValue" => $validQuestionType["maxValue"],
                "minCaption" => $newMinCaption,
                "maxCaption" => $newMaxCaption,
                "increment" => $validQuestionType["increment"],
            );
        }
        else
            $toInsert = array(
                "questionId"=>$questionId,
            );

        $questionOptionId = $this->questionnaireDB->insertQuestionOptions($validQuestionType["tableName"], $toInsert);

        $recordsToInsert = array();
        if ($validQuestionType["subTableName"] == CHECK_BOX_OPTION_TABLE) {
            if(count($validQuestionType["options"]) <= 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Checkbox option error.");

            foreach ($validQuestionType["options"] as $row) {
                $newDescription = $this->questionnaireDB->copyToDictionary($row["description"], $validQuestionType["subTableName"]);
                array_push($recordsToInsert, array(
                    "parentTableId"=>$questionOptionId,
                    "description"=>$newDescription,
                    "order"=>$row["order"],
                    "specialAction"=>$row["specialAction"],
                ));
            }
            $this->questionnaireDB->insertCheckboxOption($recordsToInsert);
        }
        else if ($validQuestionType["subTableName"] == RADIO_BUTTON_OPTION_TABLE) {
            if(count($validQuestionType["options"]) <= 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Radio Button option error.");

            foreach ($validQuestionType["options"] as $row) {
                $newDescription = $this->questionnaireDB->copyToDictionary($row["description"], $validQuestionType["subTableName"]);
                array_push($recordsToInsert, array(
                    "parentTableId"=>$questionOptionId,
                    "description"=>$newDescription,
                    "order"=>$row["order"],
                ));
            }
            $this->questionnaireDB->insertRadioButtonOption($recordsToInsert);
        }
    }

    /**
     * Gets a list of existing questions. For each question, it will list the libraries it belongs too (if any) and
     * if the question is locked (e.a. if the question was already sent to a patient).
     *
     * @return array $questions : the list of existing questions
     */
    public function getQuestions(){
        $questions = array();
        $questionsLists = $this->questionnaireDB->fetchAllQuestions();
        foreach ($questionsLists as $row){
            $libraries = $this->questionnaireDB->fetchLibrariesQuestion($row["ID"]);
            $libNameEn = array();
            $libNameFr = array();
            foreach($libraries as $library) {
                array_push($libNameEn, $library["text_EN"]);
                array_push($libNameFr, $library["text_FR"]);
            }

            $libNameEn = implode(", ", $libNameEn);
            $libNameFr = implode(", ", $libNameFr);

            if ($libNameEn == "") $libNameEn = "None";
            if ($libNameFr == "") $libNameFr = "Aucune";
            $questionLocked = $this->isQuestionLocked($row["ID"]);

            $questionArray = array (
                'serNum'				=> $row["ID"],
                'text_EN'				=> strip_tags($row["text_EN"]),
                'text_FR'				=> strip_tags($row["text_FR"]),
                'private'				=> $row["private"],
                'answertype_serNum'		=> $row["answertype_Id"],
                'answertype_name_EN'	=> $row["answertype_name_EN"],
                'answertype_name_FR'	=> $row["answertype_name_FR"],
                'library_name_EN'		=> $libNameEn,
                'library_name_FR'		=> $libNameFr,
                'final'         		=> $row["final"],
                'locked'        		=> $questionLocked,
            );
            array_push($questions, $questionArray);
        }
        return $questions;
    }

    function isQuestionLocked($questionId) {
        $questionnairesList = array();
        $questionnaires = $this->questionnaireDB->fetchQuestionnairesIdQuestion($questionId);

        foreach ($questionnaires as $questionnaire) {
            array_push($questionnairesList, $questionnaire["ID"]);
        }

        $questionLocked = 0;
        if (count($questionnairesList) > 0) {
            $questionnairesList = implode(", ", $questionnairesList);
            $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnairesList);
            $questionLocked = intval($questionLocked["total"]);
        }
        return $questionLocked;
    }

    /**
     *
     * Gets question details
     *
     * @param   question ID (int)
     * @return  array $questionDetails : the question details
     */
    public function getQuestionDetails($questionId) {
        $question = $this->questionnaireDB->getQuestionDetails($questionId);
        if(count($question) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot get question details.");

        $question = $question[0];
        $question["locked"] = $this->isQuestionLocked($questionId);

        $userAuthorizations = array();
        $userRole = $this->opalDB->getUserRole($this->opalDB->getUserId());
        if (count($userRole) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User cannot be found. Access denied.");

        foreach($userRole as $role)
            array_push($userAuthorizations, $role["RoleSerNum"]);

        $readOnly = false;
        if ($question["locked"])
            $readOnly = true;
        else if($question["final"]) {
            $readOnly = true;
            foreach($userAuthorizations as $access) {
                if (in_array($access, HelpSetup::AUTHORIZATION_MODIFICATION_FINALIZED)) {
                    $readOnly = false;
                    break;
                }
            }
        }

        if($question["typeId"] == SLIDERS)
                $options = $this->questionnaireDB->getQuestionSliderDetails($question["ID"], $question["tableName"]);
            else
                $options = $this->questionnaireDB->getQuestionOptionsDetails($question["ID"], $question["tableName"]);
        if (count($options) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question. Too many options.");

        $options = $options[0];

        $subOptions = null;
        if($question["subTableName"] != "" && $options["ID"] != "") {
            $subOptions = $this->questionnaireDB->getQuestionSubOptionsDetails($options["ID"], $question["subTableName"]);
        }

        $libraries = $this->questionnaireDB->fetchLibrariesQuestion($question["ID"]);

        $arrLib = array();
        foreach ($libraries as $lib) {
            array_push($arrLib, $lib["ID"]);
        }

        $librariesList = $this->questionnaireDB->fetchAllLibraries();

        unset($question["tableName"]);
        unset($question["subTableName"]);
        $question["options"] = $options;
        $question["subOptions"] = $subOptions;
        $question["readOnly"] = strval(intval($readOnly));
        $question["libSelected"] = $arrLib;

        foreach($librariesList as &$lib) {
            if (in_array($lib["serNum"], $arrLib))
                $lib["checked"] = 1;
            else
                $lib["checked"] = 0;
        }
        $question["libraries"] = $librariesList;

        return $question;
    }

    /**
     *
     * Updates a question
     *
     * @param array $questionDetails  : the question details
     * @return array $response : response
     */
    public function updateQuestion($questionDetails) {

        $serNum 				= $questionDetails['serNum'];
        $text_EN 				= $questionDetails['text_EN'];
        $text_FR 				= $questionDetails['text_FR'];
        $answertype_serNum 		= $questionDetails['answertype_serNum'];
        $questiongroup_serNum 	= $questionDetails['questiongroup_serNum'];
        $last_updated_by 		= $questionDetails['last_updated_by'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				UPDATE
					QuestionnaireQuestion
				SET
					QuestionnaireQuestion.text_EN = \"$text_EN\",
					QuestionnaireQuestion.text_FR = \"$text_FR\",
					QuestionnaireQuestion.answertype_serNum = '$answertype_serNum',
					QuestionnaireQuestion.questiongroup_serNum = '$questiongroup_serNum',
					QuestionnaireQuestion.last_updated_by = '$last_updated_by'
				WHERE
					QuestionnaireQuestion.serNum = $serNum
			";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1; // Success
            return $response;

        } catch (PDOException $e) {
            $response['message'] = $e->getMessage();
            return $response;
        }
    }

    /**
     * Mark a question as deleted. First, it get the last time it was updated, and check if the question was already
     * sent to a patient. Then it checked if the record was updated in the meantime, and if not, it marks the question
     * as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionId (ID of the question), $userId (ID of the user who requested the deletion)
     * @return array $response : response
     */
    public function deleteQuestion($questionId) {
        if ($this->userInfo["userId"] == -1) {
            $response['value'] = false; // User is not identified.
            $response['message'] = 401;
            return $response;
        }

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTION_TABLE, $questionId);
        $questionnaires = $this->questionnaireDB->fetchQuestionnairesIdQuestion($questionId);
        $questionnairesList = array();
        foreach ($questionnaires as $questionnaire) {
            array_push($questionnairesList, $questionnaire["ID"]);
        }

        $wasQuestionSent = false;
        if (count($questionnairesList) > 0) {
            $wasQuestionSent = $this->opalDB->countLockedQuestionnaires(implode(", ", $questionnairesList));
            $wasQuestionSent = intval($wasQuestionSent["total"]);
        }

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTION_TABLE, $questionId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $nobodyUpdated = intval($nobodyUpdated["total"]);

        if ($nobodyUpdated && !$wasQuestionSent){
            $this->questionnaireDB->markAsDeleted(QUESTION_TABLE, $questionId);
            $response['value'] = true; // Success
            $response['message'] = 200;
            return $response;
        }
        else if (!$nobodyUpdated) {
            $response['value'] = false; // conflict error. Somebody already updated the question or record does not exists.
            $response['message'] = 409;
            return $response;
        } else {
            $response['value'] = false; // Question locked.
            $response['message'] = 423;
            return $response;
        }
    }
}
?>