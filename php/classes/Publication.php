<?php

class Publication extends OpalProject
{
    protected $questionnaireDB;
    protected $ariaDB;

    /*
     * This function connects to the questionnaire database if needed
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
            false,
            $OAUserId
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    /*
     * This function connects to the Aria database if needed
     * @params  $OAUserId (ID of the user)
     * @returns None
     * */
    protected function _connectAriaDB() {
        $this->ariaDB = new DatabaseAria(
            ARIA_DB_HOST,
            "",
            ARIA_DB_PORT,
            ARIA_DB_USERNAME,
            ARIA_DB_PASSWORD,
            ARIA_DB_DSN
        );
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
        $toValidate = $this->arraySanitization($toValidate);
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
     * Validate a list of triggers by connecting to the opalDB and ariaDB and get all the settings of the triggers.
     * First, it loads the triggers from opalDB, then it populates the trigger to validate in the different arrays that
     * will do the validation.
     *
     * The validation then begins. First, if the trigger setting should be unique and it's not (for example, more than
     * one appointment status), it will reject it. It will then check if it is a custom validation, like a range or
     * an enum. If it is a regular validation, get the list of different values from opalDB and Aria (if any) and count
     * the total.
     *
     * */
    protected function _validateTriggers(&$triggersToValidate, &$moduleId) {
        $validatedTriggers = array();
        $listTriggers = $this->opalDB->getTriggersPerModule($moduleId);
        if (count($listTriggers) <= 0) return false;

        $isValid = true;

        foreach($listTriggers as $item) {
            $temp = explode(",", $item["internalName"]);
            $tempCustom = explode(";", $item["custom"]);
            $i = 0;
            foreach($temp as $item2) {
                $validatedTriggers[$item2] = array("data" => array(), "unique" => $item["isUnique"], "selectAll" => $item["selectAll"], "opalDB" => $item["opalDB"], "opalPK" => $item["opalPK"], "ariaDB" => $item["ariaDB"], "ariaPK" => $item["ariaPK"], "custom" => json_decode($tempCustom[$i], true));
                $i++;
            }
        }

        foreach($triggersToValidate as $trigger) {
            if ($validatedTriggers[$trigger["type"]]["data"] !== null)
                array_push($validatedTriggers[$trigger["type"]]["data"], $trigger["id"]);
            else {
                return false;
                break;
            }
        }

        foreach($validatedTriggers as $key => $trigger) {
            $allTriggersData = array();
            $selectAllChecked = false;
            $idsFound = 0;
            $ariaData = array();
            if ($trigger["unique"]) {
                if (count($trigger["data"]) > 1)
                    $isValid = false;
            }
            if ($trigger["custom"]) {
                if ($trigger["custom"]["range"]) {
                    $dataRange = explode(",", $trigger["data"][0]);
                    if ($dataRange[0] < $trigger["custom"]["range"][0] || $dataRange[1] > $trigger["custom"]["range"][1] || $dataRange[0] > $dataRange[1])
                        $isValid = false;
                }
                else if ($trigger["custom"]["enum"]) {
                    if(!in_array($trigger["data"][0], $trigger["custom"]["enum"]))
                        $isValid = false;
                }
            } else {
                if ($trigger["ariaDB"] != "")
                    $ariaData = $this->ariaDB->fetchTriggersData($trigger["ariaDB"], $trigger["ariaPK"]);
                if ($trigger["opalDB"] != "") {
                    $idsToIgnore = array(-1);
                    if (count($ariaData > 0)) {
                        $idsToIgnore = array();
                        foreach ($ariaData as $item) {
                            array_push($idsToIgnore, $item[$trigger["ariaPK"]]);
                        }
                    }
                    $opalData = $this->opalDB->fetchTriggersData(str_replace("%%ARIA_ID%%", implode(", ", $idsToIgnore), $trigger["opalDB"]), $trigger["opalPK"]);
                    $allTriggersData = $opalData + $ariaData;
                }

                foreach ($trigger["data"] as $item) {
                    if (strtolower($item) == "all") {
                        $selectAllChecked = true;
                    } else if (array_key_exists($item, $allTriggersData))
                        $idsFound++;

                }

                if (!$selectAllChecked && $idsFound != count($trigger["data"]))
                    $isValid = false;
            }
        }
        return $isValid;
    }

    /*
     * Insert a questionnaire ready to be published in the questionnaire control, filter and frequency events table
     * after validating and sanitizing the data.
     * @params  array of questionnaire settings and triggers
     * @return  void
     * */
    function insertPublication($publication) {


        $testTriggers = array(array("id"=>"all", type=>"Patient"), array("id"=>"Opal2", type=>"Patient"), array("id"=>"Opal4", type=>"Patient"), array("id"=>"Male", type=>"Sex"), array("id"=>"20,40", type=>"Age"), array("id"=>"Open", type=>"AppointmentStatus"), array("id"=>"ALL", type=>"Appointment"), array("id"=>"1132", type=>"Diagnosis"), array("id"=>"1133", type=>"Diagnosis"), array("id"=>"1134", type=>"Diagnosis"), array("id"=>"1135", type=>"Diagnosis"), array("id"=>"1136", type=>"Diagnosis"), array("id"=>"1137", type=>"Diagnosis"), array("id"=>"1138", type=>"Diagnosis"), array("id"=>"5631", type=>"Doctor"), array("id"=>"8169", type=>"Doctor"), array("id"=>"7737", type=>"Machine"), array("id"=>"7739", type=>"Machine"), array("id"=>"7738", type=>"Machine"), array("id"=>"7740", type=>"Machine"));

        $this->_connectAriaDB();
        $publicationControlId = "-1";
        $publication = $this->arraySanitization($publication);

        //print_R($publication);

        $moduleDetails = $this->opalDB->getPublicationModuleUserDetails($publication["moduleId"]["value"]);

//        print_R($moduleDetails);

        $result = $this->_validateTriggers($publication["triggers"], $moduleDetails["ID"]);
        if($result)
            echo "triggers are good";
        else
            echo "triggers are bad";
        die();


        if($moduleDetails["ID"] == MODULE_QUESTIONNAIRE) {
            print "questionnaire goes here\r\n";

            $this->_connectQuestionnaireDB($this->opalDB->getOAUserId());
            $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($publication["materialId"]["value"]);
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