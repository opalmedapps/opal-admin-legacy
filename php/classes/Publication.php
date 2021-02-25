<?php

class Publication extends Module
{
    protected $questionnaireDB;

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PUBLICATION, $guestStatus);
    }

    /*
     * This function connects to the questionnaire database if needed
     * @params  $OAUserId (ID of the user)
     * @returns None
     * */
    protected function _connectQuestionnaireDB() {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            false
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
        $this->checkReadAccess();
        return $this->opalDB->getPublications();
    }

    /*
     * Get the details of a publication, plus the module associated it, its name and its description
     * @params  $publicationId (int) and $moduleId (int)
     * @return  $results (array) array that contains all the details
     * */
    public function getPublicationDetails($publicationId, $moduleId) {
        $this->checkReadAccess(array($publicationId, $moduleId));
        if($publicationId == "" || $moduleId == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid publication settings.");

        $results = array();
        $results["publication"]["publicationId"] =  $publicationId;
        $results["publication"]["moduleId"] =  $moduleId;

        $module = $this->opalDB->getModuleSettings($moduleId);
        if(!isset($module["ID"]) || $module["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module ID.");

        $publicationDetails = $this->opalDB->getPublicationDetails($moduleId, $publicationId);
        if(!isset($publicationDetails["ID"]) || $publicationDetails["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid publication settings.");

        $results["publication"]["name"]["EN"] = $publicationDetails["name_EN"];
        $results["publication"]["name"]["FR"] = $publicationDetails["name_FR"];
        $results["publication"]["module"]["EN"] = $publicationDetails["module_EN"];
        $results["publication"]["module"]["FR"] = $publicationDetails["module_FR"];
        $results["publication"]["description"]["EN"] = $publicationDetails["type_EN"];
        $results["publication"]["description"]["FR"] = $publicationDetails["type_FR"];

        $subModuleList =  json_decode($module["subModule"]);
        $results["publication"]["unique"] =  $module["unique"];

        if($moduleId == MODULE_POST) {
            $postDetails = $this->opalDB->getPostDetails($publicationId);
            foreach($subModuleList as $subModule) {
                if($postDetails["type"] == $subModule->name_EN) {
                    $results["publication"]["subModule"] = json_decode(json_encode($subModule), true);
                    //$results["publication"]["modifyPublishDateTime"] = $subModule["publishDateTime"];
                    break;
                }
            }
        }

        $results["publicationSettings"] = $this->opalDB->getPublicationSettingsIDsPerModule($moduleId);
        $tempArray = array();
        foreach($results["publicationSettings"] as $trigger)
            array_push($tempArray, $trigger["publicationSettingId"]);
        $results["publicationSettings"] = $tempArray;

        if(in_array(PUBLICATION_PUBLISH_DATE, $results["publicationSettings"])) {
            $results["publishDateTime"] = $this->opalDB->getPublishDateTime($module["tableName"], $module["primaryKey"], $publicationId);
        }

        if($moduleId == MODULE_QUESTIONNAIRE) {
            $questionnaire = $this->opalDB->getPublishedQuestionnaireDetails($publicationId);
            if(is_array($questionnaire) && count($questionnaire) != 1)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire publication.");
            $results["name"]["name_EN"] = $questionnaire[0]["name_EN"];
            $results["name"]["name_FR"] = $questionnaire[0]["name_FR"];
        }

        $frequencyEvents = $this->opalDB->getFrequencyEvents($publicationId, $module["controlTableName"]);

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

        if(is_array($frequencyEvents) && count($frequencyEvents) > 0) {
            foreach ($frequencyEvents as $data) {
                // if we've entered, then a frequency has been set
                $occurrenceArray['set'] = 1;

                $customFlag = $data["CustomFlag"];
                // the type of meta key and which content it belongs to is separated by the | delimeter
                list($metaKey, $dontNeed) = explode('|', $data["MetaKey"]);
                $metaValue = $data["MetaValue"];

                if ($metaKey == 'repeat_start') {
                    $occurrenceArray['start_date'] = $metaValue;
                } else if ($metaKey == 'repeat_end') {
                    $occurrenceArray['end_date'] = $metaValue;
                } // custom non-additional meta (eg. repeat_day, repeat_week ... any meta with one underscore that was custom made)
                else if ($customFlag == 1 and count(explode('_', $metaKey)) == 2) {
                    $occurrenceArray['frequency']['custom'] = 1;
                    $occurrenceArray['frequency']['meta_key'] = $metaKey;
                    $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
                } // additional meta (eg. repeat_day_iw, repeat_week_im ... any meta with two underscores)
                else if ($customFlag == 1 and count(explode('_', $metaKey)) == 3) {
                    $occurrenceArray['frequency']['custom'] = 1;
                    $occurrenceArray['frequency']['additionalMeta'][$metaKey] = array_map('intval', explode(',', $metaValue));
                    sort($occurrenceArray['frequency']['additionalMeta'][$metaKey]);
                } else { // should only be one predefined frequency chosen, if chosen
                    $occurrenceArray['frequency']['meta_key'] = $metaKey;
                    $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
                }
            }
            $results["occurrence"] = $occurrenceArray;
        }

        $results["triggers"] = $this->opalDB->getTriggersDetails($publicationId, $module["controlTableName"]);
        return $results;
    }

    /*
     * Get the list of materials that can be published based on the module request
     * params   module ID
     * returns  array of data
     * */
    public function getPublicationsPerModule($moduleId) {
        $this->checkReadAccess($moduleId);
        if($moduleId == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Module cannot be found. Access denied.");
        $results = $this->opalDB->getPublicationsPerModule($moduleId);

        $tempArray = array();
        foreach($results["triggers"] as $trigger)
            array_push($tempArray, $trigger["publicationSettingId"]);
        $results["triggers"] = $tempArray;
        return $results;
    }

    /*
     * Returns the chart log of a specific publication.
     * @params  $moduleId (int) ID of the module to get the logs
     *          $publicationId (int) ID of the publication of the module to get the chart logs
     * @return  (array) list of the chart logs found
     * */
    public function getPublicationChartLogs($moduleId, $publicationId) {
        $this->checkReadAccess(array($moduleId, $publicationId));
        $data = array();
        $result = $this->opalDB->getPublicationChartLogs($moduleId, $publicationId);
        //The Y value has to be converted to an int, or the chart log will reject it on the front end.
        foreach ($result as &$item) {
            $item["y"] = intval($item["y"]);
        }

        if (is_array($result) && count($result) > 0)
            array_push($data, array("name"=>"", "data"=>$result));

        return $data;
    }

    /*
     * Returns the chart log of a specific publication.
     * @params  $moduleId (int) ID of the module to get the logs
     *          $publicationId (int) ID of the publication of the module to get the chart logs
     * @return  (array) list of the chart logs found
     * */
    public function getPublicationListLogs($moduleId, $publicationId, $cronIds) {
        $this->checkReadAccess(array($moduleId, $publicationId, $cronIds));
        if($moduleId == "" || $publicationId == "" || count($cronIds) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "List Logs error. Invalid data.");
        return $this->opalDB->getPublicationListLogs($moduleId, $publicationId, $cronIds);
    }

    /*
     * Validate and sanitize the list of publish flag of publications
     * @params  array of publications to mark as published or not ($_POST)
     * @return  array of sanitize data
     * */
    function validateAndSanitizePublicationList($toValidate) {
        $validatedList = array();
        $toValidate = HelpSetup::arraySanitization($toValidate);
        foreach($toValidate as $item) {
            $id = trim(strip_tags($item["ID"]));
            $publication = trim(strip_tags($item["moduleId"]));
            $publishFlag = intval(trim(strip_tags($item["publishFlag"])));
            if ($publishFlag != 0 && $publishFlag != 1)
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
        $this->checkWriteAccess($list);
        $clearedPublishList = $this->validateAndSanitizePublicationList($list);
        $publicationModules = $this->opalDB->getPublicationModules();
        foreach($clearedPublishList as $row) {
            foreach($publicationModules as $module) {
                if ($module["ID"] == $row["moduleId"]) {
                    $this->opalDB->updatePublicationFlag($module["tableName"], $module["primaryKey"], $row["publishFlag"], $row["ID"]);
                    break;
                }
            }
        }
    }

    /*
     * This function reassign the correct appointment status when treating "Checked In". This function only exists
     * because it was decided to add it manually to the status list by hard-coding it while using incorrect values.
     * Once it will be fixed (if it is), this function will be totally useless. See ticket OPAL-74 for more details
     * */
    protected function _reassignData($data, $id, $key)  {
        $results = array();
        foreach($data as $item) {
            if($item[$id] == "1" && $key == "AppointmentStatus")
                $results["Checked In"] = $item;
            else
                $results[strval($item[$id])] = $item;
        }
        return $results;
    }

    /*
     * Validate a list of triggers by connecting to the opalDB and get all the settings of the triggers.
     * First, it loads the triggers from opalDB, then it populates the trigger to validate in the different arrays that
     * will do the validation.
     *
     * The validation then begins. First, if the trigger setting should be unique and it's not (for example, more than
     * one appointment status), it will reject it. It will then check if it is a custom validation, like a range or
     * an enum. If it is a regular validation, get the list of different values from opalDB (if any) and count
     * the total.
     *
     * @params  $triggersToValidate (array) contains the triggers received from the user to validate
     *          $moduleId (int) ID of the module with the triggers to validate
     * @returns $errMsgs (array) Array containing all the errors messages received during the validation
     * */
    protected function _validateTriggers(&$triggersToValidate, &$moduleId) {
        $validatedTriggers = array();
        $errMsgs = array();                                             //By default, no error message
        $listTriggers = $this->opalDB->getPublicationSettingsPerModule($moduleId); //Get lists of triggers and their settings
        if (is_array($listTriggers) && count($listTriggers) <= 0) {
            array_push($errMsgs, "Invalid Module.");
            return $errMsgs;
        }
        if(is_array($triggersToValidate) && count($triggersToValidate) <= 0) {
            array_push($errMsgs, "No trigger in the publication.");
            return $errMsgs;
        }

        //Prepare the validated triggers list and their validation settings
        foreach($listTriggers as $item) {
            $temp = explode(",", $item["internalName"]);
            $tempCustom = explode(";", $item["custom"]);
            $i = 0;
            foreach($temp as $item2) {
                $validatedTriggers[$item2] = array(
                    "data" => array(),
                    "unique" => $item["isUnique"],
                    "selectAll" => $item["selectAll"],
                    "opalDB" => $item["opalDB"],
                    "opalPK" => $item["opalPK"],
                    "custom" => json_decode($tempCustom[$i], true));
                $i++;
            }
        }

        //Copy the list of triggers into the list to validate
        //CheckedInFlag has being hardcoded because somebody thought it will be faster and simpler to manually
        //add 'Checked In' option as an appointment status then adding it in the database and by not following the
        //same data structure and naming convention. This is the result: hardcoded solution to bypass this mess.
        foreach($triggersToValidate as $trigger) {
            if ($validatedTriggers[$trigger["type"]]["data"] !== null)
                array_push($validatedTriggers[$trigger["type"]]["data"], $trigger["id"]);
            else if($trigger["type"] == "CheckedInFlag") {
                array_push($validatedTriggers["AppointmentStatus"]["data"], "Checked In");
            }
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

            //If the trigger should be unique but data indicates it's not, stop the processing and returns false
            if ($trigger["unique"]) {
                if (is_array($trigger["data"]) && count($trigger["data"]) > 1) {
                    array_push($errMsgs, "$key: value for this trigger must be unique.");
                }
            }

            if(is_array($trigger["data"]) && count($trigger["data"]) > 0) {
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
                    else
                        array_push($errMsgs, "Unknown trigger settings.");
                }
                //Standard process of the checkup by getting data from OpalDB
                else {
                    if ($trigger["opalDB"] != "") {
                        $opalData = $this->_reassignData($this->opalDB->fetchTriggersData($trigger["opalDB"]), $trigger["opalPK"], $key);
                    }

                    $uniqueIdList = array();
                    foreach ($trigger["data"] as $item) {
                        if (strtolower($item) == "all") {
                            $selectAllChecked = true;
                        } else if (array_key_exists($item, $opalData))
                            $idsFound++;
                        if(!in_array($item, $uniqueIdList)) array_push($uniqueIdList, $item);
                    }

                    if(is_array($uniqueIdList) && is_array($trigger["data"]) && count($uniqueIdList) == count($trigger["data"])) {
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

    /*
     * Function that validates if a series of keyword is present or not to validate a standard frequency.
     * @params  $frequency (array) array of frequencies to check
     *          $errMsgs (array) array of error messages where to store errors if found.
     * */
    protected function _validateStandardRepeat($frequency, &$errMsgs) {
        $metaKey = array("repeat_day", "repeat_week", "repeat_month");
        if(!in_array($frequency["meta_key"], $metaKey))
            array_push($errMsgs, "Invalid occurrence regular frequency meta key.");
        if(array_key_exists("additionalMeta", $frequency))
            array_push($errMsgs, "Invalid occurrence regular frequency value.");
    }

    /*
     * Validates the date time and insure it is a valid timestamp, and check if the timestamp are setup in the past.
     *
     * @params  $occurence (array) date and time to validate
     *          $errMsgs (array) list of current error messages
     *          $strictEnforcement (boolean) if a dateTime can be set in the past or not.
     * @returns void
     * */
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

    /*
     * Validate a custom frequency per day. Make sure the specific keyword is present.
     *
     * @params  $frequency (array)  list of frequency per day to validate
     *          $errMsgs (array) List of error message.
     * @returns void
     * */
    protected function _validateCustomRepeatPerDay(&$frequency, &$errMsgs) {
        if (array_key_exists("additionalMeta", $frequency))
            array_push($errMsgs, "Invalid custom occurrence frequency meta key with additional meta.");
    }

    /*
     * Validate a custom frequency per week. Check if the frequency for the days if set up is between 1 and 7.
     *
     * @params  $frequency (array)  list of frequency per week to validate
     *          $errMsgs (array) List of error message.
     * @returns void
     * */
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

    /*
     * Validate a custom frequency per month. Check if the repeat option is on date or on week. On date, check the
     * value of each date and make sure they are between the range of 1 and 31. On week, make sure the number of week
     * is in range of 1 to 6 (5 = 5th week, 6 = last week) and the day range is between 1 and 7. If there is a problem
     * add it to the error messages array.
     *
     * @params  $frequency (array)  list of frequency per month to validate
     *          $errMsgs (array) List of error message.
     * @returns void
     * */
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

    /*
     * Validate a custom frequency per year. If there is no data or more than 3 block of data, rejects the validation.
     * Next, check if there is a repeat options per month, week and day and validate each of them. Finally, update the
     * error message list if needed.
     *
     * @params  $frequency (array)  list of frequency per year to validate
     *          $errMsgs (array) List of error message.
     * @returns void
     * */
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

    /*
     * Function that call the specific function to validate per day, week, month or year for custom settings.
     *
     * @params  $frequency (array) the frequency to validate
     *          $errMsgs (array) the list of error messages where to add new ones if needed
     * @returns void
     * */
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

    /*
     * Validation of the frequency of a publication. for each setting of a publication, the functions doest the
     * following: 1) Check if the setting is mandatory or not. 2) If it is datetime, check if is valid and required.
     * 3) If the occurence is a regular, call the function _validateStandardRepeat but if it is not, call the function
     * _validateCustomRepeat.
     *
     * @params  $publication(array) details of the publication to validate
     *          $subModule(array) list of the sub-modules details to validate the obligation of specific settings
     * @returns $errMsgs(array) List of error messages if the validation failed.
     * */
    protected function _validateFrequency(&$publication, &$subModule, $strictEnforcement = true) {
        $errMsgs = array();                                             //By default, no error message
        $pubSettings = $this->opalDB->getPublicationNonTriggerSettingsPerModule($publication["moduleId"]["value"]);
        $subModule = json_decode($subModule, true);
        foreach($pubSettings as $setting) {
            $mandatory = false;
            if(is_array($subModule) && count($subModule) > 0) {
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
                    if(isset($publication["materialId"]["type"]) && $publication["materialId"]["type"] == "Announcement") {
                        if(!HelpSetup::verifyDate($publication[$setting["internalName"]], true, $custom["dateTime"]))
                            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid publishing date.");
                    }
                    else if(isset($publication[$setting["internalName"]])) {
                        if(!HelpSetup::verifyDate($publication[$setting["internalName"]], true, $custom["dateTime"]))
                            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid publishing date.");

                    }
                }
                if (array_key_exists("occurrence", $custom) && $publication['occurrence']['set']) {
                    $this->_validateDateAndRange($publication['occurrence'], $errMsgs, $strictEnforcement);

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

    /*
     * For publication of a new post only. This function updates the publication date and time of the post (if it
     * exists) before inserting the post itself in the Filters table.
     * @params  $publication (array) the details of the publication to insert
     * @return  void
     * */
    protected function _insertPublicationPost(&$publication) {
        $postDetails = $this->opalDB->getPostDetails($publication["materialId"]["value"]);
        if(is_array($postDetails) && count($postDetails) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post.");

        if(isset($publication["publishDateTime"]) && $publication["publishDateTime"] != "")
            $count = $this->opalDB->updatePostPublishDateTime(
                array("PostControlSerNum"=>$publication["materialId"]["value"],"PublishDate"=>$publication["publishDateTime"])
            );

        if(!empty($publication['triggers']))
            $this->_insertFilters($publication, $publication["materialId"]["value"], "PostControl");
    }

    /*
     * For publication of a new educational material.
     * @params  $publication (array) the details of the publication to insert
     * @return  void
     * */
    protected function _insertPublicationEduMaterial(&$publication) {
        if(!empty($publication['triggers']))
            $this->_insertFilters($publication, $publication["materialId"]["value"], "EducationalMaterialControl");
    }

    /*
     * Insert new filters for a publication.
     * @params  $publication(reference of array), $publicationControlId (reference of int), $controlTableName (string)
     * @return  void
     * */
    protected function _insertFilters(&$publication, &$publicationControlId, $controlTableName) {
        $toInsert = array();
        foreach($publication['triggers'] as $trigger) {
            array_push($toInsert, array(
                "ControlTable"=>$controlTableName,
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
     * For publication of a questionnaire (new or current). It adds the questionnaire to the questionnaire control,
     * inserts the triggers and if it exists, the frequency.
     * @params  $publication (array) the details of the publication to insert
     * @return  void
     * */
    protected function _insertPublicationQuestionnaire(&$publication) {
        $this->_connectQuestionnaireDB();
        $currentQuestionnaire = $this->questionnaireDB->getQuestionnaireDetails($publication["materialId"]["value"]);

        if(is_array($currentQuestionnaire) && count($currentQuestionnaire) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire.");
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
     * Update the publication of a questionnaire regarding the names. Then update the triggers.
     * @params  $questionnaire (array) details of the publication of the questionnaire
     *          $controlTableName (string) name of the control table for the questionnaires
     * @return  void
     * */
    protected function _updatePublicationQuestionnaire($questionnaire, $controlTableName) {
        $toUpdate = array(
            "QuestionnaireName_EN"=>$questionnaire["name"]["name_EN"],
            "QuestionnaireName_FR"=>$questionnaire["name"]["name_FR"],
            "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
            "SessionId"=>$this->opalDB->getSessionId(),
            "QuestionnaireControlSerNum"=>$questionnaire["materialId"]["value"],
        );
        $total = $this->opalDB->updateQuestionnaireControl($toUpdate);
        $this->_updateTriggers($questionnaire, $controlTableName);
    }

    /*
     * Update the publication of a post regarding the publish date. Then update the triggers.
     * @params  $post (array) details of the publication of the post
     *          $controlTableName (string) name of the control table for the posts
     * @return  void
     * */
    protected function _updatePublicationPost($post, $controlTableName) {
        $toUpdate = array(
            "PublishDate"=>$post["publishDateTime"],
            "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
            "SessionId"=>$this->opalDB->getSessionId(),
            "PostControlSerNum"=>$post["materialId"]["value"],
        );
        $total = $this->opalDB->updatePostControl($toUpdate);
        $this->_updateTriggers($post, $controlTableName);
    }

    /*
     * Update the triggers of a publications. First it deletes and updates the triggers, then add the new ones. Then
     * it updates the occurrence if there are some.
     * @params  $publication (array) details of the triggers to update
     *          $controlTableName (string) name of the control table for the posts
     * @return  void
     * */
    protected function _updateTriggers($publication, $controlTableName) {
        $total = 0;
        //Delete and update triggers
        if(!empty($publication["triggers_updated"])) {
            $existingTriggers = $this->opalDB->getFiltersByControlTableSerNum($publication["materialId"]["value"], $controlTableName);
            foreach($existingTriggers as $trigger) {
                if(!$this->_nestedSearch($trigger["id"], $trigger["type"], $publication["triggers"])) {
                    $total += $this->opalDB->deleteFilters($trigger["id"], $trigger["type"], $publication["materialId"]["value"], $controlTableName);
                    $toUpdate = array(
                        "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                        "SessionId"=>$this->opalDB->getSessionId(),
                        "FilterId"=>$trigger["id"],
                        "FilterType"=>$trigger["type"],
                        "ControlTableSerNum"=>$publication["materialId"]["value"],
                        "ControlTable"=>$controlTableName,
                    );
                    $total += $this->opalDB->updateFiltersModificationHistory($toUpdate);
                }
            }
        }

        //Add new triggers
        if(!empty($publication["triggers"])) {
            $toInsert = array();
            foreach($publication["triggers"] as $trigger) {
                if (!$this->_nestedSearch($trigger["id"], $trigger["type"], $existingTriggers))
                    array_push($toInsert, array(
                        "ControlTable"=>$controlTableName,
                        "ControlTableSerNum"=>$publication["materialId"]["value"],
                        "FilterType"=>$trigger['type'],
                        "FilterId"=>$trigger['id'],
                        "DateAdded"=>date("Y-m-d H:i:s"),
                        "LastUpdatedBy"=>$this->opalDB->getOAUserId(),
                        "SessionId"=>$this->opalDB->getSessionId(),
                    ));
            }
            $this->opalDB->insertMultipleFilters($toInsert);
        }

        if(!$publication["occurrence"]["set"]) {
            $total += $this->opalDB->deleteFrequencyEvent($publication["materialId"]["value"], $controlTableName);
        }
        else {
            $toInsert = array(
                "ControlTable"=>$controlTableName,
                "ControlTableSerNum"=>$publication["materialId"]["value"],
                "MetaKey"=>'repeat_start',
                "MetaValue"=>$publication["occurrence"]["start_date"],
                "CustomFlag"=>'0',
                "DateAdded"=>date("Y-m-d H:i:s"),
            );
            $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
            if(!$publication["occurrence"]["end_date"]) {
                $result = $this->opalDB->deleteRepeatEndFromFrequencyEvents($publication["materialId"]["value"], $controlTableName);
            }
            else {
                $toInsert = array(
                    "ControlTable" => $controlTableName,
                    "ControlTableSerNum" => $publication["materialId"]["value"],
                    "MetaKey" => 'repeat_end',
                    "MetaValue" => $publication["occurrence"]["end_date"],
                    "CustomFlag" => '0',
                    "DateAdded" => date("Y-m-d H:i:s"),
                );
                $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
            }

            $result = $this->opalDB->deleteOtherMetasFromFrequencyEvents($publication["materialId"]["value"], $controlTableName);
            $toInsert = array(
                "ControlTable"=>$controlTableName,
                "ControlTableSerNum"=>$publication["materialId"]["value"],
                "MetaKey"=>$publication['occurrence']['frequency']['meta_key']."|lqc_".$publication["materialId"]["value"],
                "MetaValue"=>$publication['occurrence']['frequency']['meta_value'],
                "CustomFlag"=>$publication['occurrence']['frequency']['custom'],
                "DateAdded"=>date("Y-m-d H:i:s"),
            );
            $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);

            if(!empty($publication['occurrence']['frequency']['additionalMeta'])) {
                foreach($publication['occurrence']['frequency']['additionalMeta'] as $meta) {
                    $toInsert = array(
                        "ControlTable"=>$controlTableName,
                        "ControlTableSerNum"=>$publication["materialId"]["value"],
                        "MetaKey"=>$meta['meta_key']."|lqc_".$publication["materialId"]["value"],
                        "MetaValue"=>implode(',', $meta['meta_value']),
                        "CustomFlag"=>'1',
                        "DateAdded"=>date("Y-m-d H:i:s"),
                    );
                    $result = $this->opalDB->insertReplaceFrequencyEvent($toInsert);
                }
            }
        }
    }

    /*
     * Insert a publication into the matching control, filter and frequency events table after validating and
     * sanitizing the data.
     * @params  array of publication settings and triggers
     * @return  false
     * */
    function insertPublication($publication) {
        $this->checkWriteAccess($publication);
        $publication = HelpSetup::arraySanitization($publication);

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
        else if($moduleDetails["ID"] == MODULE_EDU_MAT) {
            $this->_insertPublicationEduMaterial($publication);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module");

        return false;
    }

    /*
     * Update a publication into the matching control, filter and frequency events table after validating and
     * sanitizing the data.
     * @params  array of publication settings and triggers
     * @return  false
     * */
    function updatePublication($publication) {
        $this->checkWriteAccess($publication);
        $publication = HelpSetup::arraySanitization($publication);

        $moduleDetails = $this->opalDB->getModuleSettings($publication["moduleId"]["value"]);

        $result = $this->_validateTriggers($publication["triggers"], $moduleDetails["ID"]);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Trigger validation failed. " . implode(" ", $result));

        $result = $this->_validateFrequency($publication, $moduleDetails["subModule"], false);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Frequency validation failed. " . implode(" ", $result));

        if($moduleDetails["ID"] == MODULE_QUESTIONNAIRE) {
            $this->_updatePublicationQuestionnaire($publication, $moduleDetails["controlTableName"]);
        }
        else if($moduleDetails["ID"] == MODULE_POST) {
            $this->_updatePublicationPost($publication, $moduleDetails["controlTableName"]);
        }
        else if($moduleDetails["ID"] == MODULE_EDU_MAT) {
            $this->_updateTriggers($publication, $moduleDetails["controlTableName"]);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module");


        return false;
    }

    /*
     * Returns all the filters/triggers for publications
     * @params  void
     * @return  $results (array) filter/triggers found
     * */
    function getFilters() {
        $this->checkReadAccess();
        $results = array();

        $results["patients"] = $this->opalDB->getPatientsTriggers();
        $results["dx"] = $this->opalDB->getDiagnosisTriggers();
        $results["appointments"] = $this->opalDB->getAppointmentsTriggers();
        $results["appointmentStatuses"] = $this->opalDB->getAppointmentsStatusTriggers();
        $results["doctors"] = $this->opalDB->getDoctorsTriggers();
        $results["machines"] = $this->opalDB->getTreatmentMachinesTriggers();
        $results["studies"] = $this->opalDB->getStudiesTriggers();

        foreach($results["doctors"] as &$doctor) {
            $doctor["name"] = ucwords(strtolower($doctor["LastName"] . ", " . preg_replace("/^[Dd][Rr]([.]?[ ]?){1}/", "", $doctor["FirstName"]) . " " . " (" . $doctor["id"] . ")"));
            unset($doctor["FirstName"]);
            unset($doctor["LastName"]);
        }
        return $results;
    }
}