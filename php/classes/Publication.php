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
        $errMsgs = array();                                             //By default, no error message
        $listTriggers = $this->opalDB->getTriggersPerModule($moduleId); //Get lists of triggers and their settings
        if (count($listTriggers) <= 0) {
            array_push($errMsgs, "Invalid Module.");
            return $errMsgs;
        }

        //Prepare the validated triggers list and their validation settings
        foreach($listTriggers as $item) {
            $temp = explode(",", $item["internalName"]);
            $tempCustom = explode(";", $item["custom"]);
            $i = 0;
            foreach($temp as $item2) {
                $validatedTriggers[$item2] = array("data" => array(), "unique" => $item["isUnique"], "selectAll" => $item["selectAll"], "opalDB" => $item["opalDB"], "opalPK" => $item["opalPK"], "ariaDB" => $item["ariaDB"], "ariaPK" => $item["ariaPK"], "custom" => json_decode($tempCustom[$i], true));
                $i++;
            }
        }

        //Copy the list of triggers into the list to validate
        foreach($triggersToValidate as $trigger) {
            if ($validatedTriggers[$trigger["type"]]["data"] !== null)
                array_push($validatedTriggers[$trigger["type"]]["data"], $trigger["id"]);
            else {
                array_push($errMsgs, $trigger["type"] . " is an invalid trigger.");
                break;
            }
        }

        if (count($errMsgs) > 0) return $errMsgs;

        //Validate the triggers
        foreach($validatedTriggers as $key => $trigger) {
            $allTriggersData = array();
            $selectAllChecked = false;
            $idsFound = 0;
            $ariaData = array();

            //If the trigger should be unique but data indicates it's not, stop the processing and returns false
            if ($trigger["unique"]) {
                if (count($trigger["data"]) > 1) {
                    array_push($errMsgs, "$key: value for this trigger must be unique.");
                }
            }

            if(count($trigger["data"]) > 0) {
                //If the trigger requires custom checkup (like looking into a list or with a range)
                if ($trigger["custom"]) {
                    if ($trigger["custom"]["range"]) {
                        $dataRange = explode(",", $trigger["data"][0]);
                        if ($dataRange[0] < $trigger["custom"]["range"][0] || $dataRange[1] > $trigger["custom"]["range"][1] || $dataRange[0] > $dataRange[1]) {
                            array_push($errMsgs, "$key: invalid range value.");
                        }
                    }
                    else if ($trigger["custom"]["enum"]) {
                        if(!in_array($trigger["data"][0], $trigger["custom"]["enum"])) {
                            array_push($errMsgs, "$key: non-existant value requested.");
                        }
                    }
                }
                //Standard process of the checkup by getting data from OpalDB and Aria
                else {
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
                    }

                    $uniqueIdList = array();
                    foreach ($trigger["data"] as $item) {
                        if (strtolower($item) == "all") {
                            $selectAllChecked = true;
                        } else if (array_key_exists($item, $opalData) || array_key_exists($item, $ariaData))
                            $idsFound++;
                        if(!in_array($item, $uniqueIdList)) array_push($uniqueIdList, $item);
                    }

                    if(count($uniqueIdList) == count($trigger["data"])) {
                        if($selectAllChecked) {
                            if ($idsFound != 0) {
                                array_push($errMsgs, "$key: cannot use the word 'all' and have another value associated to this trigger.");
                            }
                        }
                        else if($idsFound != count($trigger["data"])) {
                            array_push($errMsgs, "$key: invalid IDs found.");
                        }
                    }
                    else {
                        array_push($errMsgs, "$key: duplicated value found.");
                    }
                }
            }
        }
        return $errMsgs;
    }

    protected function _validateStandardRepeat($frequency, &$errMsgs) {
        $metaKey = array("repeat_day", "repeat_week", "repeat_month");
        if(!in_array($frequency["meta_key"], $metaKey))
            array_push($errMsgs, "Invalid occurrence regular frequency meta key.");
        if(array_key_exists("additionalMeta", $frequency))
            array_push($errMsgs, "Invalid occurrence regular frequency value.");
    }

    protected function _validateDateAndRange(&$occurrence, &$errMsgs, $strictEnforcement = true) {
        $currentDate = false;
        if($strictEnforcement)
            $currentDate = (int) $occurrence["start_date"] < strtotime(date("Y-m-d"));

        if (!HelpSetup::isValidTimeStamp($occurrence["start_date"]) || $currentDate)
            array_push($errMsgs, "Invalid start date.");

        if ($occurrence["end_date"] != "" && (!HelpSetup::isValidTimeStamp($occurrence["end_date"]) || $currentDate))
            array_push($errMsgs, "Invalid end date.");

        if ($occurrence["end_date"] != "" && (int) $occurrence["end_date"] < (int) $occurrence["start_date"])
            array_push($errMsgs, "Invalid date range.");
    }

    protected function _validateCustomRepeatPerDay(&$frequency, &$errMsgs) {
        if (array_key_exists("additionalMeta", $frequency))
            array_push($errMsgs, "Invalid custom occurrence frequency meta key with additional meta.");
    }

    protected function _validateCustomRepeatPerWeek(&$frequency, &$errMsgs) {
        if(isset($frequency['additionalMeta'])) {
            if(count($frequency['additionalMeta']) == 1 && isset($frequency['additionalMeta'][0]['meta_key']) && $frequency['additionalMeta'][0]['meta_key'] == "repeat_day_iw" && isset($frequency['additionalMeta'][0]['meta_value']) && count($frequency['additionalMeta'][0]['meta_value']) >= 1 && count($frequency['additionalMeta'][0]['meta_value']) <= 7)
            {
                $tempVerif = true;
                foreach($frequency['additionalMeta'][0]['meta_value'] as $mv) {
                    if(intval($mv) < 1 || intval($mv) > 7)
                        $tempVerif = false;
                }
                if(!$tempVerif)
                    array_push($errMsgs, "Invalid meta data data for week.");
            }
            else
                array_push($errMsgs, "Invalid meta key structure for week.");
        }
    }

    protected function _validateCustomRepeatPerMonth(&$frequency, &$errMsgs) {
        if(isset($frequency['additionalMeta'])) {
            if(count($frequency['additionalMeta']) == 1) {
                if(isset($frequency['additionalMeta'][0]['meta_key'])
                    && $frequency['additionalMeta'][0]['meta_key'] == "repeat_date_im"
                    && isset($frequency['additionalMeta'][0]['meta_value'])
                    && count($frequency['additionalMeta'][0]['meta_value']) >= 1
                    && count($frequency['additionalMeta'][0]['meta_value']) <= 31
                ) {
                    $tempVerif = true;
                    foreach ($frequency['additionalMeta'][0]['meta_value'] as $mv) {
                        if(intval($mv) < 1 || intval($mv) > 31)
                            $tempVerif = false;
                    }
                    if(!$tempVerif)
                        array_push($errMsgs, "Invalid meta value data for month.");
                }
                else
                    array_push($errMsgs, "Invalid meta data structure for month.");
            } else if(count($frequency['additionalMeta']) == 2) {
                $repeatDayIsOk = true;
                $repeatWeekIsOk = true;
                foreach ($frequency['additionalMeta'] as $ad) {
                    if(isset($ad['meta_key']) && $ad['meta_key'] == 'repeat_day_iw') {
                        if(isset($ad['meta_value'])) {
                            foreach($ad['meta_value'] as $mv) {
                                if(intval($mv) < 1 || intval($mv) > 7)
                                    $repeatDayIsOk = false;
                            }
                        }
                        else
                            $repeatDayIsOk = false;
                    }
                    else if(isset($ad['meta_key']) && $ad['meta_key'] == 'repeat_week_im') {
                        if(isset($ad['meta_value'])) {
                            foreach($ad['meta_value'] as $mv) {
                                if(intval($mv) < 1 || intval($mv) > 6)
                                    $repeatWeekIsOk = false;
                            }
                        }
                        else
                            $repeatWeekIsOk = false;
                    }
                }
                if(!$repeatDayIsOk || !$repeatWeekIsOk)
                    array_push($errMsgs, "Invalid meta value data for month.");
            }
            else
                array_push($errMsgs, "Invalid meta key structure for month.");
        }
    }

    protected function _validateCustomRepeatPerYear(&$frequency, &$errMsgs) {
        $totalMeta = count($frequency['additionalMeta']);
        if(isset($frequency['additionalMeta']) && is_array($frequency['additionalMeta']) && ($totalMeta >= 1 || $totalMeta <=3)) {
            $repeatMonthIyIsOk = true;
            $repeatWeekImIsOk = true;
            $repeatDayIwIsOk = true;
            foreach($frequency['additionalMeta'] as $av) {
                if(isset($av['meta_key']) && $av['meta_key'] == "repeat_month_iy") {
                    if(isset($av['meta_value']) && count($av['meta_value']) >= 1 && count($av['meta_value']) <= 12) {
                        $tempVerif = true;
                        foreach ($av['meta_value'] as $mv) {
                            if(intval($mv) < 1 || intval($mv) > 12)
                                $tempVerif = false;
                        }
                        if(!$tempVerif)
                            $repeatMonthIyIsOk = false;
                    } else
                        $repeatMonthIyIsOk = false;
                }
                else if(isset($av['meta_key']) && $av['meta_key'] == "repeat_week_im") {
                    if(isset($av['meta_value']) && count($av['meta_value']) == 1) {
                        $tempVerif = true;
                        foreach ($av['meta_value'] as $mv) {
                            if(intval($mv) < 1 || intval($mv) > 6)
                                $tempVerif = false;
                        }
                        if(!$tempVerif)
                            $repeatWeekImIsOk = false;
                    } else
                        $repeatWeekImIsOk = false;
                }
                else if(isset($av['meta_key']) && $av['meta_key'] == "repeat_day_iw") {
                    if(isset($av['meta_value']) && count($av['meta_value']) == 1) {
                        $tempVerif = true;
                        foreach ($av['meta_value'] as $mv) {
                            if(intval($mv) < 1 || intval($mv) > 7)
                                $tempVerif = false;
                        }
                        if(!$tempVerif)
                            $repeatDayIwIsOk = false;
                    } else
                        $repeatDayIwIsOk = false;
                }
                else {
                    $repeatMonthIyIsOk = false;
                    $repeatWeekImIsOk = false;
                    $repeatDayIwIsOk = false;
                }
            }
            if(!$repeatMonthIyIsOk || !$repeatWeekImIsOk || !$repeatDayIwIsOk)
                array_push($errMsgs, "Invalid occurrence frequency additional meta structure.");
        }
    }

    protected function _validateCustomRepeat(&$frequency, &$errMsgs) {
        if(!in_array($frequency["meta_key"], array("repeat_day", "repeat_week", "repeat_month", "repeat_year")))
            array_push($errMsgs, "Invalid custom occurrence frequency meta key.");

        if($frequency["meta_key"] == "repeat_day")
            $this->_validateCustomRepeatPerDay($frequency, $errMsgs);
        else if($frequency["meta_key"] == "repeat_week")
            $this->_validateCustomRepeatPerWeek($frequency, $errMsgs);
        else if($frequency["meta_key"] == "repeat_month")
            $this->_validateCustomRepeatPerMonth($frequency, $errMsgs);
        else if($frequency["meta_key"] == "repeat_year")
            $this->_validateCustomRepeatPerYear($frequency, $errMsgs);
        else
            array_push($errMsgs, "Invalid occurrence frequency.");
    }

    protected function _validateFrequency(&$publication, &$subModule) {
        $errMsgs = array();                                             //By default, no error message
        $pubSettings = $this->opalDB->getPublicationSettingsPerModule($publication["moduleId"]["value"]);
        $subModule = json_decode($subModule, true);

        foreach($pubSettings as $setting) {
            $mandatory = false;
            if(count($subModule) > 0) {
                foreach($subModule as $sub) {
                    if ($sub["name_EN"] == $publication["materialId"]["type"] && array_key_exists($setting["internalName"], $sub)) {
                        $mandatory = ($sub[$setting["internalName"]] == 1);
                    }
                }
            }
            if($mandatory && !array_key_exists($setting["internalName"], $publication)) {
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Publication setting " . $setting["internalName"] . " not found.");
                break;
            }

            if($setting["custom"] != "") {
                $custom = json_decode($setting["custom"], true);
                if (array_key_exists("dateTime", $custom)) {
                    if(!HelpSetup::verifyDate($publication[$setting["internalName"]], true, $custom["dateTime"]))
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid publishing date");
                }
                if (array_key_exists("occurrence", $custom) && $publication['occurrence']['set']) {
                    $this->_validateDateAndRange($publication['occurrence'], $errMsgs);

                    if (array_key_exists("frequency", $publication['occurrence']) && array_key_exists("custom", $publication['occurrence']['frequency']) && array_key_exists("meta_key", $publication['occurrence']['frequency']) && array_key_exists("meta_value", $publication['occurrence']['frequency'])) {
                        if(intval($publication['occurrence']['frequency']["meta_value"]) > 59 || intval($publication['occurrence']['frequency']["meta_value"]) < 1 )
                            array_push($errMsgs, "Invalid occurrence frequency meta value.");
                        if($publication['occurrence']['frequency']["custom"] == "0") {
                            $this->_validateStandardRepeat($publication['occurrence']['frequency'], $errMsgs);
                        }
                        else if($publication['occurrence']['frequency']["custom"] == "1") {
                            $this->_validateCustomRepeat($publication['occurrence']['frequency'],$errMsgs);
                        }
                        else
                            array_push($errMsgs, "Invalid custom settings.");
                    }
                    else
                        array_push($errMsgs, "Missing frequency data.");
                }
            }
        }

        return $errMsgs;
    }

    protected function _insertPublicationPost(&$publication) {

    }

    protected function _insertPublicationQuestionnaire(&$publication) {
        $this->_connectQuestionnaireDB($this->opalDB->getOAUserId());
        $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($publication["materialId"]["value"]);

        if(count($currentQuestionnaire) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire");
        $currentQuestionnaire = $currentQuestionnaire[0];

        $toInsert = array(
            "QuestionnaireDBSerNum"=>$currentQuestionnaire["ID"],
            "QuestionnaireName_EN"=>$publication["name"]["name_EN"],
            "QuestionnaireName_FR"=>$publication["name"]["name_FR"],
            "Intro_EN"=>htmlspecialchars_decode($currentQuestionnaire["description_EN"]),
            "Intro_FR"=>htmlspecialchars_decode($currentQuestionnaire["description_FR"]),
            "SessionId"=>$this->opalDB->getSessionId(),
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
                    "SessionId"=>$this->opalDB->getSessionId(),
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

    /*
     * Insert a publication into the matching control, filter and frequency events table after validating and
     * sanitizing the data.
     * @params  array of publication settings and triggers
     * @return  void
     * */
    function insertPublication($publication) {
        $this->_connectAriaDB();
        $publication = $this->arraySanitization($publication);

        $moduleDetails = $this->opalDB->getModuleSettings($publication["moduleId"]["value"]);

        $result = $this->_validateTriggers($publication["triggers"], $moduleDetails["ID"]);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Trigger validation failed. " . implode(" ", $result));

        $result = $this->_validateFrequency($publication, $moduleDetails["subModule"]);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Frequency validation failed. " . implode(" ", $result));

        if($moduleDetails["ID"] == MODULE_QUESTIONNAIRE) {
            $this->_insertPublicationQuestionnaire($publication);
        }
        else if($moduleDetails["ID"] == MODULE_POST) {
            $this->_insertPublicationPost($publication);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module");



        return false;

    }

}