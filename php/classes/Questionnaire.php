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
        $this->checkReadAccess();
        $results = $this->questionnaireDB->fetchAllQuestionnaires();
        foreach($results as &$questionnaire) {
            $questionnaire["locked"] = $this->isQuestionnaireLocked($questionnaire["ID"]);
        }
        return $results;
    }

    /*
     * This function returns a list of questionnaire an user can access
     * @param   void
     * @return  list of questionnaires (array)
     * */
    public function getFinalizedQuestionnaires(){
        $this->checkReadAccess();
        $results = $this->questionnaireDB->fetchAllFinalQuestionnaires();
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
            "title_EN"=>htmlspecialchars($questionnaireToSanitize['title_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "title_FR"=>htmlspecialchars($questionnaireToSanitize['title_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "description_EN"=>htmlspecialchars($questionnaireToSanitize['description_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "description_FR"=>htmlspecialchars($questionnaireToSanitize['description_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        );

        if ($validatedQuestionnaire["title_EN"] == "" || $validatedQuestionnaire["title_FR"] == "" || $validatedQuestionnaire["description_EN"] == "" || $validatedQuestionnaire["description_FR"] == "")
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
        if (is_array($questionnaireToSanitize['libraries']) && count($questionnaireToSanitize['libraries']) > 0)
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
                $typeId = intval(strip_tags($question["typeId"]));

                if($order <= 0 || $questionId <= 0 || $typeId <= 0) {
                    return false;
                    break;
                }

                array_push($options, array("order"=>$order,"questionId"=>$questionId,"optional"=>$optional,"typeId"=>$typeId));
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
     * Check if a specific questionnaire has being published already
     * @param   $questionnaireId (integer)
     * @return  true or false (boolean)
     * */
    function isQuestionnaireLocked($questionnaireId) {
        $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnaireId);
        $questionLocked = (intval($questionLocked["total"]) > 0?true:false);
        return $questionLocked;
    }

    /*
     * Gets questionnaire details
     *
     * @param integer $questionnaireId : the questionnaire ID
     * @return array $questionnaireDetails : the questionnaire details
     */
    public function getQuestionnaireDetails($questionnaireId){
        $this->checkReadAccess($questionnaireId);
        $questionnaireDetails = $this->questionnaireDB->getQuestionnaireDetails($questionnaireId);

        if(count($questionnaireDetails) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");

        $questionnaireDetails = $questionnaireDetails[0];

        $questionnaireDetails["title_EN"] = htmlspecialchars_decode($questionnaireDetails["title_EN"]);
        $questionnaireDetails["title_FR"] = htmlspecialchars_decode($questionnaireDetails["title_FR"]);
        $questionnaireDetails["description_EN"] = htmlspecialchars_decode($questionnaireDetails["description_EN"]);
        $questionnaireDetails["description_FR"] = htmlspecialchars_decode($questionnaireDetails["description_FR"]);
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
            $question["question_EN"] = strip_tags(htmlspecialchars_decode($question["question_EN"]));
            $question["question_FR"] = strip_tags(htmlspecialchars_decode($question["question_FR"]));

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
        $this->checkWriteAccess($newQuestionnaire);
        $toInsert = array(FRENCH_LANGUAGE=>$newQuestionnaire['title_FR'], ENGLISH_LANGUAGE=>$newQuestionnaire['title_EN']);
        $title = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(FRENCH_LANGUAGE=>$newQuestionnaire['description_FR'], ENGLISH_LANGUAGE=>$newQuestionnaire['description_EN']);
        $description = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $nickname = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);
        $instruction = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        foreach($newQuestionnaire["questions"] as &$question)
            $question["sectionId"] = $sectionId;

        $toInsert = array(
            "title"=>$title,
            "nickname"=>$nickname,
            "description"=>$description,
            "instruction"=>$instruction,
            "private"=>$newQuestionnaire["private"],
        );

        /*
         * Because the ORMS system is unable to recognize what kind of format of visualization it must use for any new
         * questionnaire created (because an array was hardcoded manually with the questionnaire IDs on ORMS side which
         * is a VERY BAD IDEA), we have to change the visualization type here. This section of code should be changed
         * ASAP once the code will be properly on ORMS side (which means probably never)
         * */
        foreach($newQuestionnaire["questions"] as &$question) {
            if ($question["typeId"] == SLIDERS)
                $toInsert["visualization"] = 1;
        }

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
        foreach($newQuestionnaire["questions"] as &$question) {
            unset($question["typeId"]);
            $question["sectionId"] = $sectionId;
        }

        $this->questionnaireDB->insertQuestionsIntoSection($newQuestionnaire["questions"]);
    }

    /**
     *
     * Gets list logs of legacy questionnaires during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @return array : the legacy questionnaire logs for table view
     */
    public function getQuestionnaireListLogs($ids) {
        $this->checkReadAccess($ids);
        return $this->opalDB->getQuestionnaireListLogs($ids);
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
        $this->checkDeleteAccess($questionnaireId);
        $questionnaireToDelete = $this->getQuestionnaireDetails($questionnaireId);

        if ($this->questionnaireDB->getOAUserId() <= 0 || $questionnaireToDelete["deleted"] == 1 || ($questionnaireToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $questionnaireToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTIONNAIRE_TABLE, $questionnaireId);

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTIONNAIRE_TABLE, $questionnaireId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $wasQuestionSent = $this->isQuestionnaireLocked($questionnaireId);

        if ($nobodyUpdated && !$wasQuestionSent) {
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["title"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["nickname"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["description"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["instruction"]);

            foreach($questionnaireToDelete["sections"] as $section)
                $this->questionnaireDB->markAsDeletedNoUSer(SECTION_TABLE, $section);

            $this->questionnaireDB->markAsDeleted(QUESTIONNAIRE_TABLE, $questionnaireId);
            $response['value'] = true; // Success
            $response['message'] = 200;
            return $response;
        }
        else if (!$nobodyUpdated)
            // conflict error. Somebody already updated the question or record does not exists.
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Conflict error with the questionnaire.");
        else
            // Questionnaire locked.
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Questionnaire locked.");
    }

    /*
     * This function updates a questionnaire. If the questionnaire has being published already, it is considered as
     * locked and cannot be sent. First, it will remove any questions to the questionnaire, then it will add the ones
     * requested, and finally update the questions settings. Next, the dictionary will be updated with updated titles,
     * and finally the privacy and status (draft final) of the questionnaire will be updated.
     *
     * @params  $updatedQuestionnaire (array)
     * @return  void
     * */
    public function updateQuestionnaire($updatedQuestionnaire){
        $this->checkWriteAccess($updatedQuestionnaire);
        $total = 0;
        $questionnaireUpdated = 0;
        $oldQuestions = array();
        $newQuestions = array();
        $updatedQuestions = array();
        $questionsToKeep = array();
        $questionCheckPrivacy = array();
        $visualization = 0;

        //Get current questionnaire infos
        $oldQuestionnaire = $this->getQuestionnaireDetails($updatedQuestionnaire["ID"]);

        //If the questionnaire is locked, ignore all the changes
        if($this->isQuestionnaireLocked($oldQuestionnaire["ID"])) return false;

        //Look for the current questions IDs associated to the questionnaire
        foreach($oldQuestionnaire["questions"] as $question)
            if(!in_array($question["ID"], $oldQuestions))
                array_push($oldQuestions, $question["ID"]);

        //Prepare the list of questions to keep, to update and to insert
        /*
         * Because the ORMS system is unable to recognize what kind of format of visualization it must use for any new
         * questionnaire created (because an array was hardcoded manually with the questionnaire IDs on ORMS side which
         * is a VERY BAD IDEA), we have to change the visualization type here. This section of code should be changed
         * ASAP once the code will be properly updated on ORMS side (which means probably never)
         * */
        foreach($updatedQuestionnaire["questions"] as $question) {
            if ($question["typeId"] == SLIDERS)
                $visualization = 1;
            unset($question["typeId"]);
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
                "content"=>$updatedQuestionnaire["title_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["title"],
            ),
            array(
                "content"=>$updatedQuestionnaire["title_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["title"],
            ),
        );
        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, QUESTIONNAIRE_TABLE);

        $toUpdateDict = array(
            array(
                "content"=>$updatedQuestionnaire["description_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["description"],
            ),
            array(
                "content"=>$updatedQuestionnaire["description_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldQuestionnaire["description"],
            ),
        );
        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, QUESTIONNAIRE_TABLE);

        //Update privay and final fields if necessary
        $toUpdate = array(
            "ID"=>$oldQuestionnaire["ID"],
            "private"=>$updatedQuestionnaire["private"],
            "final"=>$updatedQuestionnaire["final"],
            "visualization"=>$visualization,
        );
        $questionnaireUpdated = $this->questionnaireDB->updateQuestionnaire($toUpdate);

        /*
         * If any modifications were made except to the questionnaire table, force the questionnaire entry to be updated
         * with the name and date anyway.
         * */
        if ($questionnaireUpdated == 0 && $total > 0)
            $this->questionnaireDB->forceUpdate($updatedQuestionnaire["ID"], QUESTIONNAIRE_TABLE);
    }
}
?>