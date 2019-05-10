<?php

/**
 *
 * Questionnaire-AnswerType class
 */
class QuestionType extends QuestionnaireProject {

    public static function validateAndSanitize($newQuestionType) {
        $validatedQT = array(
            'typeId' => strip_tags($newQuestionType['ID']),
            'name_EN' => strip_tags($newQuestionType['name_EN']),
            'name_FR' => strip_tags($newQuestionType['name_FR']),
            'private' => strip_tags($newQuestionType['private']),
            'userId' => strip_tags($newQuestionType['userId']),
            'options' => $newQuestionType['options'],
        );


        if( $validatedQT["typeId"] == "" ||  $validatedQT["name_EN"] == "" ||  $validatedQT["name_FR"] == "" ||  $validatedQT["userId"] == "")
            return false;

        if ($validatedQT["typeId"] == SLIDERS)
        {
            $validatedQT["MinCaption_EN"] = strip_tags($newQuestionType["MinCaption_EN"]);
            $validatedQT["MinCaption_FR"] = strip_tags($newQuestionType["MinCaption_FR"]);
            $validatedQT["MaxCaption_EN"] = strip_tags($newQuestionType["MaxCaption_EN"]);
            $validatedQT["MaxCaption_FR"] = strip_tags($newQuestionType["MaxCaption_FR"]);
            $validatedQT["minValue"] = floatval(strip_tags($newQuestionType["minValue"]));
            $validatedQT["maxValue"] = floatval(strip_tags($newQuestionType["maxValue"]));
            $validatedQT["increment"] = floatval(strip_tags($newQuestionType["increment"]));

            if( $validatedQT["MinCaption_EN"] == "" ||  $validatedQT["MinCaption_FR"] == "" ||  $validatedQT["MaxCaption_EN"] == "" ||  $validatedQT["MaxCaption_FR"] == "" || $validatedQT["minValue"] <= 0.0 || $validatedQT["maxValue"] <= 0.0 || $validatedQT["increment"] <= 0.0 || $validatedQT["minValue"] >= $validatedQT["maxValue"])
                return false;

            $validatedQT["maxValue"] = floatval(floor(($validatedQT["maxValue"] - $validatedQT["minValue"]) / $validatedQT["increment"]) * $validatedQT["increment"]) + $validatedQT["minValue"];
        }
        else if ($validatedQT["typeId"] == CHECKBOXES || $validatedQT["typeId"] == RADIO_BUTTON) {
            if (count($validatedQT["options"]) <= 0)
                return false;
            $sanitizedOptions = array();
            foreach($validatedQT["options"] as $option) {
                $temp = array();
                $temp["text_EN"] = strip_tags($option["text_EN"]);
                $temp["text_FR"] = strip_tags($option["text_FR"]);
                $temp["order"] = strip_tags($option["order"]);
                array_push($sanitizedOptions, $temp);
            }

            self::sortOptions($sanitizedOptions);

            if ($validatedQT["typeId"] == CHECKBOXES ) {
                $validatedQT["minAnswer"] = 1;
                $validatedQT["maxAnswer"] = count($sanitizedOptions);
            }
            $validatedQT["options"] = $sanitizedOptions;
        }
        else if ($validatedQT["typeId"] != TEXT_BOX)
            return false;

        return $validatedQT;
    }

    /*
     * Inserts a new question type.
     * @param array $newQuestionType : the question type to be inserted
     * @return void
     */
    public function insertQuestionType($newQuestionType){
        $options = array();
        $subOptions = array();
        if($newQuestionType["typeId"] == CHECKBOXES) {
            $tableToInsert = TYPE_TEMPLATE_CHECKBOX_TABLE;
            $subTableToInsert = TYPE_TEMPLATE_CHECKBOX_OPTION_TABLE;

            foreach($newQuestionType["options"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["text_FR"], ENGLISH_LANGUAGE=>$opt["text_EN"]);
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

            foreach($newQuestionType["options"] as $opt) {
                $tempArray = array();
                $toInsert = array(FRENCH_LANGUAGE=>$opt["text_FR"], ENGLISH_LANGUAGE=>$opt["text_EN"]);
                $tempArray["description"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
                $tempArray["order"] = $opt["order"];
                array_push($subOptions, $tempArray);
            }
        }
        else if ($newQuestionType["typeId"] == SLIDERS) {
            $tableToInsert = TYPE_TEMPLATE_SLIDER_TABLE;
            $toInsert = array(
                FRENCH_LANGUAGE=>$newQuestionType["MinCaption_FR"], ENGLISH_LANGUAGE=>$newQuestionType["MinCaption_EN"]
            );
            $options["minCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $toInsert = array(
                FRENCH_LANGUAGE=>$newQuestionType["MaxCaption_FR"], ENGLISH_LANGUAGE=>$newQuestionType["MaxCaption_EN"]
            );
            $options["maxCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $options["minValue"] = $newQuestionType["minValue"];
            $options["maxValue"] = $newQuestionType["maxValue"];
            $options["increment"] = $newQuestionType["increment"];
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
     * Gets a list of existing question types
     * @param integer $userId : the user id
     * @return array $questionTypes : the list of existing answer types
     */
    public function getQuestionTypes(){
        $questionTypes = array();
        $result = $this->questionnaireDB->getQuestionTypes();

        foreach ($result as $row) {
            $temp = array(
                'serNum'        => $row["serNum"],
                'typeSerNum'    => $row["typeSerNum"],
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

                $temp["options"] = $this->questionnaireDB->getQuestionTypesOptions($row["serNum"], $row["tableName"], $row["subTableName"]);
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
}

?>