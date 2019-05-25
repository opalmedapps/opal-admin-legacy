<?php
/**
 *
 * Questionnaire class
 */
class Questionnaire extends QuestionnaireModule {


    /*
     * This function returns a list of questionnaire an user can access
     * @param   void
     * @return  list of questionnaires (array)
     * */
    public function getQuestionnaires(){
        $results = $this->questionnaireDB->fetchAllQuestionnaires();
        foreach($results as &$questionnaire) {
            $questionnaire["locked"] = $this->isQuestionnaireLocked($questionnaire["ID"]);
        }
        return $results;
    }

    /*
     * This function validate and sanitize a questionnaire
     * @param   $questionnaireToSanitize (array)
     * @return  sanitized questionnaire or false if invalid
     * */
    function validateAndSanitize($questionnaireToSanitize) {
        //Sanitize the name of the questionnaire. If it is empty, rejects it.
        $validatedQuestionnaire = array(
            "text_EN"=>htmlspecialchars($questionnaireToSanitize['text_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "text_FR"=>htmlspecialchars($questionnaireToSanitize['text_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        );

        if ($validatedQuestionnaire["text_EN"] == "" || $validatedQuestionnaire["text_FR"] == "")
            return false;

        //Sanitize the questionnaire ID (if any). IF there was supposed to be an ID and it's empty, reject the
        //questionnaire
        if($questionnaireToSanitize["ID"] != "") {
            $validatedQuestionnaire["ID"] = strip_tags($questionnaireToSanitize["ID"]);
            if($validatedQuestionnaire["ID"] == "")
                return false;
        }

        //Sanitize the list of libraries associated to the questionnaire
        $libraries = array();
        if (count($questionnaireToSanitize['libraries']) > 0)
            foreach($questionnaireToSanitize['libraries'] as $library)
                array_push($libraries, strip_tags($library));

        $validatedQuestionnaire["libraries"] = $libraries;
        $validatedQuestionnaire["private"] = (strip_tags($questionnaireToSanitize['private'])=="true"||strip_tags($questionnaireToSanitize['private'])=="1"?"1":"0");
        $validatedQuestionnaire["final"] = (strip_tags($questionnaireToSanitize['final'])=="true"||strip_tags($questionnaireToSanitize['final'])=="1"?"1":"0");

        //Validate the list of questions
        $options = array();
        $arrIds = array();
        if(!empty($questionnaireToSanitize["questions"]))
            foreach($questionnaireToSanitize["questions"] as $question) {
                $order = intval(strip_tags($question["order"]));
                $questionId = intval(strip_tags($question["ID"]));
                if ($questionId != 0)
                    array_push($arrIds, $questionId);
                $optional = intval(strip_tags($question["optional"]));

                if($order <= 0 || $questionId <= 0) {
                    return false;
                    break;
                }

                array_push($options, array("order"=>$order,"questionId"=>$questionId,"optional"=>$optional));
            }
        else
            return false;

        //If the number of questions found in the DB is not equal to the number of questions in the questionnaire,
        //reject it
        $questionsInfo = $this->questionnaireDB->fetchQuestionsByIds($arrIds);
        if(count($questionsInfo) != count($options))
            return false;

        //If any question is private, mark down the questionnaire should be private
        $anyPrivateQuestion = false;
        foreach($questionsInfo as $question) {
            if (intval(strip_tags($question["private"])) == 1) $anyPrivateQuestion = true;
        }

        //If the questionnaire has being set to be public with at least one private question, rejects it
        if ($anyPrivateQuestion && $validatedQuestionnaire["private"] != "1")
            return false;

        //sort, reassign the order of questions and returned the sanitized questionnaire
        self::sortOptions($options);
        $validatedQuestionnaire["questions"] = $options;
        return $validatedQuestionnaire;
    }

    /*
     * Check if a specific questionnaire has being sent already
     * @param   $questionnaireId (integer)
     * @return  true or false (boolean)
     * */
    function isQuestionnaireLocked($questionnaireId) {
        $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnaireId);
        $questionLocked = intval($questionLocked["total"]);
        return $questionLocked;
    }

    /*
     * Gets questionnaire details
     *
     * @param integer $questionnaireId : the questionnaire ID
     * @return array $questionnaireDetails : the questionnaire details
     */
    public function getQuestionnaireDetails($questionnaireId){
        $questionnaireDetails = $this->questionnaireDB->getQuestionnaireDetails($questionnaireId);

        if(count($questionnaireDetails) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");

        $questionnaireDetails = $questionnaireDetails[0];
        $questionnaireDetails["text_EN"] = strip_tags(htmlspecialchars_decode($questionnaireDetails["text_EN"]));
        $questionnaireDetails["text_FR"] = strip_tags(htmlspecialchars_decode($questionnaireDetails["text_FR"]));
        $questionnaireDetails["locked"] = intval($this->isQuestionnaireLocked($questionnaireId));

        $readOnly = false;
        $isOwner = false;
        if($this->questionnaireDB->getOAUserId() == $questionnaireDetails["OAUserId"])
            $isOwner = true;
        if ($questionnaireDetails["locked"])
            $readOnly = true;

        $questionnaireDetails["readOnly"] = intval($readOnly);
        $questionnaireDetails["isOwner"] = intval($isOwner);
        $questionnaireDetails["final"] = intval($questionnaireDetails["final"]);

        $sectionDetails = $this->questionnaireDB->getSectionsByQuestionnaireId($questionnaireDetails["ID"]);
        $sectionDetails = $sectionDetails[0];
        $questionnaireDetails["sections"] = array($sectionDetails["ID"]);
        $questionnaireDetails["questions"] = $this->questionnaireDB->getQuestionsBySectionId($sectionDetails["ID"]);
        foreach($questionnaireDetails["questions"] as &$question) {
            $question["order"] = intval($question["order"]);
            $question["text_EN"] = strip_tags(htmlspecialchars_decode($question["text_EN"]));
            $question["text_FR"] = strip_tags(htmlspecialchars_decode($question["text_FR"]));

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
            $question["options"] = $options;
            $question["subOptions"] = $subOptions;
        }
        return $questionnaireDetails;
    }

    /*
     * Inserts a questionnaire into the questionnaire, section and questionSection tables.
     *
     * @param array $questionnaireDetails  : the questionnaire details
     * @return void
     */
    public function insertQuestionnaire($newQuestionnaire){
        $toInsert = array(FRENCH_LANGUAGE=>$newQuestionnaire['text_FR'], ENGLISH_LANGUAGE=>$newQuestionnaire['text_EN']);
        $title = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $nickname = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);
        $description = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);
        $instruction = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(
            "title"=>$title,
            "nickname"=>$nickname,
            "description"=>$description,
            "instruction"=>$instruction,
            "private"=>$newQuestionnaire["private"],
        );

        $questionnaireId = $this->questionnaireDB->insertQuestionnaire($toInsert);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $titleSection = $this->questionnaireDB->addToDictionary($toInsert, SECTION_TABLE);
        $instructionSection = $this->questionnaireDB->addToDictionary($toInsert, SECTION_TABLE);

        $toInsert = array(
            "questionnaireId"=>$questionnaireId,
            "title"=>$titleSection,
            "instruction"=>$instructionSection,
        );

        $sectionId = $this->questionnaireDB->insertSection($toInsert);
        foreach($newQuestionnaire["questions"] as &$question)
            $question["sectionId"] = $sectionId;

        $this->questionnaireDB->insertQuestionsIntoSection($newQuestionnaire["questions"]);
    }

    /**
     * Mark a questionnaire as deleted. First, it get the last time it was updated, check if the user has the proper
     * authorization, and check if the questionnaire was already sent to a patient. Then it checked if the record was
     * updated in the meantime, and if not, it marks the questionnaire as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionnaireId (ID of the questionnaire)
     * @return array $response : response
     */
    public function deleteQuestionnaire($questionnaireId){
        $questionToDelete = $this->questionnaireDB->getQuestionnaireDetails($questionnaireId);
        $questionToDelete = $questionToDelete[0];
        if ($this->questionnaireDB->getOAUserId() <= 0 || $questionToDelete["deleted"] == 1 || ($questionToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $questionToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTIONNAIRE_TABLE, $questionnaireId);

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTIONNAIRE_TABLE, $questionnaireId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $wasQuestionSent = $this->isQuestionnaireLocked($questionnaireId);

        if ($nobodyUpdated && !$wasQuestionSent) {
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["title"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["nickname"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["description"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["instruction"]);
            $this->questionnaireDB->markAsDeleted(QUESTIONNAIRE_TABLE, $questionnaireId);
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

    /*
     * This function updates a questionnaire. If the questionnaire has being sent already, it is considered as locked
     * and cannot be sent. First, it will remove any questions to the questionnaire, then it will add the ones
     * requested, and finally update the questions settings. Next, the dictionary will be updated with updated titles,
     * and finally the privacy and status (draft final) of the questionnaire will be updated.
     *
     * @params  $updatedQuestionnaire (array)
     * @return  void
     * */
    public function updateQuestionnaire($updatedQuestionnaire){
        $total = 0;
        $questionnaireUpdated = 0;
        $oldQuestions = array();
        $newQuestions = array();
        $updatedQuestions = array();
        $questionsToKeep = array();
        $questionCheckPrivacy = array();

        /*
         *  $this->questionnaireDB->fetchQuestionsByIds($arrIds)
         * */
        //Get current questionnaire infos
        $oldQuestionnaire = $this->getQuestionnaireDetails($updatedQuestionnaire["ID"]);

        //If the questionnaire is locked, ignore all the changes
        if($this->isQuestionnaireLocked($oldQuestionnaire["ID"])) return false;

        //Look for the current questions IDs associated to the questionnaire
        foreach($oldQuestionnaire["questions"] as $question)
            if(!in_array($question["ID"], $oldQuestions))
                array_push($oldQuestions, $question["ID"]);

        //Prepare the list of questions to keep, to update and to insert
        foreach($updatedQuestionnaire["questions"] as $question) {
            array_push($questionCheckPrivacy, $question["questionId"]);
            if (!in_array($question["questionId"], $questionsToKeep))
                array_push($questionsToKeep, $question["questionId"]);
            if (!in_array($question["questionId"], $oldQuestions) && !in_array($question["questionId"], $newQuestions)) {
                $tempQuestion = $question;
                $tempQuestion["sectionId"] = $oldQuestionnaire["sections"][0];
                array_push($newQuestions, $tempQuestion);
            }
            if (in_array($question["questionId"], $oldQuestions) && !in_array($question["questionId"], $updatedQuestions)) {
                $tempQuestion = $question;
                $tempQuestion["sectionId"] = $oldQuestionnaire["sections"][0];
                array_push($updatedQuestions, $tempQuestion);
            }
        }

        //Check if any questions are private. If it is, then the questionnaire must be private no matter what
        $anyPrivate = $this->questionnaireDB->countPrivateQuestions($questionCheckPrivacy);
        $anyPrivate = intval($anyPrivate["total"]);
        if($anyPrivate)
            $updatedQuestionnaire["private"] = true;

        //Delete questions from questionnaire if necessary
        $total += $this->questionnaireDB->deleteQuestionsFromSection($oldQuestionnaire["sections"][0], $questionsToKeep);

        //Insert new questions to the questionnaire if necessary
        if(!empty($newQuestions))
            $total += $this->questionnaireDB->insertQuestionsIntoSection($newQuestions);

        //Update questions settings to the questionnaire if necessary
        foreach($updatedQuestions as $question) {
            $tempQuestion = $question;
            unset($tempQuestion["sectionId"]);
            unset($tempQuestion["questionId"]);
            $total += $this->questionnaireDB->updateQuestionSection($question["sectionId"], $question["questionId"], $tempQuestion);
        }

        //Update the dictionary entry for the title if necessary
        $toUpdateDict = array(
            array(
                "content"=>$updatedQuestionnaire["text_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["title"],
            ),
            array(
                "content"=>$updatedQuestionnaire["text_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["title"],
            ),
        );
        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, QUESTIONNAIRE_TABLE);

        //Update privay and final fields if necessary
        $toUpdate = array(
            "ID"=>$oldQuestionnaire["ID"],
            "private"=>$updatedQuestionnaire["private"],
            "final"=>$updatedQuestionnaire["final"],
        );
        $questionnaireUpdated = $this->questionnaireDB->updateQuestionnaire($toUpdate);

        /*
         * If any modifications were made except to the questionnaire table, force the questionnaire entry to be updated
         * with the name and date anyway.
         * */
        if ($questionnaireUpdated == 0 && $total > 0)
            $this->questionnaireDB->forceUpdateQuestionnaire($updatedQuestionnaire["ID"]);
    }
}
?>