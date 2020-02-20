<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:47 AM
 */

class PublishedQuestionnaire extends Questionnaire {

//    public function __construct($OAUserId = false, $sessionId = false) {
//        parent::__construct($OAUserId);
//
//        $this->opalDB->setSessionId($sessionId);
//    }

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
     * Get details of a published questionnaire
     * @params  ID of the requested published questionnaire
     * @return  array with all the details
     * */
    public function getPublishedQuestionnaireDetails($id) {
        $occurrenceArray = array(
            'start_date' => null,
            'end_date' => null,
            'set' => 0,
            'frequency' => array (
                'custom' => 0,
                'meta_key' => null,
                'meta_value' => null,
                'additionalMeta' => array()
            )
        );

        $publishedQuestionnaire = $this->opalDB->getPublishedQuestionnaireDetails(strip_tags($id));

        if(count($publishedQuestionnaire) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid published questionnaire");

        $publishedQuestionnaire = $publishedQuestionnaire[0];
        $questionnaireTriggers = $this->opalDB->getPublishedQuestionnaireTriggers($id);
        $publishedQuestionnaire["triggers"] = $questionnaireTriggers;
        $frequencyEvents = $this->opalDB->getPublishedQuestionnaireFrequencyEvents($id);

        foreach($frequencyEvents as $data) {
            // if we've entered, then a frequency has been set
            $occurrenceArray['set'] = 1;

            $customFlag     = $data["CustomFlag"];
            // the type of meta key and which content it belongs to is separated by the | delimeter
            list($metaKey, $dontNeed) = explode('|', $data["MetaKey"]);
            $metaValue      = $data["MetaValue"];

            if ($metaKey == 'repeat_start') {
                $occurrenceArray['start_date'] = $metaValue;
            }
            else if ($metaKey == 'repeat_end') {
                $occurrenceArray['end_date'] = $metaValue;
            }
            // custom non-additional meta (eg. repeat_day, repeat_week ... any meta with one underscore that was custom made)
            else if ($customFlag == 1 and count(explode('_', $metaKey)) == 2) {
                $occurrenceArray['frequency']['custom'] = 1;
                $occurrenceArray['frequency']['meta_key'] = $metaKey;
                $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
            }
            // additional meta (eg. repeat_day_iw, repeat_week_im ... any meta with two underscores)
            else if ($customFlag == 1 and count(explode('_', $metaKey)) == 3) {
                $occurrenceArray['frequency']['custom'] = 1;
                $occurrenceArray['frequency']['additionalMeta'][$metaKey] = array_map('intval', explode(',', $metaValue));
                sort($occurrenceArray['frequency']['additionalMeta'][$metaKey]);
            }
            else { // should only be one predefined frequency chosen, if chosen
                $occurrenceArray['frequency']['meta_key'] = $metaKey;
                $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
            }
        }

        $publishedQuestionnaire["occurrence"] = $occurrenceArray;
        return $publishedQuestionnaire;
    }

    /*
     * Validate and sanitize the list of published flags for questionnaire
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

    /*
     * Recursive function that sanitize the data
     * @params  array to sanitize
     * @return  array sanitized
     * */
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

    /*
     * This function search specific ID and type in an array
     * @params  ID and types to search in an array
     * return   boolean
     * */
    protected function _nestedSearch($id, $type, $array) {
        if(empty($array) || !$id || !$type)
            return false;
        foreach ($array as $key => $val)
            if ($val['id'] === $id and $val['type'] === $type)
                return true;
        return false;
    }

    /*
     * Updates the triggers and filters of published questionnaires.
     * @params  Array of triggers and settings
     * @return  void
     * */
    function updatePublishedQuestionnaire($questionnaire) {
        $questionnaire = $this->validateAndSanitize($questionnaire);
        $toUpdate = array(
            "QuestionnaireName_EN"=>$questionnaire["name_EN"],
            "QuestionnaireName_FR"=>$questionnaire["name_FR"],
            "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
            "SessionId"=>$questionnaire["sessionId"],
            "QuestionnaireControlSerNum"=>$questionnaire["serial"],
        );
        $total = $this->opalDB->updateQuestionnaireControl($toUpdate);

        //Delete and update triggers
        if(!empty($questionnaire["triggers_updated"])) {
            $existingTriggers = $this->opalDB->getFiltersByControlTableSerNum($questionnaire["serial"], "LegacyQuestionnaireControl");
            foreach($existingTriggers as $trigger) {
                if(!$this->_nestedSearch($trigger["id"], $trigger["type"], $questionnaire["triggers"])) {
                    $total += $this->opalDB->deleteFilters($trigger["id"], $trigger["type"], $questionnaire["serial"], "LegacyQuestionnaireControl");
                    $toUpdate = array(
                        "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                        "SessionId"=>$questionnaire["sessionId"],
                        "FilterId"=>$trigger["id"],
                        "FilterType"=>$trigger["type"],
                        "ControlTableSerNum"=>$questionnaire["serial"],
                        "ControlTable"=>"LegacyQuestionnaireControl",
                    );
                    $total += $this->opalDB->updateFiltersModificationHistory($toUpdate);
                }
            }
        }

        //Add new triggers
        if(!empty($questionnaire["triggers"])) {
            $toInsert = array();
            foreach($questionnaire["triggers"] as $trigger) {
                if (!$this->_nestedSearch($trigger["id"], $trigger["type"], $existingTriggers))
                    array_push($toInsert, array(
                        "ControlTable"=>"LegacyQuestionnaireControl",
                        "ControlTableSerNum"=>$questionnaire["serial"],
                        "FilterType"=>$trigger['type'],
                        "FilterId"=>$trigger['id'],
                        "DateAdded"=>date("Y-m-d H:i:s"),
                        "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                        "SessionId"=>$questionnaire["sessionId"],
                    ));
            }
            $this->opalDB->insertMultipleFilters($toInsert);
        }

        if(!$questionnaire["occurrence"]["set"]) {
            $total += $this->opalDB->deleteFrequencyEvent($questionnaire["serial"], "LegacyQuestionnaireControl");
        }
        else {
            $toInsert = array(
                "ControlTable"=>'LegacyQuestionnaireControl',
                "ControlTableSerNum"=>$questionnaire["serial"],
                "MetaKey"=>'repeat_start',
                "MetaValue"=>$questionnaire["occurrence"]["start_date"],
                "CustomFlag"=>'0',
                "DateAdded"=>date("Y-m-d H:i:s"),
            );
            $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
            if(!$questionnaire["occurrence"]["end_date"]) {
                $result = $this->opalDB->deleteRepeatEndFromFrequencyEvents($questionnaire["serial"], "LegacyQuestionnaireControl");
            }
            else {
                $toInsert = array(
                    "ControlTable" => 'LegacyQuestionnaireControl',
                    "ControlTableSerNum" => $questionnaire["serial"],
                    "MetaKey" => 'repeat_end',
                    "MetaValue" => $questionnaire["occurrence"]["end_date"],
                    "CustomFlag" => '0',
                    "DateAdded" => date("Y-m-d H:i:s"),
                );
                $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
            }

            $result = $this->opalDB->deleteOtherMetasFromFrequencyEvents($questionnaire["serial"], "LegacyQuestionnaireControl");
            $toInsert = array(
                "ControlTable"=>'LegacyQuestionnaireControl',
                "ControlTableSerNum"=>$questionnaire["serial"],
                "MetaKey"=>$questionnaire['occurrence']['frequency']['meta_key']."|lqc_".$questionnaire["serial"],
                "MetaValue"=>$questionnaire['occurrence']['frequency']['meta_value'],
                "CustomFlag"=>$questionnaire['occurrence']['frequency']['custom'],
                "DateAdded"=>date("Y-m-d H:i:s"),
            );
            $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);

            if(!empty($questionnaire['occurrence']['frequency']['additionalMeta'])) {
                foreach($questionnaire['occurrence']['frequency']['additionalMeta'] as $meta) {
                    $toInsert = array(
                        "ControlTable"=>'LegacyQuestionnaireControl',
                        "ControlTableSerNum"=>$questionnaire["serial"],
                        "MetaKey"=>$meta['meta_key']."|lqc_".$questionnaire["serial"],
                        "MetaValue"=>implode(',', $meta['meta_value']),
                        "CustomFlag"=>'1',
                        "DateAdded"=>date("Y-m-d H:i:s"),
                    );
                    $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
                }
            }

        }
    }
}