<?php

/**
 *
 * Questionnaire-AnswerType class
 */
class QuestionType {

    protected $questionnaireDB;
    protected $opalDB;

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

    protected function setUserInfo($userId) {
        $userInfo = $this->opalDB->getUserInfo($userId);
        $this->opalDB->setUserId($userInfo["userId"]);
        $this->opalDB->setUsername($userInfo["username"]);
        $this->questionnaireDB->setUserId($userInfo["userId"]);
        $this->questionnaireDB->setUsername($userInfo["username"]);
    }

    /*
     * Function to sort options by their order value. Only being used when a question type has a list of options to sort
     * */
    protected static function sort_order($a, $b){
        if (intval($a["order"]) == intval($b["order"])) return 0;
        return (intval($a["order"]) < intval($b["order"])) ? -1 : 1;
    }

    /*
     * Inserts a new question type. First, it sanitizes all the data. Then it inserts the question text into the
     * dictionary. Third, it inserts into the question table, and lastly it inserts in the correct question type
     * options table if it necessary.
     * @param array $newQuestionType : the question type to be inserted
     * @return void
     */
    public function insertQuestionType($newQuestionType){

        /*
         * Sanitization of the data
         * */
        $typeId = strip_tags($newQuestionType["typeId"]);
        $nameEn = strip_tags($newQuestionType["name_EN"]);
        $nameFr = strip_tags($newQuestionType["name_FR"]);
        $private = strip_tags($newQuestionType["private"]);
        $options = array();
        $optionToInsert = array();
        if($typeId == CHECKBOXES) {
            $tableToInsert = TYPE_TEMPLATE_CHECKBOX_TABLE;
            $subTableToInsert = TYPE_TEMPLATE_CHECKBOX_OPTION_TABLE;

            foreach($newQuestionType["options"] as $opt) {
                $temp = array();
                $toInsert = array(FRENCH_LANGUAGE=>strip_tags($opt["text_FR"]), ENGLISH_LANGUAGE=>strip_tags($opt["text_EN"]));
                $temp["description"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
                $temp["order"] = strip_tags($opt["position"]);
                array_push($options, $temp);
            }
            usort($options, 'self::sort_order');
            $cpt = 0;
            foreach($options as &$row) {
                $cpt++;
                $row["order"] = $cpt;
            }

            $optionToInsert["minAnswer"]= 1;
            $optionToInsert["maxAnswer"] = count($options);
        }
        else if ($typeId == RADIO_BUTTON) {
            $tableToInsert = TYPE_TEMPLATE_RADIO_BUTTON_TABLE;
            $subTableToInsert = TYPE_TEMPLATE_RADIO_BUTTON_OPTION_TABLE;

            foreach($newQuestionType["options"] as $opt) {
                $temp = array();
                $toInsert = array(FRENCH_LANGUAGE=>strip_tags($opt["text_FR"]), ENGLISH_LANGUAGE=>strip_tags($opt["text_EN"]));
                $temp["description"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
                $temp["order"] = strip_tags($opt["position"]);
                array_push($options, $temp);
            }
            usort($options, 'self::sort_order');
            $cpt = 0;
            foreach($options as &$row) {
                $cpt++;
                $row["order"] = $cpt;
            }
        }
        else if ($typeId == SLIDERS) {
            $tableToInsert = TYPE_TEMPLATE_SLIDER_TABLE;
            $minValue = floatval(strip_tags($newQuestionType["minValue"]));
            $minCaptionEn = strip_tags($newQuestionType["MinCaption_EN"]);
            $minCaptionFr = strip_tags($newQuestionType["MinCaption_FR"]);
            $maxValue = floatval(strip_tags($newQuestionType["maxValue"]));
            $maxCaptionEn = strip_tags($newQuestionType["MaxCaption_EN"]);
            $maxCaptionFr = strip_tags($newQuestionType["MaxCaption_FR"]);
            $increment = floatval(strip_tags($newQuestionType["increment"]));

            if ($increment <= 0 || $minValue <= 0 || $maxValue <= $minValue) {
                header('Content-Type: application/javascript');
                $response['value'] = false;
                $response['message'] = 500;
                $response['details'] = "Invalid slider format. " . $newQuestionType["minValue"];
                print_R($_POST);
                echo json_encode($response);
                die();
            }

            $maxValue = floatval(floor(($maxValue - $minValue) / $increment) * $increment) + $minValue;

            $toInsert = array(
                FRENCH_LANGUAGE=>$minCaptionFr, ENGLISH_LANGUAGE=>$minCaptionEn
            );
            $optionToInsert["minCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $toInsert = array(
                FRENCH_LANGUAGE=>$maxCaptionFr, ENGLISH_LANGUAGE=>$maxCaptionEn
            );
            $optionToInsert["maxCaption"] = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);
            $optionToInsert["minValue"] = $minValue;
            $optionToInsert["maxValue"] = $maxValue;
            $optionToInsert["increment"] = $increment;
        }
        else {
            $typeId = TEXT_BOX;
            $tableToInsert = TYPE_TEMPLATE_TEXTBOX_TABLE;
        }

        /*
         * Insertion in the dictionary
         * */
        $toInsert = array(FRENCH_LANGUAGE=>$nameFr, ENGLISH_LANGUAGE=>$nameEn);
        $dictId = $this->questionnaireDB->addToDictionary($toInsert, TYPE_TEMPLATE_TABLE);

        /*
         * Insert data into the typeTemplate table
         * */
        $toInsert = array(
            "name"=>$dictId,
            "typeId"=>$typeId,
            "private"=>$private,
        );
        $questionTypeId = $this->questionnaireDB->addToTypeTemplateTable($toInsert);

        /*
         * Insertion into the type template table.
         * */
        $optionToInsert["typeTemplateId"] = $questionTypeId;
        $parentTableId = $this->questionnaireDB->addToTypeTemplateTableType($tableToInsert, $optionToInsert);

        /*
         * If the table type has a sub-table for the list of options (for example, checkbox and checkbox options),
         * insert request data.
         * */
        if ($subTableToInsert != "") {
            foreach ($options as &$opt) {
                $opt["parentTableId"] = $parentTableId;
            }
            $this->questionnaireDB->addToTypeTemplateTableTypeOptions($subTableToInsert, $options);
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
    public function getQuestionTypeCategories(){
        return $this->questionnaireDB->getQuestionTypeCategories();
    }
}

?>