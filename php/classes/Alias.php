<?php

/**
 *   Alias class
 *
 */
class Alias extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_ALIAS, $guestStatus);
    }

    /**
     *
     * Gets a list of expressions from a source database
     *
     * @param int $sourceDBSer : the serial number of the source database
     * @param string $expressionType : the type of expressions to look out for
     * @return array $expressionList : the list of existing expressions
     */
    public function getExpressions ($sourceDBSer, $expressionType) {
        $this->checkReadAccess(array($sourceDBSer, $expressionType));

        $results = array();
//        $databaseObj = new Database();

        try {

            // get already assigned expressions from our database
            $assignedExpressions = $this->getAssignedExpressions($sourceDBSer, $expressionType);

            if ($expressionType == "Task")
                $type = 1;
            else if ($expressionType == "Appointment")
                $type = 2;
            else
                $type = 3;

//            if ($sourceDBSer != ARIA_SOURCE_DB && $sourceDBSer != ORMS_SOURCE_DB && $sourceDBSer != MOSAIQ_SOURCE_DB && $sourceDBSer != LOCAL_SOURCE_DB)
//                $sourceDBSer = ARIA_SOURCE_DB;

            if($sourceDBSer == ARIA_SOURCE_DB)
                $sql = "SELECT description AS name, code AS id, description FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." WHERE type = " . $type . " AND source = " . $sourceDBSer . " AND deleted = 0 ORDER BY code";
            else
                $sql = "SELECT CONCAT(code, ' (', description, ')') AS name, code AS id, description FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." WHERE type = " . $type . " AND source = " . $sourceDBSer . " AND deleted = 0 ORDER BY code";

            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD);
            $host_db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);

            if($sourceDBSer == ARIA_SOURCE_DB)
                foreach ($results as &$item) {
                    $assignedExpression = $this->assignedSearch($item["description"], $item["description"], $assignedExpressions);
                    $item["added"] = 0;
                    if ($assignedExpression)
                        $item['assigned'] = $assignedExpression;
                    else
                        $item['assigned'] = null;
                }
            else
                foreach ($results as &$item) {
                    $assignedExpression = $this->assignedSearch($item["id"], $item["description"], $assignedExpressions);
                    $item["added"] = 0;
                    if ($assignedExpression)
                        $item['assigned'] = $assignedExpression;
                    else
                        $item['assigned'] = null;
                }

            return $results;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets a list of already assigned expressions in our database
     *
     * @param int $sourceDBSer : the serial number of the source database
     * @param string $expressionType : the type of expressions to look out for
     * @return array $diagnoses : the list of diagnoses
     */
    public function getAssignedExpressions ($sourceDBSer, $expressionType) {

        $expressions = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    ae.ExpressionName,
                    ae.Description,
                    Alias.AliasName_EN
                FROM
                    AliasExpression ae,
                    Alias
                WHERE
                    ae.AliasSerNum = Alias.AliasSerNum
                -- AND Alias.AliasType = '$expressionType'
                AND Alias.SourceDatabaseSerNum = '$sourceDBSer'
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $expressionDetails = array (
                    'id'        => $data[0],
                    'description'   => $data[1],
                    'name_EN'   => "$data[2]"
                );
                array_push($expressions, $expressionDetails);
            }

            return $expressions;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }

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
     * @return array
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
//
//        foreach ($result["terms"] as &$term) {
//            $term["added"] = intval($term["added"]);
//        }

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
     *
     * Inserts an alias into the database
     *
     * @param array $aliasDetails : the alias details
     * @return void
     */
    public function insertAlias( $aliasDetails ) {
        $this->checkWriteAccess($aliasDetails);

        $aliasName_EN 	= $aliasDetails['name_EN'];
        $aliasName_FR 	= $aliasDetails['name_FR'];
        $aliasDesc_EN	= $aliasDetails['description_EN'];
        $aliasDesc_FR	= $aliasDetails['description_FR'];
        $aliasType	    = $aliasDetails['type']['name'];
        $aliasColorTag  = $aliasDetails['color'];
        $aliasTerms	    = $aliasDetails['terms'];
        $userSer        = $aliasDetails['user']['id'];
        $sessionId      = $aliasDetails['user']['sessionid'];
        $checkinDetails = isset($aliasDetails['checkin_details']) ? $aliasDetails['checkin_details'] : null;
        $aliasEduMatSer = 'NULL';
        if ( is_array($aliasDetails['edumat']) && isset($aliasDetails['edumat']['serial']) ) {
            $aliasEduMatSer = $aliasDetails['edumat']['serial'];
        }
        $sourceDBSer    = $aliasDetails['source_db']['serial'];
        $hospitalMapSer = 'NULL';
        if ( is_array($aliasDetails['hospitalMap']) && isset($aliasDetails['hospitalMap']['serial']) ) {
            $hospitalMapSer = $aliasDetails['hospitalMap']['serial'];
        }

        $lastTransferred = ( in_array($aliasType, array('Appointment', 'Task') ) ?  "'2000-01-01 00:00:00'" : "'2019-01-01 00:00:00'" );

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				INSERT INTO
					Alias (
						AliasSerNum,
						AliasName_FR,
						AliasName_EN,
						AliasDescription_FR,
                        AliasDescription_EN,
                        EducationalMaterialControlSerNum,
                        HospitalMapSerNum,
                        SourceDatabaseSerNum,
                        AliasType,
                        ColorTag,
                        AliasUpdate,
                        LastUpdatedBy,
                        SessionId,
                        LastTransferred
					)
				VALUES (
					NULL,
					\"$aliasName_FR\",
					\"$aliasName_EN\",
					\"$aliasDesc_FR\",
                    \"$aliasDesc_EN\",
                    $aliasEduMatSer,
                    $hospitalMapSer,
                    '$sourceDBSer',
                    '$aliasType',
                    '$aliasColorTag',
                    '0',
                    '$userSer',
                    '$sessionId',
                    $lastTransferred
				)
			";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $aliasSer = $host_db_link->lastInsertId();

            foreach ($aliasTerms as $aliasTerm) {

                $termName = $aliasTerm['id'];
                $termDesc = $aliasTerm['description'];
                $sql = "
                    INSERT INTO
                        AliasExpression (
                            AliasSerNum,
                            ExpressionName,
                            Description,
                            LastTransferred,
                            LastUpdatedBy,
                            SessionId
                        )
                    VALUE (
                        '$aliasSer',
                        \"$termName\",
                        \"$termDesc\",
                        $lastTransferred,
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

            $this->opalDB->sanitizeEmptyAliases();

            if ($checkinDetails and $aliasType == 'Appointment') {
                $checkinPossible =  $checkinDetails['checkin_possible'];
                $instruction_EN  =  $checkinDetails['instruction_EN'];
                $instruction_FR  =  $checkinDetails['instruction_FR'];

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
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();
            }


        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }


    public function deleteAlias( $post ) {
        $this->checkDeleteAccess($post);



        return false;

        // Initialize a response array
        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        $userSer    = $user['id'];
        $sessionId  = $user['sessionid'];
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                DELETE FROM
                    AliasExpression
                WHERE
                    AliasExpression.AliasSerNum = $aliasSer
			";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
				DELETE FROM
					Alias
				WHERE
					Alias.AliasSerNum = $aliasSer
			";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE AliasMH
                SET
                    AliasMH.LastUpdatedBy = '$userSer',
                    AliasMH.SessionId = '$sessionId'
                WHERE
                    AliasMH.AliasSerNum = $aliasSer
                ORDER BY AliasMH.AliasRevSerNum DESC
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Updates an alias in the database
     *
     * @param array $aliasDetails : the alias details
     * @return array $response : response
     */
    public function updateAlias( $aliasDetails ) {
        $this->checkWriteAccess($aliasDetails);

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

                        //echo $sql;

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
     *
     * Gets chart logs of a alias or aliases
     *
     * @param integer $serial : the alias serial number
     * @param string $type : the alias type
     * @return array $aliasLogs : the alias logs for highcharts
     */
    public function getAliasChartLogs ($serial, $type) {
        $this->checkReadAccess(array($serial, $type));

        $aliasLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // get all logs for all aliases
            if (!$serial and !$type) {
                $aliasSeries = array();

                /* APPOINTMENTS */
                $sql = "
                    SELECT DISTINCT
                        al.AliasName_EN,
                        apmh.CronLogSerNum,
                        COUNT(apmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        Alias al,
                        AliasExpression ae,
                        AppointmentMH apmh,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = apmh.CronLogSerNum
                    AND apmh.CronLogSerNum IS NOT NULL
                    AND apmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum = al.AliasSerNum
                    GROUP BY
                        al.AliasName_EN,
                        apmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[0];
                    $aliasDetail = array (
                        'x' => $data[3],
                        'y' => intval($data[2]),
                        'cron_serial' => $data[1]
                    );
                    if(!isset($aliasSeries[$seriesName])) {
                        $aliasSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($aliasSeries[$seriesName]['data'], $aliasDetail);
                }

                /* DOCUMENTS */
                $sql = "
                    SELECT DISTINCT
                        al.AliasName_EN,
                        docmh.CronLogSerNum,
                        COUNT(docmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        Alias al,
                        AliasExpression ae,
                        DocumentMH docmh,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = docmh.CronLogSerNum
                    AND docmh.CronLogSerNum IS NOT NULL
                    AND docmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum = al.AliasSerNum
                    GROUP BY
                        al.AliasName_EN,
                        docmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[0];
                    $aliasDetail = array (
                        'x' => $data[3],
                        'y' => intval($data[2]),
                        'cron_serial' => $data[1]
                    );
                    if(!isset($aliasSeries[$seriesName])) {
                        $aliasSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($aliasSeries[$seriesName]['data'], $aliasDetail);
                }

                /* TASKS */
                $sql = "
                    SELECT DISTINCT
                        al.AliasName_EN,
                        tmh.CronLogSerNum,
                        COUNT(tmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        Alias al,
                        AliasExpression ae,
                        TaskMH tmh,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = tmh.CronLogSerNum
                    AND tmh.CronLogSerNum IS NOT NULL
                    AND tmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum = al.AliasSerNum
                    GROUP BY
                        al.AliasName_EN,
                        tmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[0];
                    $aliasDetail = array (
                        'x' => $data[3],
                        'y' => intval($data[2]),
                        'cron_serial' => $data[1]
                    );
                    if(!isset($aliasSeries[$seriesName])) {
                        $aliasSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($aliasSeries[$seriesName]['data'], $aliasDetail);
                }

                foreach ($aliasSeries as $seriesName => $series) {
                    array_push($aliasLogs, $series);
                }

            }
            // get logs for specific alias
            else {
                if ($type == 'Appointment') {

                    $sql = "
                        SELECT DISTINCT
                            apmh.CronLogSerNum,
                            COUNT(apmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            AppointmentMH apmh,
                            AliasExpression ae,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = apmh.CronLogSerNum
                        AND apmh.CronLogSerNum IS NOT NULL
                        AND apmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                        AND ae.AliasSerNum = $serial
                        GROUP BY
                            apmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY
                            cl.CronDateTime ASC
                    ";

                }

                else if ($type == 'Document') {
                    $sql = "
                        SELECT DISTINCT
                            docmh.CronLogSerNum,
                            COUNT(docmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            DocumentMH docmh,
                            AliasExpression ae,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = docmh.CronLogSerNum
                        AND docmh.CronLogSerNum IS NOT NULL
                        AND docmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                        AND ae.AliasSerNum = $serial
                        GROUP BY
                            docmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY
                            cl.CronDateTime ASC
                    ";
                }

                else if ($type == 'Task') {
                    $sql = "
                        SELECT DISTINCT
                            taskmh.CronLogSerNum,
                            COUNT(taskmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            TaskMH taskmh,
                            AliasExpression ae,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = taskmh.CronLogSerNum
                        AND taskmh.CronLogSerNum IS NOT NULL
                        AND taskmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
                        AND ae.AliasSerNum = $serial
                        GROUP BY
                            taskmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY
                            cl.CronDateTime ASC
                    ";
                }
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $aliasSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $type;
                    $aliasDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($aliasSeries[$seriesName])) {
                        $aliasSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($aliasSeries[$seriesName]['data'], $aliasDetail);
                }

                foreach ($aliasSeries as $seriesName => $series) {
                    array_push($aliasLogs, $series);
                }
            }
            return $aliasLogs;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets list logs of appointments/documents/tasks during one or many cron sessions
     *
     */
    public function getAliasListLogs($aliasIds, $type) {
        $aliasLogs = array();
        foreach($aliasIds as &$id) {
            $id = intval($id);
        }
        if (!$type)
            $aliasLogs = $this->opalDB->getAliasesLogs($aliasIds);
        else if ($type == 'Appointment')
            $aliasLogs = $this->opalDB->getAppointmentsLogs($aliasIds);
        else if ($type == 'Document')
            $aliasLogs = $this->opalDB->getDocumentsLogs($aliasIds);
        else if ($type == 'Task')
            $aliasLogs = $this->opalDB->getTasksLogs($aliasIds);

        return $aliasLogs;
    }

    /**
     *
     * Does a nested search for match
     *
     * @param string $id    : the needle id
     * @param string $description  : the needle description
     * @param array $array  : the key-value haystack
     * @return boolean
     */
    public function nestedSearch($id, $description, $array) {
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
     *
     * Checks if an expression has been assigned to an alias
     *
     * @param string $id    : the needle id
     * @param string $description  : the needle description
     * @param array $array  : the key-value haystack
     * @return $assignedAlias
     */
    public function assignedSearch($id, $description, $array) {
        $assignedAlias = null;
        if(empty($array) || !$id){
            return $assignedAlias;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id and $val['description'] === $description) {
                $assignedAlias = $val;
                return $assignedAlias;
            }
        }
        return $assignedAlias;
    }

    /*
     * Get the list of educational materials an alias can assign to.
     * @params  void
     * @returns array - list of available educational material an alias has access
     * */
    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }

    /*
     * Get the list of hospital maps an alias can assign to.
     * @params  void
     * @returns array - list of available hospital maps an alias has access
     * */
    public function getHospitalMaps() {
        $this->checkReadAccess();
        return $this->opalDB->getHospitalMaps();
    }
}