<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:47 AM
 */

class PublishedQuestionnaire extends Questionnaire {

    public function __construct($OAUserId = false, $sessionId = false) {
        parent::__construct($OAUserId);

        $this->opalDB->setSessionId($sessionId);
    }

    /*
     * This function returns all the published questionnaires from the Questionnaire Control
     * @params  void
     * @return  array of published questionnaires
     * */
    public function getPublishedQuestionnaires() {
        $occurrenceArray = array(
            'start_date' => null,
            'end_date' => null,
            'set' => 0,
            'frequency' => array (
                'meta_key' => null,
                'meta_value' => null,
                'additionalMeta' => array()
            )
        );

        $publishedQuestionnaires = $this->opalDB->getPublishedQuestionnaires();
        foreach($publishedQuestionnaires as &$row) {
            $titles = $this->questionnaireDB->getQuestionnaireNames($row["db_serial"]);
            $row["expression_EN"] = $titles["title_EN"];
            $row["expression_FR"] = $titles["title_FR"];
            $row["triggers"] = $this->opalDB->getFilters($row["serial"]);
            $row["occurrence"] = $occurrenceArray;
        }
        return $publishedQuestionnaires;
    }

    /*
     * Validate and saniteze the list of published flags for questionnaire
     * @params  array of questionnaire to mark as published or not ($_POST)
     * @return  array of sanitize data
     * */

    function validateAndSanitizePublicationList($toValidate) {
        $validatedList = array();
        foreach($toValidate as $questionnaire) {
            $id = trim(strip_tags($questionnaire["serial"]));
            $publish = intval(trim(strip_tags($questionnaire["publish"])));
            if ($publish != 0 && $publish != 1)
                $publish = 0;
            array_push($validatedList, array("serial"=>$id, "publish"=>$publish));
        }
        return $validatedList;
    }

    /*
     * Update the status of a series of questionnaires (published = 1 / unpublished = 0)
     * @params  array of ID with the publication flag
     *          for example:    array(
     *                              array("serial"=>1, "publish"=>0),
     *                              array("serial"=>2, "publish"=>1),
     *                              array("serial"=>3, "publish"=>0),
     *                              ...
     *                          )
     * @return  void
     * */
    function updatePublicationFlags($list) {
        foreach($list as $row) {
            $this->opalDB->updatePublicationFlags($row["serial"], $row["publish"]);
        }
    }

    function validateAndSanitize($arrayForm) {
        $sanitizedArray = array();
        foreach($arrayForm as $key=>$value) {
            $key = strip_tags($key);
            if(is_array($value))
                $value = $this->validateAndSanitize($value);
            else
                $value = strip_tags($value);
            $sanitizedArray[$key] = $value;
        }
        return $sanitizedArray;
    }

    /*
     * Insert a questionnaire ready to be published in the questionnaire control, filter and frequency events table
     * after validating and sanitizing the data.
     * @params  array of questionnaire settings and triggers
     * @return  void
     * */
    function insertPublishedQuestionnaire($questionnaire) {
        $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($questionnaire["questionnaireId"]);

        if(count($currentQuestionnaire) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");
        $currentQuestionnaire = $currentQuestionnaire[0];

        $questionnaire = $this->validateAndSanitize($questionnaire);

        $toInsert = array(
            "QuestionnaireDBSerNum"=>$currentQuestionnaire["ID"],
            "QuestionnaireName_EN"=>$questionnaire["name_EN"],
            "QuestionnaireName_FR"=>$questionnaire["name_FR"],
            "Intro_EN"=>htmlspecialchars_decode($currentQuestionnaire["description_EN"]),
            "Intro_FR"=>htmlspecialchars_decode($currentQuestionnaire["description_FR"]),
            "SessionId"=>$questionnaire["sessionId"],
            "DateAdded"=>date("Y-m-d H:i:s"),
            "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
        );

        $questionnaireControlId = $this->opalDB->insertPublishedQuestionnaire($toInsert);
        $toInsert = array();
        if(!empty($questionnaire['triggers'])) {
            foreach($questionnaire['triggers'] as $trigger) {
                array_push($toInsert, array(
                    "ControlTable"=>"LegacyQuestionnaireControl",
                    "ControlTableSerNum"=>$questionnaireControlId,
                    "FilterType"=>$trigger['type'],
                    "FilterId"=>$trigger['id'],
                    "DateAdded"=>date("Y-m-d H:i:s"),
                    "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                    "SessionId"=>$questionnaire["sessionId"],
                ));
            }
            $this->opalDB->insertMultipleFilters($toInsert);
        }

        if ($questionnaire['occurrence']['set']) {
            $toInsert = array();
            array_push($toInsert, array(
                "ControlTable"=>"LegacyQuestionnaireControl",
                "ControlTableSerNum"=>$questionnaireControlId,
                "MetaKey"=>"repeat_start",
                "MetaValue"=>$questionnaire['occurrence']['start_date'],
                "CustomFlag"=>"0",
                "DateAdded"=>date("Y-m-d H:i:s"),
            ));

            if($questionnaire['occurrence']['end_date']) {
                array_push($toInsert, array(
                    "ControlTable"=>"LegacyQuestionnaireControl",
                    "ControlTableSerNum"=>$questionnaireControlId,
                    "MetaKey"=>"repeat_end",
                    "MetaValue"=>$questionnaire['occurrence']['end_date'],
                    "CustomFlag"=>"0",
                    "DateAdded"=>date("Y-m-d H:i:s"),
                ));
            }

            array_push($toInsert, array(
                "ControlTable"=>"LegacyQuestionnaireControl",
                "ControlTableSerNum"=>$questionnaireControlId,
                "MetaKey"=>$questionnaire['occurrence']['frequency']['meta_key']."|lqc_".$questionnaireControlId,
                "MetaValue"=>$questionnaire['occurrence']['frequency']['meta_value'],
                "CustomFlag"=>$questionnaire['occurrence']['frequency']['custom'],
                "DateAdded"=>date("Y-m-d H:i:s"),
            ));

            if(!empty($questionnaire['occurrence']['frequency']['additionalMeta'])) {
                foreach($questionnaire['occurrence']['frequency']['additionalMeta'] as $meta) {
                    array_push($toInsert, array(
                        "ControlTable"=>"LegacyQuestionnaireControl",
                        "ControlTableSerNum"=>$questionnaireControlId,
                        "MetaKey"=>$meta['meta_key']."|lqc_".$questionnaireControlId,
                        "MetaValue"=>implode(',', $meta['meta_value']),
                        "CustomFlag"=>"1",
                        "DateAdded"=>date("Y-m-d H:i:s"),
                    ));
                }
            }
            $this->opalDB->insertMultipleFrequencyEvents($toInsert);
        }
    }
}