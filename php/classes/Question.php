<?php

/**
 *
 * Question class
 */
class Question extends QuestionnaireModule {

    const PIVOTAL_QUESTION_FIELDS = array("ID", "display", "definition", "question", "typeId");
    const PIVOTAL_QUESTION_OPTIONS_FIELDS = array("ID", "questionId");
    const PIVOTAL_QUESTION_OPTIONS_SLIDERS_FIELDS = array("ID", "questionId", "minCaption", "maxCaption");
    const PIVOTAL_QUESTION_SUB_OPTIONS_FIELDS = array("ID", "parentTableId", "description");

    static function validateAndSanitize($questionToSanitize) {
        $validatedQuestion = array(
            "text_EN"=>htmlspecialchars($questionToSanitize['text_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "text_FR"=>htmlspecialchars($questionToSanitize['text_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "typeId"=>strip_tags($questionToSanitize['typeId']),
            "OAUserId"=>strip_tags($questionToSanitize['OAUserId']),
        );

        if($questionToSanitize["ID"] != "") {
            $validatedQuestion["ID"] = strip_tags($questionToSanitize["ID"]);
            if($validatedQuestion["ID"] == "")
                return false;
        }

        $libraries = array();
        if (count($questionToSanitize['libraries']) > 0)
            foreach($questionToSanitize['libraries'] as $library)
                array_push($libraries, strip_tags($library));

        $validatedQuestion["libraries"] = $libraries;
        $validatedQuestion["private"] = (strip_tags($questionToSanitize['private'])=="true"||strip_tags($questionToSanitize['private'])=="1"?"1":"0");
        $validatedQuestion["final"] = (strip_tags($questionToSanitize['final'])=="true"||strip_tags($questionToSanitize['final'])=="1"?"1":"0");

        if ($validatedQuestion["text_EN"] == "" || $validatedQuestion["text_FR"] == "" || $validatedQuestion["typeId"] == "")
            return false;

        $options = array();
        if(!empty($questionToSanitize["options"]))
            foreach($questionToSanitize["options"] as $key=>$value)
                if ($key != '$$hashKey')
                    $options[strip_tags($key)] = strip_tags($value);
        $validatedQuestion["options"] = $options;

        $subOptions = array();

        if(!empty($questionToSanitize["subOptions"])) {
            foreach ($questionToSanitize["subOptions"] as $aSub) {
                $newSub = array();
                foreach ($aSub as $key => $value)
                    if ($key != '$$hashKey')
                        $newSub[strip_tags($key)] = strip_tags($value);
                array_push($subOptions, $newSub);
            }
        }
        $validatedQuestion["subOptions"] = $subOptions;
        return $validatedQuestion;
    }

    /**
     * Inserts a question into our database
     * @param   array $questionDetails, array containing all the questions details
     * @return  ID of the new question
     */
    function insertQuestion($questionDetails){
        $validQuestionType = $this->questionnaireDB->getTypeTemplate($questionDetails['typeId']);
        if(!$validQuestionType)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetching question type error.");

        if(count($questionDetails['libraries']) > 0) {
            $librariesToAdd = $this->questionnaireDB->getLibraries($questionDetails['libraries']);
            if(count($librariesToAdd) <= 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetching library error.");
        }

        $toInsert = array(FRENCH_LANGUAGE=>$questionDetails['text_FR'], ENGLISH_LANGUAGE=>$questionDetails['text_EN']);
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
            "private"=>$questionDetails['private'],
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
        if ($validQuestionType["subTableName"] == CHECKBOX_OPTION_TABLE) {
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
    function getQuestions(){
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
                'text_EN'				=> strip_tags(htmlspecialchars_decode($row["text_EN"])),
                'text_FR'				=> strip_tags(htmlspecialchars_decode($row["text_FR"])),
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

    function getFinalizedQuestions(){
        $questionsLists = $this->questionnaireDB->getFinalizedQuestions();
        foreach ($questionsLists as &$row){
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

            $row["library_name_EN"] = $libNameEn;
            $row["library_name_FR"] = $libNameFr;

            $row["text_EN"] = htmlspecialchars_decode($row["text_EN"]);
            $row["text_FR"] = htmlspecialchars_decode($row["text_FR"]);

            if($row["typeId"] == SLIDERS)
                $options = $this->questionnaireDB->getQuestionSliderDetails($row["ID"], $row["tableName"]);
            else
                $options = $this->questionnaireDB->getQuestionOptionsDetails($row["ID"], $row["tableName"]);
            if (count($options) > 1)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question. Too many options.");

            $options = $options[0];

            $subOptions = null;
            if($row["subTableName"] != "" && $options["ID"] != "") {
                $subOptions = $this->questionnaireDB->getQuestionSubOptionsDetails($options["ID"], $row["subTableName"]);
            }
            $row["options"] = $options;
            $row["subOptions"] = $subOptions;
        }
        return $questionsLists;
    }

    function isQuestionLocked($questionId) {
        $questionnairesList = array();
        $questionnaires = $this->questionnaireDB->fetchQuestionnairesIdQuestion($questionId);

        foreach ($questionnaires as $questionnaire) {
            array_push($questionnairesList, $questionnaire["ID"]);
        }

        $questionLocked = 0;
        if (count($questionnairesList) > 0) {
            $questionLocked = $this->opalDB->countLockedQuestionnaires(implode(", ", $questionnairesList));
            $questionLocked = intval($questionLocked["total"]);
        }
        return $questionLocked;
    }

    /**
     * Gets question details
     *
     * @param   question ID (int)
     * @return  array $questionDetails : the question details
     */
    function getQuestionDetails($questionId) {
        $question = $this->questionnaireDB->getQuestionDetails($questionId);
        if(count($question) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot get question details.");

        $question = $question[0];
        $question["text_EN"] = htmlspecialchars_decode($question["text_EN"]);
        $question["text_FR"] = htmlspecialchars_decode($question["text_FR"]);
        $question["locked"] = $this->isQuestionLocked($questionId);

        $readOnly = false;
        $isOwner = false;
        if($this->questionnaireDB->getOAUserId() == $question["OAUserId"])
            $isOwner = true;
        if ($question["locked"])
            $readOnly = true;

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

        $question["options"] = $options;
        $question["subOptions"] = $subOptions;
        $question["readOnly"] = strval(intval($readOnly));
        $question["isOwner"] = strval(intval($isOwner));
        $question["libraries"] = $arrLib;

        return $question;
    }

    function updateLibrariesForQuestion($questionId, $libraries) {
        $total = 0;
        if(empty($libraries))
            $libraries = array("-1");
        $arrNewLib = $this->questionnaireDB->getLibrariesByUser(implode(", ", $libraries));

        $validNewLibraries = array();
        $toInsertLibraries = array();

        foreach ($arrNewLib as $lib) {
            array_push($validNewLibraries, $lib["ID"]);
            array_push($toInsertLibraries, array("questionId"=>$questionId, "libraryId"=>$lib["ID"]));
        }

        if(empty($validNewLibraries)) $validNewLibraries = array("-1");

        $total += $this->questionnaireDB->removeLibrariesForQuestion($questionId, $validNewLibraries);

        if(!empty($toInsertLibraries)) {
            $total += $this->questionnaireDB->insertLibrariesForQuestion($toInsertLibraries);
        }
        return $total;
    }

    function updateRadioButtonOptions($options, $subOptions) {
        $optionsToKeepAndUpdate = array();
        $optionsToAdd = array();
        $total = 0;
        self::sortOptions($subOptions);

        foreach($subOptions as $sub)
            if ($sub["ID"] != "")
                array_push($optionsToKeepAndUpdate, $sub["ID"]);

        if (empty($optionsToKeepAndUpdate))
            $optionsToKeepAndUpdate = array("-1");

        if (!empty($optionsToKeepAndUpdate)) {
            $optionsToDelete = $this->questionnaireDB->fetchOptionsToBeDeleted(RADIO_BUTTON_OPTION_TABLE, RADIO_BUTTON_TABLE, $options["ID"], $optionsToKeepAndUpdate);
            foreach ($optionsToDelete as $opt)
                $this->questionnaireDB->markAsDeletedInDictionary($opt["description"]);
            $total += $this->questionnaireDB->deleteOptionsForQuestion(RADIO_BUTTON_OPTION_TABLE, RADIO_BUTTON_TABLE, $options["ID"], $optionsToKeepAndUpdate);

            foreach($subOptions as $data) {
                $toUpdate = array();
                foreach($data as $key=>$value) {
                    if (in_array($key, array("ID","description_FR","description_EN"))) continue;
                    else if($key == "OAUserId")
                        $toUpdate["OAUserId"] = $value;
                    else
                        $toUpdate[$key] = $value;
                }

                $toUpdateDict = array(
                    array(
                        "content"=>$data["description_FR"],
                        "languageId"=>FRENCH_LANGUAGE,
                        "contentId"=>$data["description"],
                    ),
                    array(
                        "content"=>$data["description_EN"],
                        "languageId"=>ENGLISH_LANGUAGE,
                        "contentId"=>$data["description"],
                    ),
                );
                $total += $this->questionnaireDB->updateDictionary($toUpdateDict, RADIO_BUTTON_OPTION_TABLE);
                $total += $this->questionnaireDB->updateSubOptionsForQuestion(RADIO_BUTTON_OPTION_TABLE, RADIO_BUTTON_TABLE, $data["ID"], $toUpdate);
            }
        }

        foreach($subOptions as $sub) {
            if ($sub["ID"] == "") {
                $toInsert = array(FRENCH_LANGUAGE=>$sub['description_FR'], ENGLISH_LANGUAGE=>$sub['description_EN']);
                $dictId = $this->questionnaireDB->addToDictionary($toInsert, RADIO_BUTTON_TABLE);
                array_push($optionsToAdd, array("parentTableId"=>$options["ID"], "description"=>$dictId, "order"=>$sub["order"]));
            }
        }

        if (!empty($optionsToAdd))
            $total += $this->questionnaireDB->insertOptionsQuestion(RADIO_BUTTON_OPTION_TABLE, $optionsToAdd);
        return $total;
    }

    function updateCheckboxOptions($options, $subOptions) {
        $optionsToKeepAndUpdate = array();
        $optionsToAdd = array();
        $total = 0;
        self::sortOptions($subOptions);

        foreach($subOptions as $sub)
            if ($sub["ID"] != "")
                array_push($optionsToKeepAndUpdate, $sub["ID"]);

        if (empty($optionsToKeepAndUpdate))
            $optionsToKeepAndUpdate = array("-1");

        if (!empty($optionsToKeepAndUpdate)) {
            $optionsToDelete = $this->questionnaireDB->fetchOptionsToBeDeleted(CHECKBOX_OPTION_TABLE, CHECKBOX_TABLE, $options["ID"], $optionsToKeepAndUpdate);
            foreach ($optionsToDelete as $opt)
                $this->questionnaireDB->markAsDeletedInDictionary($opt["description"]);
            $total += $this->questionnaireDB->deleteOptionsForQuestion(CHECKBOX_OPTION_TABLE, CHECKBOX_TABLE, $options["ID"], $optionsToKeepAndUpdate);

            foreach($subOptions as $data) {
                $toUpdate = array();
                foreach($data as $key=>$value) {
                    if (in_array($key, array("ID","description_FR","description_EN"))) continue;
                    else if($key == "OAUserId")
                        $toUpdate["OAUserId"] = $value;
                    else
                        $toUpdate[$key] = $value;
                }

                $toUpdateDict = array(
                    array("content"=>$data["description_FR"], "languageId"=>FRENCH_LANGUAGE, "contentId"=>$data["description"]),
                    array("content"=>$data["description_EN"], "languageId"=>ENGLISH_LANGUAGE, "contentId"=>$data["description"]),
                );
                $total += $this->questionnaireDB->updateDictionary($toUpdateDict, CHECKBOX_OPTION_TABLE);
                $total += $this->questionnaireDB->updateSubOptionsForQuestion(CHECKBOX_OPTION_TABLE, CHECKBOX_TABLE, $data["ID"], $toUpdate);
            }
        }

        foreach($subOptions as $sub) {
            if ($sub["ID"] == "") {
                $toInsert = array(FRENCH_LANGUAGE=>$sub['description_FR'], ENGLISH_LANGUAGE=>$sub['description_EN']);
                $dictId = $this->questionnaireDB->addToDictionary($toInsert, CHECKBOX_OPTION_TABLE);
                array_push($optionsToAdd, array("parentTableId"=>$options["ID"], "description"=>$dictId, "order"=>$sub["order"]));
            }
        }

        if (!empty($optionsToAdd))
            $total += $this->questionnaireDB->insertOptionsQuestion(CHECKBOX_OPTION_TABLE, $optionsToAdd);

        $options["minAnswer"] = 1;
        $options["maxAnswer"] = $this->questionnaireDB->getQuestionTotalSubOptions($options["ID"], CHECKBOX_OPTION_TABLE);
        $options["maxAnswer"] = $options["maxAnswer"]["total"];
        $tempId = $options["ID"];
        unset($options["ID"]);

        $total += $this->questionnaireDB->updateOptionsForQuestion(CHECKBOX_TABLE, $tempId, $options);
        return $total;
    }

    function updateSliderOptions($options) {
        $total = 0;

        $options["minValue"] = floatval($options["minValue"]);
        $options["maxValue"] = floatval($options["maxValue"]);
        $options["increment"] = floatval($options["increment"]);

        if( $options["minCaption_EN"] == "" ||  $options["minCaption_FR"] == "" ||  $options["maxCaption_EN"] == "" ||  $options["maxCaption_FR"] == "" || $options["minValue"] <= 0.0 || $options["maxValue"] <= 0.0 || $options["increment"] <= 0.0 || $options["minValue"] >= $options["maxValue"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid data.");

        $options["maxValue"] = floatval(floor(($options["maxValue"] - $options["minValue"]) / $options["increment"]) * $options["increment"]) + $options["minValue"];

        $toUpdateDict = array(
            array("content"=>$options["minCaption_FR"], "languageId"=>FRENCH_LANGUAGE, "contentId"=>$options["minCaption"]),
            array("content"=>$options["minCaption_EN"], "languageId"=>ENGLISH_LANGUAGE, "contentId"=>$options["minCaption"]),
        );

        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, SLIDER_TABLE);

        $toUpdateDict = array(
            array("content"=>$options["maxCaption_FR"], "languageId"=>FRENCH_LANGUAGE, "contentId"=>$options["maxCaption"]),
            array("content"=>$options["maxCaption_EN"], "languageId"=>ENGLISH_LANGUAGE, "contentId"=>$options["maxCaption"]),
        );
        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, SLIDER_TABLE);

        $sliderToUpdate = array(
            "minValue"=>$options["minValue"],
            "maxValue"=>$options["maxValue"],
            "increment"=>$options["increment"],
        );

        $total += $this->questionnaireDB->updateOptionsForQuestion(SLIDER_TABLE, $options["ID"], $sliderToUpdate);
        return $total;
    }

    /*
     * This function validate the pivotal IDs of an updated question to insure it will not compromise the data when
     * updating the database.
     *
     * @params  Reference of the updated questions (array) and current question in the DB (array)
     * @return  boolean if the data are compromised or not.
     * */
    static function validatePivotalIDs(&$updatedQuestion, &$oldQuestion) {
        $answer = true;
        $arrayOldOption = array();

        if(!empty($oldQuestion["subOptions"])) {
            foreach ($oldQuestion["subOptions"] as $options)
                $arrayOldOption[$options["ID"]] = $options;
        }

        foreach($updatedQuestion as $key=>$value)
            if(in_array($key, self::PIVOTAL_QUESTION_FIELDS) && $oldQuestion[$key] != $value) {
                $answer = false;
                break;
            }

        if($oldQuestion["typeId"] == SLIDERS)
            $fieldLists = self::PIVOTAL_QUESTION_OPTIONS_SLIDERS_FIELDS;
        else
            $fieldLists = self::PIVOTAL_QUESTION_OPTIONS_FIELDS;

        foreach($updatedQuestion["options"] as $key=>$value) {
            if(in_array($key, $fieldLists) && $oldQuestion["options"][$key] !== $value) {
                $answer = false;
                break;
            }
        }

        foreach($updatedQuestion["subOptions"] as $sub) {
            $tempId = $sub["ID"];
            foreach ($sub as $key => $value) {
                if (in_array($key, self::PIVOTAL_QUESTION_SUB_OPTIONS_FIELDS) && $value !== $arrayOldOption[$tempId][$key]) {
                    $answer = false;
                    break;
                }
            }
        }
        return  $answer;
    }

    /**
     * This function update a question after validating the data.
     *
     * If the user is registered, is the owner of the question or the question is public, no data are missing or
     * corrupted, the update starts.
     *
     * First, the libraries associated to the question are updated. Then, if the question was not sent already to a
     * patient, it will update the dictionary with the new question text, the private and final status, and finally
     * the options depending the type of questions (slider, checkbox, etc)
     *
     * All these updates will be made only if there is only changes made. If there was any changes made but not in
     * the question table, the question will still be updated with the date and username of the person who made the
     * changes.
     *
     * param   array question details (array)
     * return  void
     */
    function updateQuestion($updatedQuestion) {
        $total = 0;
        $oldQuestion = $this->getQuestionDetails($updatedQuestion["ID"]);
        $isLocked = $this->isQuestionLocked($oldQuestion["ID"]);
        if ($oldQuestion["deleted"] == DELETED_RECORD || $this->questionnaireDB->getUsername() == "" || ($oldQuestion["private"] == 1 && $this->questionnaireDB->getOAUserId() != $oldQuestion["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");
        else if(empty($updatedQuestion["options"]) || ($updatedQuestion["typeId"] == RADIO_BUTTON || $updatedQuestion["typeId"] == CHECKBOXES) && empty($updatedQuestion["subOptions"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data.");
        else if(!self::validatePivotalIDs($updatedQuestion, $oldQuestion))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Corrupted data.");

        $total += $this->updateLibrariesForQuestion($updatedQuestion["ID"], $updatedQuestion["libraries"]);

        if($isLocked) {
            if ($total > 0)
                $this->questionnaireDB->forceUpdateQuestion($updatedQuestion["ID"]);
            return true;
        }

        $toUpdateDict = array(
            array(
                "content"=>$updatedQuestion["text_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldQuestion["question"],
            ),
            array(
                "content"=>$updatedQuestion["text_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldQuestion["question"],
            ),
        );

        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, QUESTION_TABLE);
        $toUpdateQuestion = array(
            "ID"=>$oldQuestion["ID"],
            "private"=>$updatedQuestion["private"],
            "final"=>$updatedQuestion["final"],
        );
        $questionUpdated = $this->questionnaireDB->updateQuestion($toUpdateQuestion);


        if($updatedQuestion["typeId"] == RADIO_BUTTON)
            $total += $this->updateRadioButtonOptions($updatedQuestion["options"],$updatedQuestion["subOptions"]);
        else if($updatedQuestion["typeId"] == CHECKBOXES)
            $total += $this->updateCheckboxOptions($updatedQuestion["options"],$updatedQuestion["subOptions"]);
        else if($updatedQuestion["typeId"] == SLIDERS)
            $total += $this->updateSliderOptions($updatedQuestion["options"]);

        if ($questionUpdated == 0 && $total > 0)
            $this->questionnaireDB->forceUpdateQuestion($updatedQuestion["ID"]);
    }

    /**
     * Mark a question as deleted. First, it get the last time it was updated, check if the user has the proper
     * authorization, and check if the question was already sent to a patient. Then it checked if the record was
     * updated in the meantime, and if not, it marks the question as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionId (ID of the question)
     * @return array $response : response
     */
    function deleteQuestion($questionId) {
        $questionToDelete = $this->questionnaireDB->getQuestionDetails($questionId);
        $questionToDelete = $questionToDelete[0];
        if ($this->questionnaireDB->getOAUserId() <= 0 || $questionToDelete["deleted"] == 1 || ($questionToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $questionToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

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
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["display"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["definition"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["question"]);
            $this->questionnaireDB->removeAllLibrariesForQuestion($questionId);
            $this->questionnaireDB->removeAllSectionForQuestion($questionId);
            $this->questionnaireDB->removeAllTagsForQuestion($questionId);
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