<?php

class Publication extends OpalProject
{
    protected $questionnaireDB;

    /*
     * This function connects to to questionnaire database if needed
     * @params  $OAUserId (ID of the user)
     * @returns None
     * */
    protected function _connectQuestionnaireDB($OAUserId) {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            $OAUserId
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    /*
     * Return the list of all available publications
     * params   none
     * returns  array of data
     * */
    public function getPublications() {
        return $this->opalDB->getPublications();
    }

    /*
     * Get the list of materials that can be published based on the module request
     * params   module ID
     * returns  array of data
     * */
    public function getPublicationsPerModule($moduleId) {
        if($moduleId == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Module cannot be found. Access denied.");
        $results = $this->opalDB->getPublicationsPerModule($moduleId);
        $tempArray = array();
        foreach($results["triggers"] as $trigger)
            array_push($tempArray, $trigger["publicationSettingId"]);
        $results["triggers"] = $tempArray;
        return $results;
    }

    public function getPublicationChartLogs() {
        $result = $this->opalDB->getPublicationChartLogs();

        $arrResult = array();
        $tempResult = array();

        $currentModule = "-1";
        $currentID = "-1";
        foreach($result as $row) {
            // print_r($row); print "<br/><br/>";
            if($currentModule != $row["moduleId"] || $currentID != $row["ID"]) {
                if (!empty($tempResult))
                    array_push($arrResult, array("name"=>$row["name_EN"], "data"=>$tempResult));
                $tempResult = array();
                $currentModule = $row["moduleId"];
                $currentID = $row["ID"];
            }
            array_push($tempResult, array("x"=>$row["x"],"y"=>$row["y"],"cron_serial"=>$row["cron_serial"]));
        }
        array_push($arrResult, array("name"=>$row["name_EN"], "data"=>$tempResult));
        return $arrResult;
    }

    /*
     * Validate and sanitize the list of publish flag of publications
     * @params  array of publications to mark as published or not ($_POST)
     * @return  array of sanitize data
     * */
    function validateAndSanitizePublicationList($toValidate) {
        $validatedList = array();
        foreach($toValidate as $item) {
            $id = trim(strip_tags($item["ID"]));
            $publication = trim(strip_tags($item["moduleId"]));
            $publishFlag = intval(trim(strip_tags($item["publishFlag"])));
            if (publishFlag != 0 && publishFlag != 1)
                $publishFlag = 0;
            array_push($validatedList, array("ID"=>$id, "moduleId"=>$publication, "publishFlag"=>$publishFlag));
        }
        return $validatedList;
    }

    /*
     * Update the status of a series of publications (published = 1 / unpublished = 0)
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
        $publicationModules = $this->opalDB->getPublicationModules();
        foreach($list as $row) {
            foreach($publicationModules as $module) {
                if ($module["ID"] == $row["moduleId"]) {
                    $this->opalDB->updatePublicationFlag($module["tableName"], $module["primaryKey"], $row["publishFlag"], $row["ID"]);
                    break;
                }
            }
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
    function insertPublication($publication) {
        $publicationControlId = "-1";
        $publication = $this->validateAndSanitize($publication);

        print_r($publication);

        $moduleDetails = $this->opalDB->getPublicationModuleUserDetails($publication["moduleId"]);

        if($moduleDetails["ID"] == MODULE_QUESTIONNAIRE) {
            print "questionnaire goes here\r\n";

            $this->_connectQuestionnaireDB($this->opalDB->getOAUserId());
            $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($publication["publicationId"]);
            if(count($currentQuestionnaire) != 1)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");
            $currentQuestionnaire = $currentQuestionnaire[0];


            $toInsert = array(
                "QuestionnaireDBSerNum"=>$currentQuestionnaire["ID"],
                "QuestionnaireName_EN"=>$publication["name_EN"],
                "QuestionnaireName_FR"=>$publication["name_FR"],
                "Intro_EN"=>htmlspecialchars_decode($currentQuestionnaire["description_EN"]),
                "Intro_FR"=>htmlspecialchars_decode($currentQuestionnaire["description_FR"]),
                "SessionId"=>$this->opalDB->getSessionId(),
                "DateAdded"=>date("Y-m-d H:i:s"),
                "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
            );

            print_r($toInsert);

            $controlTable = "LegacyQuestionnaireControl";
            //$publicationControlId = $this->opalDB->insertPublishedQuestionnaire($toInsert);

        }
        else if($moduleDetails["ID"] == MODULE_POST) {
            $controlTable = OPAL_POST_TABLE;
        }
        else if($moduleDetails["ID"] == MODULE_EDU_MAT) {
            $controlTable = OPAL_EDUCATION_MATERIAL_TABLE;
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module");





        $toInsertTriggers = array();
        if(!empty($publication['triggers'])) {
            foreach($publication['triggers'] as $trigger) {
                array_push($toInsertTriggers, array(
                    "ControlTable"=>$controlTable,
                    "ControlTableSerNum"=>$publicationControlId,
                    "FilterType"=>$trigger['type'],
                    "FilterId"=>$trigger['id'],
                    "DateAdded"=>date("Y-m-d H:i:s"),
                    "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                    "SessionId"=>$this->opalDB->getSessionId(),
                ));
            }
            print_r($toInsertTriggers);
            //$this->opalDB->insertMultipleFilters($toInsertTriggers);
        }



        die();


        print_r($publication);
        print_r($moduleDetails);

        return false;

        /* OLD PUBLICATION TOOL FROM QUESTIONNAIRE */
        $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($publication["questionnaireId"]);

        if(count($currentQuestionnaire) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");
        $currentQuestionnaire = $currentQuestionnaire[0];

        $toInsert = array(
            "QuestionnaireDBSerNum"=>$currentQuestionnaire["ID"],
            "QuestionnaireName_EN"=>$publication["name_EN"],
            "QuestionnaireName_FR"=>$publication["name_FR"],
            "Intro_EN"=>htmlspecialchars_decode($currentQuestionnaire["description_EN"]),
            "Intro_FR"=>htmlspecialchars_decode($currentQuestionnaire["description_FR"]),
            "SessionId"=>$publication["sessionId"],
            "DateAdded"=>date("Y-m-d H:i:s"),
            "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
        );

        $publicationControlId = $this->opalDB->insertPublishedQuestionnaire($toInsert);
        $toInsert = array();
        if(!empty($publication['triggers'])) {
            foreach($publication['triggers'] as $trigger) {
                array_push($toInsert, array(
                    "ControlTable"=>"LegacyQuestionnaireControl",
                    "ControlTableSerNum"=>$publicationControlId,
                    "FilterType"=>$trigger['type'],
                    "FilterId"=>$trigger['id'],
                    "DateAdded"=>date("Y-m-d H:i:s"),
                    "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                    "SessionId"=>$publication["sessionId"],
                ));
            }
            $this->opalDB->insertMultipleFilters($toInsert);
        }

        if ($publication['occurrence']['set']) {
            $toInsert = array();
            array_push($toInsert, array(
                "ControlTable"=>"LegacyQuestionnaireControl",
                "ControlTableSerNum"=>$publicationControlId,
                "MetaKey"=>"repeat_start",
                "MetaValue"=>$publication['occurrence']['start_date'],
                "CustomFlag"=>"0",
                "DateAdded"=>date("Y-m-d H:i:s"),
            ));

            if($publication['occurrence']['end_date']) {
                array_push($toInsert, array(
                    "ControlTable"=>"LegacyQuestionnaireControl",
                    "ControlTableSerNum"=>$publicationControlId,
                    "MetaKey"=>"repeat_end",
                    "MetaValue"=>$publication['occurrence']['end_date'],
                    "CustomFlag"=>"0",
                    "DateAdded"=>date("Y-m-d H:i:s"),
                ));
            }

            array_push($toInsert, array(
                "ControlTable"=>"LegacyQuestionnaireControl",
                "ControlTableSerNum"=>$publicationControlId,
                "MetaKey"=>$publication['occurrence']['frequency']['meta_key']."|lqc_".$publicationControlId,
                "MetaValue"=>$publication['occurrence']['frequency']['meta_value'],
                "CustomFlag"=>$publication['occurrence']['frequency']['custom'],
                "DateAdded"=>date("Y-m-d H:i:s"),
            ));

            if(!empty($publication['occurrence']['frequency']['additionalMeta'])) {
                foreach($publication['occurrence']['frequency']['additionalMeta'] as $meta) {
                    array_push($toInsert, array(
                        "ControlTable"=>"LegacyQuestionnaireControl",
                        "ControlTableSerNum"=>$publicationControlId,
                        "MetaKey"=>$meta['meta_key']."|lqc_".$publicationControlId,
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