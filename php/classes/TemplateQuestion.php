<?php

/* Template question class */

class TemplateQuestion extends QuestionnaireModule {

    const PIVOTAL_TEMPLATE_QUESTION_FIELDS = array("ID", "name", "typeId");
    const PIVOTAL_TEMPLATE_QUESTION_OPTIONS_FIELDS = array("ID", "templateQuestionId");
    const PIVOTAL_TEMPLATE_QUESTION_OPTIONS_SLIDERS_FIELDS = array("ID", "templateQuestionId", "minCaption", "maxCaption");
    const PIVOTAL_TEMPLATE_QUESTION_SUB_OPTIONS_FIELDS = array("ID", "parentTableId", "description");

    public function validateAndSanitize($newTemplateQuestion) {
        $validatedQT = array(
            'typeId' => strip_tags($newTemplateQuestion['typeId']),
            'name_EN' => strip_tags($newTemplateQuestion['name_EN']),
            'name_FR' => strip_tags($newTemplateQuestion['name_FR']),
            'private' => strip_tags($newTemplateQuestion['private']),
            'OAUserId' => strip_tags($newTemplateQuestion['OAUserId']),
            'options' => $newTemplateQuestion['options'],
        );

        if ($newTemplateQuestion["ID"] != "")
            $validatedQT["ID"] = strip_tags($newTemplateQuestion['ID']);

        if( $validatedQT["typeId"] == "" ||  $validatedQT["name_EN"] == "" ||  $validatedQT["name_FR"] == "" ||  $validatedQT["OAUserId"] == "")
            return false;


        $options = array();
        if(!empty($newTemplateQuestion["options"]))
            foreach($newTemplateQuestion["options"] as $key=>$value)
                if ($key != '$$hashKey')
                    $options[strip_tags($key)] = strip_tags($value);
        $validatedQT["options"] = $options;


        $subOptions = array();

        if(!empty($newTemplateQuestion["subOptions"])) {
            foreach ($newTemplateQuestion["subOptions"] as $aSub) {
                $newSub = array();
                foreach ($aSub as $key => $value)
                    if ($key != '$$hashKey')
                        $newSub[strip_tags($key)] = strip_tags($value);
                array_push($subOptions, $newSub);
            }
        }
        $validatedQT["subOptions"] = $subOptions;

        if($validatedQT["typeId"] == SLIDERS) {
            if(!$validatedQT["options"] = $this->validateSliderForm($validatedQT["options"]))
                return false;
        }
        else if ($validatedQT["typeId"] == CHECKBOXES || $validatedQT["typeId"] == RADIO_BUTTON) {
            if (count($validatedQT["subOptions"]) <= 0) return false;
            foreach($validatedQT["subOptions"] as $sub) {
                if($sub["description_EN"] == "" || $sub["description_FR"] == "" || $sub["order"] == "")
                    return false;
            }
        }
        return $validatedQT;
    }

    /*
     * This function validate the pivotal IDs of an updated type template to insure it will not compromise the data when
     * updating the database.
     *
     * @params  Reference of the updated type template (array) and current type template in the DB (array)
     * @return  boolean if the data are compromised or not.
     * */
    function validatePivotalIDs(&$updatedTemplateQuestion, &$oldTemplateQuestion) {
        $answer = true;
        $arrayOldOption = array();

        if(!empty($oldTemplateQuestion["subOptions"])) {
            foreach ($oldTemplateQuestion["subOptions"] as $options)
                $arrayOldOption[$options["ID"]] = $options;
        }

        foreach($updatedTemplateQuestion as $key=>$value)
            if(in_array($key, self::PIVOTAL_TEMPLATE_QUESTION_FIELDS) && $oldTemplateQuestion[$key] != $value) {
                $answer = false;
                break;
            }

        if($oldTemplateQuestion["typeId"] == SLIDERS) {
            $updatedTemplateQuestion["subOptions"] = array();
            $fieldLists = self::PIVOTAL_TEMPLATE_QUESTION_OPTIONS_SLIDERS_FIELDS;
        }
        else
            $fieldLists = self::PIVOTAL_TEMPLATE_QUESTION_OPTIONS_FIELDS;

        foreach($updatedTemplateQuestion["options"] as $key=>$value) {
            if(in_array($key, $fieldLists) && $oldTemplateQuestion["options"][$key] !== $value) {
                $answer = false;
                break;
            }
        }

        foreach($updatedTemplateQuestion["subOptions"] as $sub) {
            $tempId = $sub["ID"];
            foreach ($sub as $key => $value) {
                if (in_array($key, self::PIVOTAL_TEMPLATE_QUESTION_SUB_OPTIONS_FIELDS) && $value !== $arrayOldOption[$tempId][$key]) {
                    $answer = false;
                    break;
                }
            }
        }
        return  $answer;
    }

    /**
     * This function update a question type template after validating the data.
     *
     * If the user is registered, is the owner of the question or the question is public, no data are missing or
     * corrupted, the update starts.
     *
     * First, it will update the dictionary with the new question text, the private and final status, and finally
     * the options depending the type of template (slider, checkbox, etc)
     *
     * All these updates will be made only if there is only changes made. If there was any changes made but not in
     * the type template table, the question will still be updated with the date and username of the person who made the
     * changes.
     *
     * param   array type template details (array)
     * return  void
     */
    function updateTemplateQuestion($updatedTemplateQuestion) {
        $total = 0;
        $oldTemplateQuestion = $this->getTemplateQuestionDetails($updatedTemplateQuestion["ID"]);
        $updatedTemplateQuestion = $this->validateAndSanitize($updatedTemplateQuestion);

        if(!$updatedTemplateQuestion)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");
        if ($oldTemplateQuestion["deleted"] == DELETED_RECORD || $this->questionnaireDB->getUsername() == "" || ($oldTemplateQuestion["private"] == 1 && $this->questionnaireDB->getOAUserId() != $oldTemplateQuestion["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");
        else if(empty($updatedTemplateQuestion["options"]) || ($updatedTemplateQuestion["typeId"] == RADIO_BUTTON || $updatedTemplateQuestion["typeId"] == CHECKBOXES) && empty($updatedTemplateQuestion["subOptions"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data.");
        else if(!$this->validatePivotalIDs($updatedTemplateQuestion, $oldTemplateQuestion))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Corrupted data.");

        $toUpdateDict = array(
            array(
                "content"=>$updatedTemplateQuestion["name_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldTemplateQuestion["name"],
            ),
            array(
                "content"=>$updatedTemplateQuestion["name_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldTemplateQuestion["name"],
            ),
        );

        print_R($oldTemplateQuestion);
        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, TEMPLATE_QUESTION_TABLE);
        $toUpdateTemplateQuestion = array(
            "ID"=>$oldTemplateQuestion["ID"],
            "private"=>$updatedTemplateQuestion["private"],
        );
        $totalQuestionUpdated = $this->questionnaireDB->updateTemplateQuestion($toUpdateTemplateQuestion);




        print_R($toUpdateTemplateQuestion);die($totalQuestionUpdated . " " . $total);






        /*


        $toUpdateDict = array(
            array(
                "content"=>$updatedTemplateQuestion["question_FR"],
                "languageId"=>FRENCH_LANGUAGE,
                "contentId"=>$oldTemplateQuestion["question"],
            ),
            array(
                "content"=>$updatedTemplateQuestion["question_EN"],
                "languageId"=>ENGLISH_LANGUAGE,
                "contentId"=>$oldTemplateQuestion["question"],
            ),
        );

        $total += $this->questionnaireDB->updateDictionary($toUpdateDict, QUESTION_TABLE);
        $toUpdateQuestion = array(
            "ID"=>$oldTemplateQuestion["ID"],
            "private"=>$updatedTemplateQuestion["private"],
            "final"=>$updatedTemplateQuestion["final"],
        );
        $questionUpdated = $this->questionnaireDB->updateQuestion($toUpdateQuestion);


        if($updatedTemplateQuestion["typeId"] == RADIO_BUTTON)
            $total += $this->updateRadioButtonOptions($updatedTemplateQuestion["options"],$updatedTemplateQuestion["subOptions"]);
        else if($updatedTemplateQuestion["typeId"] == CHECKBOXES)
            $total += $this->updateCheckboxOptions($updatedTemplateQuestion["options"],$updatedTemplateQuestion["subOptions"]);
        else if($updatedTemplateQuestion["typeId"] == SLIDERS)
            $total += $this->updateSliderOptions($updatedTemplateQuestion["options"]);

        if ($questionUpdated == 0 && $total > 0)
            $this->questionnaireDB->forceUpdateQuestion($updatedTemplateQuestion["ID"]);

         * */






    }

    /*
     * Inserts a new question type.
     * @param array $newTemplateQuestion : the question type to be inserted
     * @return void
     */
    public function insertTemplateQuestion($newTemplateQuestion){

        $newTemplateQuestion = $this->validateAndSanitize($newTemplateQuestion);

        if(!$newTemplateQuestion)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

        $options = array();
        $subOptions = array();
        if($newTemplateQuestion["typeId"] == CHECKBOXES) {
            $tableToInsert = TEMPLATE_QUESTION_CHECKBOX_TABLE;
            $subTableToInsert = TEMPLATE_QUESTION_CHECKBOX_OPTION_TABLE;

            foreach($newTemplateQuestion["subOptions"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["description_FR"], ENGLISH_LANGUAGE=>$opt["description_EN"]);
                $tempArray["description"] = $this->questionnaireDB->addToDictionary($toInsert, TEMPLATE_QUESTION_TABLE);
                $tempArray["order"] = $opt["order"];
                array_push($subOptions, $tempArray);
            }
            $options["minAnswer"]= 1;
            $options["maxAnswer"] = count($subOptions);
        }
        else if ($newTemplateQuestion["typeId"] == RADIO_BUTTON) {
            $tableToInsert = TEMPLATE_QUESTION_RADIO_BUTTON_TABLE;
            $subTableToInsert = TEMPLATE_QUESTION_RADIO_BUTTON_OPTION_TABLE;

            foreach($newTemplateQuestion["subOptions"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["description_FR"], ENGLISH_LANGUAGE=>$opt["description_EN"]);
                $tempArray["description"] = $this->questionnaireDB->addToDictionary($toInsert, TEMPLATE_QUESTION_TABLE);
                $tempArray["order"] = $opt["order"];
                array_push($subOptions, $tempArray);
            }
        }
        else if ($newTemplateQuestion["typeId"] == SLIDERS) {
            $tableToInsert = TEMPLATE_QUESTION_SLIDER_TABLE;
            $toInsert = array(
                FRENCH_LANGUAGE=>$newTemplateQuestion["options"]["minCaption_FR"], ENGLISH_LANGUAGE=>$newTemplateQuestion["options"]["minCaption_EN"]
            );
            $options["minCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TEMPLATE_QUESTION_TABLE);
            $toInsert = array(
                FRENCH_LANGUAGE=>$newTemplateQuestion["options"]["maxCaption_FR"], ENGLISH_LANGUAGE=>$newTemplateQuestion["options"]["maxCaption_EN"]
            );
            $options["maxCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TEMPLATE_QUESTION_TABLE);
            $options["minValue"] = $newTemplateQuestion["options"]["minValue"];
            $options["maxValue"] = $newTemplateQuestion["options"]["maxValue"];
            $options["increment"] = $newTemplateQuestion["options"]["increment"];
        }
        else {
            $newTemplateQuestion["typeId"] = TEXT_BOX;
            $tableToInsert = TEMPLATE_QUESTION_TEXTBOX_TABLE;
        }

        //Insertion in the dictionary
        $toInsert = array(FRENCH_LANGUAGE=>$newTemplateQuestion["name_FR"], ENGLISH_LANGUAGE=>$newTemplateQuestion["name_EN"]);
        $dictId = $this->questionnaireDB->addToDictionary($toInsert, TEMPLATE_QUESTION_TABLE);

        //Insert data into the templateQuestion table
        $toInsert = array(
            "name"=>$dictId,
            "typeId"=>$newTemplateQuestion["typeId"],
            "private"=>$newTemplateQuestion["private"],
        );
        $templateQuestionId = $this->questionnaireDB->addToTypeTemplateTable($toInsert);

        // Insertion into the type template table.
        $options["templateQuestionId"] = $templateQuestionId;
        $parentTableId = $this->questionnaireDB->addToTypeTemplateTableType($tableToInsert, $options);

        /*
         * If the table type has a sub-table for the list of options (for example, checkbox and checkbox options),
         * insert requested data.
         * */
        if ($subTableToInsert != "") {
            foreach ($subOptions as &$opt) {
                $opt["parentTableId"] = $parentTableId;
            }
            $this->questionnaireDB->addToTypeTemplateTableTypeOptions($subTableToInsert, $subOptions);
        }
    }

    /*
     * This function returns the details of a specific question type.
     * @param   ID of the question type (int)
     * @return  array of details of the question type
     * */
    public function getTemplateQuestionDetails($templateQuestionId) {

        $templateQuestion = $this->questionnaireDB->getTemplateQuestionDetails($templateQuestionId);
        if(count($templateQuestion) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question type. Number of result is wrong.");
        $templateQuestion = $templateQuestion[0];

        $isOwner = false;
        if($this->questionnaireDB->getOAUserId() == $templateQuestion["OAUserId"])
            $isOwner = true;

        if($templateQuestion["typeId"] == SLIDERS)
            $options = $this->questionnaireDB->getQuestionSliderDetails($templateQuestion["ID"], $templateQuestion["tableName"], "templateQuestionId");
        else
            $options = $this->questionnaireDB->getQuestionOptionsDetails($templateQuestion["ID"], $templateQuestion["tableName"], "templateQuestionId");
        if (count($options) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question. Too many options.");

        $options = $options[0];
        $subOptions = null;

        if($templateQuestion["subTableName"] != "" && $options["ID"] != "") {
            $subOptions = $this->questionnaireDB->getQuestionSubOptionsDetails($options["ID"], $templateQuestion["subTableName"]);
        }

        $templateQuestion["isOwner"] = strval(intval($isOwner));
        $templateQuestion["options"] = $options;
        $templateQuestion["subOptions"] = $subOptions;
        return $templateQuestion;
    }

    /*
     * Gets a list of existing question types
     * @param integer $OAUserId : the user id
     * @return array $templateQuestions : the list of existing answer types
     */
    public function getTemplatesQuestions(){
        $templateQuestions = array();
        $result = $this->questionnaireDB->getTemplatesQuestions();

        foreach ($result as $row) {
            $temp = array(
                'ID'        => $row["ID"],
                'typeId'    => $row["typeId"],
                'name'       => $row["name"],
                'name_EN'       => $row["name_EN"],
                'name_FR'       => $row["name_FR"],
                'private'       => $row["private"],
                'category_EN'   => $row["category_EN"],
                'category_FR'   => $row["category_FR"],
                'minCaption_EN'   => $row["minCaption_EN"],
                'minCaption_FR'   => $row["minCaption_FR"],
                'maxCaption_EN'   => $row["maxCaption_EN"],
                'maxCaption_FR'   => $row["maxCaption_FR"],
                'created_by'    => $row["created_by"],
                'minValue'      => $row["minValue"],
                'maxValue'      => $row["maxValue"],
                'increment'     => $row["increment"],
            );

            // if the table has a subtable, returns its options
            if($row["subTableName"] != "") {
                $temp["options"] = $this->questionnaireDB->getTemplateQuestionsOptions($row["ID"], $row["tableName"], $row["subTableName"]);
            }
            array_push($templateQuestions, $temp);
        }
        return $templateQuestions;
    }

    /*
     * Gets a list of answer type categories
     * @return array $answerTypeCategories : the list of answer type categories
     */
    public function getTemplateQuestionList(){
        return $this->questionnaireDB->getTemplateQuestionList();
    }

    /**
     * Mark a question type as deleted. First, it get the last time it was updated, check if the user has the proper
     * authorization. Then it checked if the record was updated in the meantime, and if not, it marks the question as
     * being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  if the user has the proper authorization and no more than one user is doing modification
     * on it at a specific moment. Not following the proper procedure will have some serious impact on the integrity
     * of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionId (ID of the question)
     * @return array $response : response
     */
    function deleteTemplateQuestion($templateQuestionId) {
        $templateQuestionToDelete = $this->questionnaireDB->getTypeTemplate($templateQuestionId);


        if ($this->questionnaireDB->getOAUserId() <= 0 || $templateQuestionToDelete["deleted"] == 1 || ($templateQuestionToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $templateQuestionToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(TEMPLATE_QUESTION_TABLE, $templateQuestionId);
        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(TEMPLATE_QUESTION_TABLE, $templateQuestionId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);

        $nobodyUpdated = intval($nobodyUpdated["total"]);
        if ($nobodyUpdated){
            $this->questionnaireDB->markAsDeletedInDictionary($templateQuestionToDelete["name"]);
            $this->questionnaireDB->markAsDeleted(TEMPLATE_QUESTION_TABLE, $templateQuestionId);
            $response['value'] = true; // Success
            $response['message'] = 200;
            return $response;
        }
        else {
            $response['value'] = false; // conflict error. Somebody already updated the question or record does not exists.
            $response['message'] = 409;
            return $response;
        }
    }
}

?>