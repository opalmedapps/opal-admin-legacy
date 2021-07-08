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
            $questionnaire["locked"] = $this->_isQuestionnaireLocked($questionnaire["ID"]);
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
            $questionnaire["locked"] = $this->_isQuestionnaireLocked($questionnaire["ID"]);
        }
        return $results;
    }

    /*
     * This function validate and sanitize a questionnaire
     * @param   $post (array)
     * @return  sanitized questionnaire or false if invalid
     * */
    function validateAndSanitize($post) {
        $post = HelpSetup::arraySanitization($post);
        //arraySanitization
        //Sanitize the name of the questionnaire. If it is empty, rejects it.
        $validatedQuestionnaire = array(
            "title_EN"=>htmlspecialchars($post['title_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "title_FR"=>htmlspecialchars($post['title_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "description_EN"=>htmlspecialchars($post['description_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "description_FR"=>htmlspecialchars($post['description_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        );

        if ($validatedQuestionnaire["title_EN"] == "" || $validatedQuestionnaire["title_FR"] == "" || $validatedQuestionnaire["description_EN"] == "" || $validatedQuestionnaire["description_FR"] == "")
            return false;

        //Sanitize the questionnaire ID (if any). IF there was supposed to be an ID and it's empty, reject the
        //questionnaire
        if($post["ID"] != "") {
            $validatedQuestionnaire["ID"] = strip_tags($post["ID"]);
            if($validatedQuestionnaire["ID"] == "")
                return false;
        }

        //Sanitize the list of libraries associated to the questionnaire
        $libraries = array();
        if (is_array($post['libraries']) && count($post['libraries']) > 0)
            foreach($post['libraries'] as $library)
                array_push($libraries, strip_tags($library));

        $validatedQuestionnaire["libraries"] = $libraries;
        $validatedQuestionnaire["private"] = (strip_tags($post['private'])=="true"||strip_tags($post['private'])=="1"?"1":"0");
        $validatedQuestionnaire["final"] = (strip_tags($post['final'])=="true"||strip_tags($post['final'])=="1"?"1":"0");

        //Validate the list of questions
        $options = array();
        $arrIds = array();
        if(!empty($post["questions"]))
            foreach($post["questions"] as $question) {
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

        // If the purpose does not exists, reject it
        if (array_key_exists("purpose", $post) && $post["purpose"] != "") {
            $purpose = $this->questionnaireDB->getPurposeDetails($post["purpose"]);
            if(count($purpose) != 1)
                return false;
            else
                $validatedQuestionnaire["purpose"] = $post["purpose"];
        } else
            return false;

        // If the respondent does not exists, reject it
        if (array_key_exists("respondent", $post) && $post["respondent"] != "") {
            $respondent = $this->questionnaireDB->getRespondentDetails($post["respondent"]);
            if(count($respondent) != 1)
                return false;
            else
                $validatedQuestionnaire["respondent"] = $post["respondent"];
        } else
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
    protected function _isQuestionnaireLocked($questionnaireId) {
        $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnaireId);
        $questionLocked = (intval($questionLocked["total"]) > 0?true:false);
        return $questionLocked;
    }

    public function getPurposesRespondents() {
        $this->checkReadAccess();
        $result["purposes"] = $this->questionnaireDB->getPurposes();
        $result["respondents"] = $this->questionnaireDB->getRespondents();
        return $result;
    }

    /*
     * API call to get questionnaire details
     *
     * @param integer $questionnaireId : the questionnaire ID
     * @return array $questionnaireDetails : the questionnaire details
     */
    public function getQuestionnaireDetails($questionnaireId){
        $this->checkReadAccess($questionnaireId);
        return $this->_getQuestionnaireDetails($questionnaireId);

    }

    /*
     * Protected method used only internally to get questionnaire details
     *
     * @param integer $questionnaireId : the questionnaire ID
     * @return array $questionnaireDetails : the questionnaire details
     * */
    protected function _getQuestionnaireDetails($questionnaireId) {
        $questionnaireDetails = $this->questionnaireDB->getQuestionnaireDetails($questionnaireId);

        if(count($questionnaireDetails) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");

        $questionnaireDetails = $questionnaireDetails[0];

        $questionnaireDetails["title_EN"] = htmlspecialchars_decode($questionnaireDetails["title_EN"]);
        $questionnaireDetails["title_FR"] = htmlspecialchars_decode($questionnaireDetails["title_FR"]);
        $questionnaireDetails["description_EN"] = htmlspecialchars_decode($questionnaireDetails["description_EN"]);
        $questionnaireDetails["description_FR"] = htmlspecialchars_decode($questionnaireDetails["description_FR"]);
        $questionnaireDetails["locked"] = intval($this->_isQuestionnaireLocked($questionnaireId));
        $questionnaireDetails["purpose"] = $this->questionnaireDB->getPurposeDetails($questionnaireDetails["purpose"]);
        $questionnaireDetails["purpose"] = $questionnaireDetails["purpose"][0];
        $questionnaireDetails["respondent"] = $this->questionnaireDB->getRespondentDetails($questionnaireDetails["respondent"]);
        $questionnaireDetails["respondent"] = $questionnaireDetails["respondent"][0];

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

        $toInsert = array(
            "title"=>$title,
            "nickname"=>$nickname,
            "description"=>$description,
            "instruction"=>$instruction,
            "private"=>$newQuestionnaire["private"],
            "purposeId"=>$newQuestionnaire["purpose"],
            "respondentId"=>$newQuestionnaire["respondent"],
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
        $questionnaireToDelete = $this->_getQuestionnaireDetails($questionnaireId);

        if ($this->questionnaireDB->getOAUserId() <= 0 || $questionnaireToDelete["deleted"] == 1 || ($questionnaireToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $questionnaireToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTIONNAIRE_TABLE, $questionnaireId);

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTIONNAIRE_TABLE, $questionnaireId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $wasQuestionSent = $this->_isQuestionnaireLocked($questionnaireId);

        if ($nobodyUpdated && !$wasQuestionSent) {
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["title"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["nickname"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["description"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionnaireToDelete["instruction"]);


            foreach($questionnaireToDelete["sections"] as $section)
                $this->questionnaireDB->markAsDeletedNoUSer(SECTION_TABLE, $section);

            $this->questionnaireDB->markAsDeleted(QUESTIONNAIRE_TABLE, $questionnaireId);
            $this->opalDB->purgeQuestionnaireFromStudies($questionnaireId);
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
        $oldQuestions = array();
        $newQuestions = array();
        $updatedQuestions = array();
        $questionsToKeep = array();
        $questionCheckPrivacy = array();
        $visualization = 1;

        //Get current questionnaire infos
        $oldQuestionnaire = $this->_getQuestionnaireDetails($updatedQuestionnaire["ID"]);

        //If the questionnaire is locked, ignore all the changes
        if($this->_isQuestionnaireLocked($oldQuestionnaire["ID"])) return false;

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
            if ($question["typeId"] != SLIDERS)
                $visualization = 0;
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
            "purposeId"=>$updatedQuestionnaire["purpose"],
            "respondentId"=>$updatedQuestionnaire["respondent"],
        );
        $questionnaireUpdated = $this->questionnaireDB->updateQuestionnaire($toUpdate);

        // If the questionnaire is a draft or is not a research one or not for patient, make sure it's not assigned to
        // any study
        if($updatedQuestionnaire["final"] == NON_FINAL_RECORD || $updatedQuestionnaire["purpose"] != PURPOSE_RESEARCH || $updatedQuestionnaire["respondent"] != RESPONDENT_PATIENT)
            $this->opalDB->purgeQuestionnaireFromStudies($oldQuestionnaire["ID"]);

        /*
         * If any modifications were made except to the questionnaire table, force the questionnaire entry to be updated
         * with the name and date anyway.
         * */
        if ($questionnaireUpdated == 0 && $total > 0)
            $this->questionnaireDB->forceUpdate($updatedQuestionnaire["ID"], QUESTIONNAIRE_TABLE);
    }

    /**
     * Get the list of questionnaires status, visualization form, and completion date for a specific patient on a site
     * Validation code :    Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit information
     *                      are coded from right to left:
     *                      1: mrn is missing
     *                      2: site is missing
     *                      3: combo mrn site does not exists
     * @param $post array - $_POST content
     * @return array
     */
    public function getQuestionnaireListOrms($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post))
            $errCode = $this->_validateBasicPatientInfo($post, $patientSite);
        else
            $errCode = "111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->questionnaireDB->getQuestionnaireListOrms($post["mrn"], $post["site"]);
    }

    /**
     * Get the list of chart answer from a specific questionnaire for a specific patient.
     *
     * Note: Using questionSectionId and questionText does not seems to be the right thing to do but because of a lack
     * of time and man power, we cannot test it more and simplify the SQL query. See ticket OPAL-1026 for more details.
     *
     * @param $post array - $_POST content. Contains mrn, site code and questionnaireId
     * @return array - answers found (if any)
     */
    public function getChartAnswersFromQuestionnairePatient($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $patientSite = array();
        $errCode = $this->_validateQuestionnaireAndPatient($post, $patientSite);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $patientInfo = $this->questionnaireDB->getPatientPerExternalId($patientSite["PatientSerNum"]);

        if(count($patientInfo) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Patients info invalid in DB. Please contact your admin.");
        $patientInfo = $patientInfo[0];

        $results = $this->questionnaireDB->getQuestionsByQuestionnaireId($post["questionnaireId"]);
        foreach ($results as &$result) {
            $result["answers"] = $this->questionnaireDB->getAnswersChartType(
                $patientInfo["ID"],
                $result["questionnaireId"],
                $result["questionSectionId"],
                $result["question_EN"]
            );
        }
        
        return $results;
    }

    /**
     * Get the list of non chart answer from a specific questionnaire for a specific patient.
     * @param $post array - $_POST content. Contains mrn, site code and questionnaireId
     * @return array - answers found (if any)
     */
    public function getNonChartAnswersFromQuestionnairePatient($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $patientSite = array();
        $errCode = $this->_validateQuestionnaireAndPatient($post, $patientSite);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $patientInfo = $this->questionnaireDB->getPatientPerExternalId($patientSite["PatientSerNum"]);

        if(count($patientInfo) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Patients info invalid in DB. Please contact your admin.");
        $patientInfo = $patientInfo[0];

        $results = $this->questionnaireDB->getCompletedQuestionnaireInfo($patientInfo["ID"], $post["questionnaireId"]);

        foreach ($results as &$item) {
            $item["options"] = $this->questionnaireDB->getQuestionOptions($item["questionId"]);
            $item["answers"] = $this->questionnaireDB->getAnswersNonChartType(
                $item["answerQuestionnaireId"],
                $item["sectionId"],
                $item["questionId"]
            );
        }

        return $results;
    }

    /**
     * Validate questionnaire and patient information.
     * @param $post array - data to validate
     * @param $patientSite array - patient info from specific site found
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit information
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     *                      4: questionnaire ID does not exists
     * @return string - validation code in binary
     */
    protected function _validateQuestionnaireAndPatient(&$post, &$patientSite) {
        if (is_array($post)) {
            $errCode = $this->_validateBasicPatientInfo($post, $patientSite);

            // 4th bit - Questionnaire ID
            if(!array_key_exists("questionnaireId", $post) || $post["questionnaireId"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        } else
            $errCode = "1111";
        return $errCode;
    }

    /**
     * Return the list of published questionnaires found.
     * @return array - published questionnaires found
     */
    public function getPublishedQuestionnaires() {
        $this->checkReadAccess();
        return $this->questionnaireDB->getPublishedQuestionnaires();
    }

    /**
     * Get the list of questionnaires a specific patient answered.
     * @param $post - contains the MRN of the patient and the site of the hospital
     * @return array - list of questionnaires found
     */
    public function getAnsweredQuestionnairesPatient($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $patientSite = array();
        if (is_array($post))
            $errCode = $this->_validateBasicPatientInfo($post, $patientSite);
        else
            $errCode = "111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->questionnaireDB->getAnsweredQuestionnairesPatient($post["mrn"], $post["site"]);
    }

    /**
     * Get the last completed questionnaire from a specific patient on a site.
     * @param $post array - contains mrn and site
     * @return array - last answered questionnaire found (if any)
     */
    public function getLastCompletedQuestionnaire($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $patientSite = array();
        if (is_array($post))
            $errCode = $this->_validateBasicPatientInfo($post, $patientSite);
        else
            $errCode = "111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->opalDB->getLastCompletedQuestionnaire($patientSite["PatientSerNum"]);
    }

    public function getPatientsCompletedQuestionnaires($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        if (is_array($post)) {
            if (!array_key_exists("questionnaires", $post) || (!is_array($post["questionnaires"])))
                $post["questionnaires"] = array();
            $listIds = array();


            foreach ($post["questionnaires"]as $item) {
                $item = intval($item);
                if(!in_array($item, $listIds))
                    array_push($listIds, $item);
            }
            if (count($listIds) != count($post["questionnaires"]))
                $errCode = "1";
            else {
                $errCode = "0";
                $post["questionnaires"] = $listIds;
            }
        }
        else
            $errCode = "1";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->opalDB->getPatientsCompletedQuestionnaires($post["questionnaires"]);
    }
}