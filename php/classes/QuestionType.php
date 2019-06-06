<?php

/**
 *
 * Questionnaire-AnswerType class
 */
class QuestionType extends QuestionnaireModule {

    const PIVOTAL_TYPE_TEMPLATE_FIELDS = array("ID", "name", "typeId");
    const PIVOTAL_TYPE_TEMPLATE_OPTIONS_FIELDS = array("ID", "typeTemplateId");
    const PIVOTAL_TYPE_TEMPLATE_OPTIONS_SLIDERS_FIELDS = array("ID", "typeTemplateId", "minCaption", "maxCaption");
    const PIVOTAL_TYPE_TEMPLATE_SUB_OPTIONS_FIELDS = array("ID", "parentTableId", "description");

    public function validateAndSanitize($newQuestionType) {
        $validatedQT = array(
            'typeId' => strip_tags($newQuestionType['typeId']),
            'name_EN' => strip_tags($newQuestionType['name_EN']),
            'name_FR' => strip_tags($newQuestionType['name_FR']),
            'private' => strip_tags($newQuestionType['private']),
            'OAUserId' => strip_tags($newQuestionType['OAUserId']),
            'options' => $newQuestionType['options'],
        );

        if ($newQuestionType["ID"] != "")
            $validatedQT["ID"] = strip_tags($newQuestionType['ID']);

        if( $validatedQT["typeId"] == "" ||  $validatedQT["name_EN"] == "" ||  $validatedQT["name_FR"] == "" ||  $validatedQT["OAUserId"] == "")
            return false;


        $options = array();
        if(!empty($newQuestionType["options"]))
            foreach($newQuestionType["options"] as $key=>$value)
                if ($key != '$$hashKey')
                    $options[strip_tags($key)] = strip_tags($value);
        $validatedQT["options"] = $options;


        $subOptions = array();

        if(!empty($newQuestionType["subOptions"])) {
            foreach ($newQuestionType["subOptions"] as $aSub) {
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
    function validatePivotalIDs(&$updatedQuestion, &$oldQuestion) {
        $answer = true;
        $arrayOldOption = array();

        if(!empty($oldQuestion["subOptions"])) {
            foreach ($oldQuestion["subOptions"] as $options)
                $arrayOldOption[$options["ID"]] = $options;
        }

        foreach($updatedQuestion as $key=>$value)
            if(in_array($key, self::PIVOTAL_TYPE_TEMPLATE_FIELDS) && $oldQuestion[$key] != $value) {
                $answer = false;
                break;
            }

        if($oldQuestion["typeId"] == SLIDERS) {
            $updatedQuestion["subOptions"] = array();
            $fieldLists = self::PIVOTAL_TYPE_TEMPLATE_OPTIONS_SLIDERS_FIELDS;
        }
        else
            $fieldLists = self::PIVOTAL_TYPE_TEMPLATE_OPTIONS_FIELDS;

        foreach($updatedQuestion["options"] as $key=>$value) {
            if(in_array($key, $fieldLists) && $oldQuestion["options"][$key] !== $value) {
                $answer = false;
                break;
            }
        }

        foreach($updatedQuestion["subOptions"] as $sub) {
            $tempId = $sub["ID"];
            foreach ($sub as $key => $value) {
                if (in_array($key, self::PIVOTAL_TYPE_TEMPLATE_SUB_OPTIONS_FIELDS) && $value !== $arrayOldOption[$tempId][$key]) {
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
    function updateQuestionType($updatedQuestionType) {

    }

    /*
     * Inserts a new question type.
     * @param array $newQuestionType : the question type to be inserted
     * @return void
     */
    public function insertQuestionType($newQuestionType){

        $newQuestionType = $this->validateAndSanitize($newQuestionType);

        if(!$newQuestionType)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

        $options = array();
        $subOptions = array();
        if($newQuestionType["typeId"] == CHECKBOXES) {
            $tableToInsert = TYPE_TEMPLATE_CHECKBOX_TABLE;
            $subTableToInsert = TYPE_TEMPLATE_CHECKBOX_OPTION_TABLE;

            foreach($newQuestionType["subOptions"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["description_FR"], ENGLISH_LANGUAGE=>$opt["description_EN"]);
                $tempArray["description"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
                $tempArray["order"] = $opt["order"];
                array_push($subOptions, $tempArray);
            }
            $options["minAnswer"]= 1;
            $options["maxAnswer"] = count($subOptions);
        }
        else if ($newQuestionType["typeId"] == RADIO_BUTTON) {
            $tableToInsert = TYPE_TEMPLATE_RADIO_BUTTON_TABLE;
            $subTableToInsert = TYPE_TEMPLATE_RADIO_BUTTON_OPTION_TABLE;

            foreach($newQuestionType["subOptions"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["description_FR"], ENGLISH_LANGUAGE=>$opt["description_EN"]);
                $tempArray["description"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
                $tempArray["order"] = $opt["order"];
                array_push($subOptions, $tempArray);
            }
        }
        else if ($newQuestionType["typeId"] == SLIDERS) {
            $tableToInsert = TYPE_TEMPLATE_SLIDER_TABLE;
            $toInsert = array(
                FRENCH_LANGUAGE=>$newQuestionType["options"]["minCaption_FR"], ENGLISH_LANGUAGE=>$newQuestionType["options"]["minCaption_EN"]
            );
            $options["minCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $toInsert = array(
                FRENCH_LANGUAGE=>$newQuestionType["options"]["maxCaption_FR"], ENGLISH_LANGUAGE=>$newQuestionType["options"]["maxCaption_EN"]
            );
            $options["maxCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $options["minValue"] = $newQuestionType["options"]["minValue"];
            $options["maxValue"] = $newQuestionType["options"]["maxValue"];
            $options["increment"] = $newQuestionType["options"]["increment"];
        }
        else {
            $newQuestionType["typeId"] = TEXT_BOX;
            $tableToInsert = TYPE_TEMPLATE_TEXTBOX_TABLE;
        }

        //Insertion in the dictionary
        $toInsert = array(FRENCH_LANGUAGE=>$newQuestionType["name_FR"], ENGLISH_LANGUAGE=>$newQuestionType["name_EN"]);
        $dictId = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);

        //Insert data into the typeTemplate table
        $toInsert = array(
            "name"=>$dictId,
            "typeId"=>$newQuestionType["typeId"],
            "private"=>$newQuestionType["private"],
        );
        $questionTypeId = $this->questionnaireDB->addToTypeTemplateTable($toInsert);

        // Insertion into the type template table.
        $options["typeTemplateId"] = $questionTypeId;
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
    public function getQuestionTypeDetails($questionTypeId) {

        $questionType = $this->questionnaireDB->getQuestionTypeDetails($questionTypeId);
        if(count($questionType) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question type. Number of result is wrong.");
        $questionType = $questionType[0];

        $isOwner = false;
        if($this->questionnaireDB->getOAUserId() == $questionType["OAUserId"])
            $isOwner = true;

        if($questionType["typeId"] == SLIDERS)
            $options = $this->questionnaireDB->getQuestionSliderDetails($questionType["ID"], $questionType["tableName"], "typeTemplateId");
        else
            $options = $this->questionnaireDB->getQuestionOptionsDetails($questionType["ID"], $questionType["tableName"], "typeTemplateId");
        if (count($options) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Errors fetching the question. Too many options.");

        $options = $options[0];
        $subOptions = null;

        if($questionType["subTableName"] != "" && $options["ID"] != "") {
            $subOptions = $this->questionnaireDB->getQuestionSubOptionsDetails($options["ID"], $questionType["subTableName"]);
        }

        $questionType["isOwner"] = strval(intval($isOwner));
        $questionType["options"] = $options;
        $questionType["subOptions"] = $subOptions;
        return $questionType;
    }

    /*
     * Gets a list of existing question types
     * @param integer $OAUserId : the user id
     * @return array $questionTypes : the list of existing answer types
     */
    public function getQuestionTypes(){
        $questionTypes = array();
        $result = $this->questionnaireDB->getQuestionTypes();

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
                $temp["options"] = $this->questionnaireDB->getQuestionTypesOptions($row["ID"], $row["tableName"], $row["subTableName"]);
            }
            array_push($questionTypes, $temp);
        }
        return $questionTypes;
    }

    /*
     * Gets a list of answer type categories
     * @return array $answerTypeCategories : the list of answer type categories
     */
    public function getQuestionTypeList(){
        return $this->questionnaireDB->getQuestionTypeList();
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
    function deleteQuestionType($questionTypeId) {
        $questionTypeToDelete = $this->questionnaireDB->getTypeTemplate($questionTypeId);


        if ($this->questionnaireDB->getOAUserId() <= 0 || $questionTypeToDelete["deleted"] == 1 || ($questionTypeToDelete["private"] == 1 && $this->questionnaireDB->getOAUserId() != $questionTypeToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(TYPE_TEMPLATE_TABLE, $questionTypeId);
        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(TYPE_TEMPLATE_TABLE, $questionTypeId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);

        $nobodyUpdated = intval($nobodyUpdated["total"]);
        if ($nobodyUpdated){
            $this->questionnaireDB->markAsDeletedInDictionary($questionTypeToDelete["name"]);
            $this->questionnaireDB->markAsDeleted(TYPE_TEMPLATE_TABLE, $questionTypeId);
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