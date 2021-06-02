<?php

/**
 *   Alias class
 *
 */
class Alias extends Module {

    /**
     * Alias constructor.
     * @param boolean $guestStatus
     */
    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_ALIAS, $guestStatus);
    }

    /**
     * Get the list of alias expressions ans mark those already assigned.
     * @param $sourceDatabaseId int - Source database ID or sernum
     * @param $aliasType string - type of alias (Task, Appointment or Document)
     * @return array - list of results
     */
    public function getExpressions ($sourceDatabaseId, $aliasType) {
        $this->checkReadAccess(array($sourceDatabaseId, $aliasType));

        $aa = $this->opalDB->getAliasExpressions($sourceDatabaseId);

        if ($aliasType == ALIAS_TYPE_TASK_TEXT)
            $type = ALIAS_TYPE_TASK;
        else if ($aliasType == ALIAS_TYPE_APPOINTMENT_TEXT)
            $type = ALIAS_TYPE_APPOINTMENT;
        else if ($aliasType == ALIAS_TYPE_DOCUMENT_TEXT)
            $type = ALIAS_TYPE_DOCUMENT;
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Wrong alias type.");

        $results = $this->opalDB->getSourceAliasesByTypeAndSource($type, $sourceDatabaseId);

        foreach ($results as &$item)
            $item["added"] = 0;

        return $results;
    }

    /**
     * Validate a list of publication flags for patient.
     * @param $post - publish flag to validate
     * @return string - string to convert in int for error code
     */
    protected function _validatePublishFlag(&$post) {
        $errCode = "";
        if (is_array($post) && array_key_exists("data", $post) && is_array($post["data"])) {
            $errFound = false;
            foreach ($post["data"] as $item) {
                if (!array_key_exists("serial", $item) || $item["serial"] == "" || !array_key_exists("update", $item) || $item["update"] == "") {
                    $errFound = true;
                    break;
                }
            }
            if ($errFound)
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode = "1";
        return $errCode;
    }

    /**
     * Update the publication flags of a list of aliases
     * @param $post
     */
    public function updateAliasPublishFlags($post) {
        $this->checkWriteAccess($post);
        HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePublishFlag($post);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($post["data"] as $item)
            $this->opalDB->updateAliasPublishFlag($item["serial"], $item["update"]);

        $this->opalDB->sanitizeEmptyAliases();
    }

    /**
     * Return the list of aliases and they expressions
     * @return array - results found
     */
    public function getAliases() {
        $this->checkReadAccess();
        $result = $this->opalDB->getAliases();
        foreach ($result as &$alias) {
            $alias["source_db"] = array('serial' => $alias["sd_serial"], 'name' => $alias["sd_name"]);
            unset($alias["sd_serial"]);
            unset($alias["sd_name"]);
        }
        return $result;
    }

    /**
     * Get the details of a specific alias. If the alias does not exists, return an error 400 and validation 1.
     * @param $post - array - must contains serial entry
     * @return array
     */
    public function getAliasDetails($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $id = intval($post["serial"]);

        $result = $this->opalDB->getAliasDetails($id);
        if (count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => 1)));
        else if (count($result) == 1)
            $result = $result[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates test results found.");

        $result["source_db"] = array("serial"=>$result["SourceDatabaseSerNum"], "name"=>$result["SourceDatabaseName"]);

        $result["checkin_details"] = ($result["checkin_possible"] != "" ? array("checkin_possible"=>$result["checkin_possible"], "instruction_EN"=>$result["instruction_EN"], "instruction_FR"=>$result["instruction_FR"]) : array());

        $result["eduMat"] = ($result["eduMatSer"] != "" ? $this->_getEducationalMaterialDetails($result["eduMatSer"]) : "");
        $result["terms"] = $this->opalDB->getAliasExpression($result["serial"]);
        $result["deactivated"] = $this->opalDB->getAliasExpression($result["serial"], DELETED_RECORD);

        $result["count"] = count($result["terms"]);
        $result["hospitalMap"] = ($result["hospitalMapSer"] != "" ? $this->opalDB->getHospitalMapDetails($result["hospitalMapSer"]) : "");

        // Unset unused values
        unset($result["checkin_possible"]);
        unset($result["instruction_EN"]);
        unset($result["instruction_FR"]);
        unset($result["SourceDatabaseSerNum"]);
        unset($result["SourceDatabaseName"]);

        return $result;
    }

    /**
     * Validate and sanitize an alias
     * Validation code :    Error validation code is coded as an int of 12 bits (value from 0 to 4095). Bit informations
     *                      are coded from right to left:
     *                      1: type of alias missing or invalid
     *                      2: checkin details missing or invalid
     *                      3: hospital map missing or invalid
     *                      4: color missing or invalid
     *                      5: english description missing
     *                      6: french description missing
     *                      7: educational material (if present) invalid
     *                      8: english name missing
     *                      9: french name missing
     *                      10: source database missing or invalid
     *                      11: list of alias expression missing or invalid
     *                      12: alias ID is missing or invalid if it is an update
     * @param $post array - data for the alias to validate
     * @param $isAnUpdate boolean - if the validation must include the ID of the alias or not
     * @return string - validation code
     */
    protected function _validateAndSanitizeAlias(&$post, $isAnUpdate = false) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("type", $post) || $post["type"] == "" ||
                ($post["type"] != ALIAS_TYPE_APPOINTMENT_TEXT && $post["type"] != ALIAS_TYPE_DOCUMENT_TEXT && $post["type"] != ALIAS_TYPE_TASK_TEXT))
                $errCode = "001" . $errCode;
            else {
                $errCode = "0" . $errCode;

                // 2nd bit
                if (array_key_exists("checkin_details", $post) && $post["checkin_details"] != "") {

                    if ($post["type"] != ALIAS_TYPE_APPOINTMENT_TEXT || !array_key_exists("checkin_details", $post) || $post["checkin_details"] == "" ||
                        !array_key_exists("checkin_possible", $post["checkin_details"]) || $post["checkin_details"]["checkin_possible"] == "" ||
                        ($post["checkin_details"]["checkin_possible"] != 0 && $post["checkin_details"]["checkin_possible"] != 1) ||
                        !array_key_exists("instruction_EN", $post["checkin_details"]) || $post["checkin_details"]["instruction_EN"] == "" ||
                        !array_key_exists("instruction_FR", $post["checkin_details"]) || $post["checkin_details"]["instruction_FR"] == ""
                    )
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                }
                else
                    $errCode = "0" . $errCode;

                // 3rd bit
                if (array_key_exists("hospitalMap", $post) && $post["hospitalMap"] != "") {
                    if($post["type"] != ALIAS_TYPE_APPOINTMENT_TEXT)
                        $errCode = "1" . $errCode;
                    else {
                        $total = $this->opalDB->countHospitalMap($post["hospitalMap"]);
                        $total = intval($total["total"]);
                        if ($total <= 0)
                            $errCode = "1" . $errCode;
                        else if ($total == 1)
                            $errCode = "0" . $errCode;
                        else
                            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Duplicate hospital maps found.");
                    }
                } else
                    $errCode = "0" . $errCode;
            }

            // 4th bit
            if (!array_key_exists("color", $post) || $post["color"] == "" || !preg_match("/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/", $post["color"]))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 5th bit
            if (!array_key_exists("description_EN", $post) || $post["description_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 6th bit
            if (!array_key_exists("description_FR", $post) || $post["description_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 7th bit
            if (array_key_exists("eduMat", $post) && $post["eduMat"] != "") {
                $total = $this->opalDB->countEduMaterial($post["eduMat"]);
                $total = intval($total["total"]);
                if($total <= 0)
                    $errCode = "1" . $errCode;
                else if($total == 1)
                    $errCode = "0" . $errCode;
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Duplicate edu material found.");
            } else
                $errCode = "0" . $errCode;

            // 8th bit
            if (!array_key_exists("name_EN", $post) || $post["name_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 9th bit
            if (!array_key_exists("name_FR", $post) || $post["name_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 10th bit
            if (!array_key_exists("source_db", $post) || $post["source_db"] == "")
                $errCode = "1" . $errCode;
            else {
                $total = $this->opalDB->countSourceDatabase($post["source_db"]);
                $total = intval($total["total"]);
                if($total <= 0)
                    $errCode = "1" . $errCode;
                else if($total == 1)
                    $errCode = "0" . $errCode;
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Duplicate source database found.");
            }

            // 11th bit
            if (!array_key_exists("terms", $post) || (!is_array($post["terms"])))
                $errCode = "1" . $errCode;
            else {
                $listIds = array();
                foreach ($post["terms"] as $term)
                    array_push($listIds, intval($term));

                $total = $this->opalDB->selectAliasExpressionsToInsert($listIds);
                $post["terms"] = $listIds;
                if(count($total) != count($post["terms"]))
                    $errCode = "1" . $errCode;
                else {
                    $post["terms"] = $total;
                    $errCode = "0" . $errCode;
                }
            }

            // 12th bit
            if($isAnUpdate) {
                if (!array_key_exists("id", $post) || $post["id"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $result = $this->opalDB->getAliasDetails($post["id"]);
                    if (count($result) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($result) == 1)
                        $errCode = "0" . $errCode;
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates aliases found.");
                }
            } else
                $errCode = "0" . $errCode;

        } else
            $errCode = "111111111111";

        return $errCode;
    }

    /**
     * Insert/replace list of alias expressions for an alias
     * @param $aliasExpressions array - list of alias expressions
     * @param $aliasId int - Alias ID (or sernum)
     * @param $lastTransferred string - date of last transferred to use
     */
    protected function _replaceAliasExpressions(&$aliasExpressions, &$aliasId, &$lastTransferred = "") {
        $toInsert = array();
        foreach($aliasExpressions as $item) {
            if($lastTransferred != "")
            array_push($toInsert, array(
                "AliasSerNum"=>$aliasId,
                "masterSourceAliasId"=>$item['ID'],
                "ExpressionName"=>$item['code'],
                "Description"=>$item['description'],
                "LastTransferred"=>$lastTransferred,
            ));
            else
                array_push($toInsert, array(
                    "AliasSerNum"=>$aliasId,
                    "masterSourceAliasId"=>$item['ID'],
                    "ExpressionName"=>$item['code'],
                    "Description"=>$item['description']
                ));
        }

        $this->opalDB->replaceMultipleAliasExpressions($toInsert);
    }

    /**
     * Insert/Replace appointment checkin details
     * @param $checkin_details array - contains the checkin details
     * @param $aliasId - Alias ID (or sernum) to associate the checkin details
     */
    protected function _replaceAppointmentCheckin(&$checkin_details, &$aliasId) {
        $toInsert = array(
            "AliasSerNum"=>$aliasId,
            "CheckinPossible"=>$checkin_details["checkin_possible"],
            "CheckinInstruction_EN"=>$checkin_details["instruction_EN"],
            "CheckinInstruction_FR"=>$checkin_details["instruction_FR"],
            "DateAdded"=>date("Y-m-d H:i:s"),
        );
        $this->opalDB->replaceAppointmentCheckin($toInsert);
    }

    /**
     * Validate and insert a new alias. If alias is an appointment, insert appointment check in too.
     * @param $post array - details of the alias to insert after validation and sanitization
     */
    public function insertAlias($post) {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateAndSanitizeAlias($post);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $lastTransferred = ( in_array($post["type"], array(ALIAS_TYPE_TASK_TEXT, ALIAS_TYPE_APPOINTMENT_TEXT) ) ?  "2000-01-01 00:00:00" : "2019-01-01 00:00:00" );

        $toInsert = array(
            "AliasType"=>$post["type"],
            "AliasName_FR"=>$post["name_FR"],
            "AliasName_EN"=>$post["name_EN"],
            "AliasDescription_FR"=>$post["description_FR"],
            "AliasDescription_EN"=>$post["description_EN"],
            "SourceDatabaseSerNum"=>$post["source_db"],
            "ColorTag"=>$post["color"],
            "LastTransferred"=>$lastTransferred,
        );

        if (array_key_exists("eduMat", $post) && $post["eduMat"] != "")
            $toInsert["EducationalMaterialControlSerNum"] = $post["eduMat"];

        if (array_key_exists("hospitalMap", $post) && $post["hospitalMap"] != "")
            $toInsert["HospitalMapSerNum"] = $post["hospitalMap"];

        $newAliasId = $this->opalDB->insertAlias($toInsert);

        $this->_replaceAliasExpressions($post["terms"], $newAliasId, $lastTransferred);

        if($post["type"] == ALIAS_TYPE_APPOINTMENT_TEXT)
            $this->_replaceAppointmentCheckin($post["checkin_details"], $newAliasId);
    }

    public function updateAlias( $post ) {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateAndSanitizeAlias($post);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $toUpdate = array(
            "AliasSerNum"=>$post["id"],
            "AliasName_EN"=>$post["name_EN"],
            "AliasName_FR"=>$post["name_FR"],
            "AliasDescription_EN"=>$post["description_EN"],
            "AliasDescription_FR"=>$post["description_FR"],
            "ColorTag"=>$post["color"],
            "EducationalMaterialControlSerNum"=>(array_key_exists("eduMat", $post) && $post["eduMat"] !="" ? $post["eduMat"] : null),
            "HospitalMapSerNum"=>(array_key_exists("hospitalMap", $post) && $post["hospitalMap"] !="" ? $post["hospitalMap"] : null),
        );

        $this->opalDB->updateAlias($toUpdate);

        $existingAliasExpressions = array();
        foreach ($post["terms"] as $diagnosis)
            array_push($existingAliasExpressions, $diagnosis["ID"]);

        $this->opalDB->deleteAliasExpressions($post["serial"], $existingAliasExpressions);
        $this->_replaceAliasExpressions($post["terms"], $post["id"]);

        die();

        $aliasName_EN 	= $aliasDetails['name_EN'];
        $aliasName_FR 	= $aliasDetails['name_FR'];
        $aliasDesc_EN	= $aliasDetails['description_EN'];
        $aliasDesc_FR	= $aliasDetails['description_FR'];
        $aliasSer	    = $aliasDetails['serial'];
        $aliasTerms	    = $aliasDetails['terms'];
        $aliasEduMatSer = $aliasDetails['edumatser'] ? $aliasDetails['edumatser'] : 'NULL';
        $hospitalMapSer = $aliasDetails['hospitalMapSer'] ? $aliasDetails['hospitalMapSer'] : 'NULL';
        $checkinDetails = $aliasDetails['checkin_details'] ? $aliasDetails['checkin_details'] : null;

        $aliasColorTag  = $aliasDetails['color'];

        $userSer        = $aliasDetails['user']['id'];
        $sessionId      = $aliasDetails['user']['sessionid'];

        $existingTerms	= array();

        $detailsUpdated = $aliasDetails['details_updated'];
        $expressionsUpdated = $aliasDetails['expressions_updated'];
        $checkinDetailsUpdated = $aliasDetails['checkin_details_updated'];

        // Initialize a response array
        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            if ($detailsUpdated) {
                $sql = "
    				UPDATE
    					Alias
    				SET
    					Alias.AliasName_EN 		                = \"$aliasName_EN\",
    					Alias.AliasName_FR 		                = \"$aliasName_FR\",
    					Alias.AliasDescription_EN	            = \"$aliasDesc_EN\",
                        Alias.AliasDescription_FR	            = \"$aliasDesc_FR\",
                        Alias.EducationalMaterialControlSerNum  = $aliasEduMatSer,
                        Alias.HospitalMapSerNum                 = $hospitalMapSer,
                        Alias.ColorTag                          = '$aliasColorTag',
                        Alias.LastUpdatedBy                     = '$userSer',
                        Alias.SessionId                         = '$sessionId'
    				WHERE
    					Alias.AliasSerNum = $aliasSer
    			";

                $query = $host_db_link->prepare( $sql );
                $query->execute();
            }

            if ($checkinDetailsUpdated) {
                $checkinPossible = $checkinDetails['checkin_possible'];
                $instruction_EN = $checkinDetails['instruction_EN'];
                $instruction_FR = $checkinDetails['instruction_FR'];

                $sql = "
                    INSERT INTO
                        AppointmentCheckin (
                            AliasSerNum,
                            CheckinPossible,
                            CheckinInstruction_EN,
                            CheckinInstruction_FR,
                            DateAdded,
                            LastUpdatedBy,
                            SessionId
                        )
                    VALUE (
                        '$aliasSer',
                        '$checkinPossible',
                        \"$instruction_EN\",
                        \"$instruction_FR\",
                        NOW(),
                        '$userSer',
                        '$sessionId'
                    )
										ON DUPLICATE KEY UPDATE
											AliasSerNum = '$aliasSer',
											CheckinPossible = '$checkinPossible',
											CheckinInstruction_EN = \"$instruction_EN\",
											CheckinInstruction_FR = \"$instruction_FR\",
											LastUpdatedBy = '$userSer',
											SessionId = '$sessionId';
                ";

                // $sql = "
                //     UPDATE
                //         AppointmentCheckin
                //     SET
                //         AppointmentCheckin.CheckinPossible          = '$checkinPossible',
                //         AppointmentCheckin.CheckinInstruction_EN    = \"$instruction_EN\",
                //         AppointmentCheckin.CheckinInstruction_FR    = \"$instruction_FR\",
                //         AppointmentCheckin.LastUpdatedBy            = '$userSer',
                //         AppointmentCheckin.SessionId                = '$sessionId'
                //     WHERE
                //         AppointmentCheckin.AliasSerNum = $aliasSer
                // ";

                $query = $host_db_link->prepare( $sql );
                $query->execute();
            }

            if ($expressionsUpdated) {

                $sql = "
    				SELECT DISTINCT
    					AliasExpression.ExpressionName,
                        AliasExpression.Description
    				FROM
    					AliasExpression
    				WHERE
    					AliasExpression.AliasSerNum = $aliasSer
    			";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $termArray = array(
                        'id'          => $data[0],
                        'description'   => $data[1]
                    );
                    array_push($existingTerms, $termArray);

                }

                // This loop compares the old terms with the new
                // If old terms not in new, then remove old
                foreach ($existingTerms as $existingTerm) {
                    $existingTermName = $existingTerm['id'];
                    $existingTermDesc = $existingTerm['description'];
                    if (!$this->nestedSearch($existingTermName, $existingTermDesc, $aliasTerms)) {
                        $sql = "
                            DELETE FROM
    							AliasExpression
    						WHERE
                                AliasExpression.ExpressionName = \"$existingTermName\"
                            AND AliasExpression.Description = \"$existingTermDesc\"
                            AND AliasExpression.AliasSerNum = $aliasSer
    					";

                        $query = $host_db_link->prepare( $sql );
                        $query->execute();

                        $sql = "
                            UPDATE AliasExpressionMH
                            SET
                                AliasExpressionMH.LastUpdatedBy = '$userSer',
                                AliasExpressionMH.SessionId = '$sessionId'
                            WHERE
                                AliasExpressionMH.ExpressionName = \"$existingTermName\"
                            AND AliasExpressionMH.Description = \"$existingTermDesc\"
                            ORDER BY AliasExpressionMH.RevSerNum DESC
                            LIMIT 1
                        ";
                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
                    }
                }

                // If new terms, then insert
                foreach ($aliasTerms as $term) {
                    $termName = $term['id'];
                    $termDesc = $term['description'];
                    if (!$this->nestedSearch($termName, $termDesc, $existingTerms)) {
                        $sql = "
                            INSERT INTO
                                AliasExpression (
                                    AliasExpressionSerNum,
                                    AliasSerNum,
                                    ExpressionName,
                                    Description,
                                    LastUpdatedBy,
                                    SessionId
                                )
                            VALUES (
                                NULL,
                                '$aliasSer',
                                \"$termName\",
                                \"$termDesc\",
                                '$userSer',
                                '$sessionId'
                            )
                            ON DUPLICATE KEY UPDATE
                                AliasSerNum = '$aliasSer',
                                LastUpdatedBy = '$userSer',
                                SessionId = '$sessionId'
    					";
                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
                    }
                }
            }

            $this->opalDB->sanitizeEmptyAliases();

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     * Get the list of all active source databases
     * @return array
     */
    public function getSourceDatabases () {
        $this->checkReadAccess();
        return $this->opalDB->getSourceDatatabes();
    }

    /**
     * Get the chart logs for all aliases or for a specific one
     * @param $post array - May contains AliasId and the typy
     * @return array - results found
     */
    public function getAliasChartLogs ($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $serial = $post["serial"];
        $type = $post["type"];

        $aliasLogs = array();
        if (!$serial && !$type) {
            $aliasSeries = array();
            $results = $this->opalDB->getAliasLogs();

            foreach ($results as $result) {
                $seriesName = $result["name"];
                $aliasDetail = array (
                    'x' => $result["x"],
                    'y' => intval($result["y"]),
                    'cron_serial' => $result["cron_serial"]
                );
                if(!isset($aliasSeries[$seriesName]))
                    $aliasSeries[$seriesName] = array(
                        'name'  => $seriesName,
                        'data'  => array()
                    );
                array_push($aliasSeries[$seriesName]['data'], $aliasDetail);
            }

        } else {
            $aliasSeries = array();
            if ($type == ALIAS_TYPE_APPOINTMENT_TEXT)
                $results = $this->opalDB->getAppointmentLogs($serial);
            else if ($type == ALIAS_TYPE_DOCUMENT_TEXT)
                $results = $this->opalDB->getDocumentLogs($serial);
            else if ($type == ALIAS_TYPE_TASK_TEXT)
                $results = $this->opalDB->getTaskLogs($serial);
            else
                HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Wrong alias type.");

            foreach ($results as $result) {
                $seriesName = $type;

                if(!isset($aliasSeries[$seriesName])) {
                    $aliasSeries[$seriesName] = array(
                        'name'  => $seriesName,
                        'data'  => array()
                    );
                }
                array_push($aliasSeries[$seriesName]['data'], array (
                    'x' => $result["x"],
                    'y' => intval($result["y"]),
                    'cron_serial' => $result["cron_serial"]
                ));
            }

        }
        foreach ($aliasSeries as $seriesName => $series)
            array_push($aliasLogs, $series);

        return $aliasLogs;
    }

    /**
     * Get the logs of a specific list of aliases
     * @param $post array - contains the list of ids and the type of aliases
     * @return array - list of logs for a list of ids
     */
    public function getAliasListLogs($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);

        $aliasIds = json_decode($post['serials']);
        $type = ( $_POST['type'] === 'undefined' ) ? null : $_POST['type'];

        $aliasLogs = array();
        $validIds = array();
        foreach($aliasIds as $id)
            array_push($validIds, intval($id));

        if(count($validIds) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "No alias IDs.");

        if (!$type)
            $aliasLogs = $this->opalDB->getAliasesLogs($validIds);
        else if ($type == ALIAS_TYPE_APPOINTMENT_TEXT)
            $aliasLogs = $this->opalDB->getAppointmentsLogs($validIds);
        else if ($type == ALIAS_TYPE_DOCUMENT_TEXT)
            $aliasLogs = $this->opalDB->getDocumentsLogs($validIds);
        else if ($type == ALIAS_TYPE_TASK_TEXT)
            $aliasLogs = $this->opalDB->getTasksLogs($validIds);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, "Wrong alias type.");

        return $aliasLogs;
    }

    protected function nestedSearch($id, $description, $array) {
        if(empty($array) || !$id || !$description){
            return 0;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id and $val['description'] === $description) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Get the list of educational materials an alias can assign to.
     * @return array - list of available educational material an alias has access
     */
    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }

    /**
     * Get the list of hospital maps an alias can assign to.
     * @return false - list of available hospital maps an alias has access
     */
    public function getHospitalMaps() {
        $this->checkReadAccess();
        return $this->opalDB->getHospitalMaps();
    }
}