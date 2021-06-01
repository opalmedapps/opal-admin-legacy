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
        $databaseObj = new Database();

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
     *
     * Updates Alias publish flags in our database
     *
     * @param array $aliasList : a list of aliases
     * @param object $user : the session user
     * @return array $response : response
     */
    public function updateAliasPublishFlags( $aliasList, $user ) {
        $this->checkWriteAccess(array($aliasList, $user));

        // Initialize a response array
        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            foreach ($aliasList as $alias) {

                $aliasUpdate    = $alias['update'];
                $aliasSer       = $alias['serial'];

                $sql = "
					UPDATE
						Alias
					SET
						Alias.AliasUpdate = $aliasUpdate,
                        Alias.LastUpdatedBy = $userSer,
                        Alias.SessionId = '$sessionId'
					WHERE
						Alias.AliasSerNum = $aliasSer
				";

                $query = $host_db_link->prepare( $sql );
                $query->execute();

                // Also update the cronControlAlias table for our modular cron refactor, 2021-05-31
                $this->opalDB->updateCronControlAliasPublishFlag($aliasSer, $aliasUpdate);
            }

            $this->sanitizeEmptyAliases($user);

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Removes publish flag for aliases without assigned terms
     *
     * @param object $user : the session user
     * @return void
     */
    public function sanitizeEmptyAliases($user) {
        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    Alias.AliasSerNum
                FROM
                    Alias
                LEFT JOIN
                    AliasExpression
                ON  Alias.AliasSerNum = AliasExpression.AliasSerNum
                 WHERE
                    AliasExpression.AliasSerNum IS NULL
                AND Alias.AliasUpdate != 0
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasSer = $data[0];

                $sql = "
                    UPDATE
                        Alias
                    SET
                        Alias.AliasUpdate       = 0,
                        Alias.LastUpdatedBy     = $userSer,
                        Alias.SessionId         = '$sessionId'
                    WHERE
                        Alias.AliasSerNum       = $aliasSer
                ";

                $secondQuery = $host_db_link->prepare( $sql );
                $secondQuery->execute();

                // update cronControlAlias in parallel
                $this->opalDB->updateCronControlAliasSanitizeEmpty($aliasSer);
            }
            return;
        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets a list of existing color tags
     *
     * @param string $aliasType : the alias type
     * @return array $colorTags : the list of existing color tags
     */
    public function getColorTags($aliasType) {
        $this->checkReadAccess($aliasType);

        $colorTags = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT
                    Alias.AliasName_EN,
                    Alias.AliasName_FR,
                    Alias.ColorTag
                FROM
                    Alias
                WHERE
                    Alias.AliasType = '$aliasType'
                ORDER BY
                    Alias.AliasName_EN
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasName_EN       = $data[0];
                $aliasName_FR       = $data[1];
                $colorTag           = $data[2];

                $colorArray = array(
                    'name_EN'   => $aliasName_EN,
                    'name_FR'   => $aliasName_FR,
                    'color'     => $colorTag
                );

                array_push($colorTags, $colorArray);
            }

            return $colorTags;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets a list of existing aliases
     *
     * @return array $aliasList : the list of existing aliases
     */
    public function getAliases() {
        $this->checkReadAccess();
        $aliasList = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $activeDB = Database::getActiveSourceDatabases();

            $sql = "
				SELECT DISTINCT
					Alias.AliasSerNum,
					Alias.AliasType,
					Alias.AliasName_FR,
					Alias.AliasName_EN,
					Alias.AliasDescription_FR,
                    Alias.AliasDescription_EN,
                    Alias.AliasUpdate,
                    Alias.EducationalMaterialControlSerNum,
                    Alias.SourceDatabaseSerNum,
                    SourceDatabase.SourceDatabaseName,
                    Alias.ColorTag,
                    Alias.LastUpdated
				FROM
                    Alias,
                    SourceDatabase
                WHERE
                    Alias.SourceDatabaseSerNum = SourceDatabase.SourceDatabaseSerNum
			";

            if(count($activeDB) > 0)
                $sql .= " AND Alias.SourceDatabaseSerNum IN (".implode(", ", $activeDB).")";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasSer 	    = $data[0];
                $aliasType	    = $data[1];
                $aliasName_FR	= $data[2];
                $aliasName_EN	= $data[3];
                $aliasDesc_FR	= $data[4];
                $aliasDesc_EN	= $data[5];
                $aliasUpdate    = $data[6];
                $aliasEduMatSer = $data[7];
                $sourceDatabase = array(
                    'serial'    => $data[8],
                    'name'      => $data[9]
                );
                $aliasColorTag  = $data[10];
                $aliasLU        = $data[11];
                $aliasTerms	    = array();
                $aliasEduMat    = "";

                $sql = "
					SELECT DISTINCT
						AliasExpression.ExpressionName,
                        AliasExpression.Description
					FROM
						Alias,
						AliasExpression
					WHERE
						Alias.AliasSerNum 		        = $aliasSer
					AND AliasExpression.AliasSerNum 	= Alias.AliasSerNum
				";

                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $secondQuery->execute();

                while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $termName = $secondData[0];
                    $termDesc = $secondData[1];
                    $termArray = array(
                        'id' => $termName,
                        'description' => $termDesc,
                        'added'=> 1
                    );

                    array_push($aliasTerms, $termArray);
                }

                if ($aliasEduMatSer != 0) {
                    $aliasEduMat = $this->_getEducationalMaterialDetails($aliasEduMatSer);
                }

                $aliasArray = array(
                    'name_FR' 		    => $aliasName_FR,
                    'name_EN' 		    => $aliasName_EN,
                    'serial' 		    => $aliasSer,
                    'type'			    => $aliasType,
                    'color'             => $aliasColorTag,
                    'update'            => $aliasUpdate,
                    'changed'           => 0,
                    'eduMatSer'         => $aliasEduMatSer,
                    'eduMat'            => $aliasEduMat,
                    'description_EN' 	=> $aliasDesc_EN,
                    'description_FR' 	=> $aliasDesc_FR,
                    'source_db'         => $sourceDatabase,
                    'lastupdated'       => $aliasLU,
                    'count' 		    => count($aliasTerms),
                    'terms' 		    => $aliasTerms
                );

                array_push($aliasList, $aliasArray);
            }
            return $aliasList;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets details for one particular alias
     *
     * @param integer $aliasSer : the alias serial number
     * @return array $aliasDetails : the alias details
     */
    public function getAliasDetails($aliasSer) {
        $this->checkReadAccess($aliasSer);
        $aliasDetails = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
				SELECT DISTINCT
					Alias.AliasType,
					Alias.AliasName_FR,
					Alias.AliasName_EN,
					Alias.AliasDescription_FR,
                    Alias.AliasDescription_EN,
                    Alias.AliasUpdate,
                    Alias.EducationalMaterialControlSerNum,
                    Alias.SourceDatabaseSerNum,
                    SourceDatabase.SourceDatabaseName,
                    Alias.ColorTag,
                    Alias.HospitalMapSerNum
				FROM
                    Alias,
                    SourceDatabase
				WHERE
                    Alias.AliasSerNum                       = $aliasSer
                AND SourceDatabase.SourceDatabaseSerNum     = Alias.SourceDatabaseSerNum

			";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $aliasType	    = $data[0];
            $aliasName_FR	= $data[1];
            $aliasName_EN	= $data[2];
            $aliasDesc_FR	= $data[3];
            $aliasDesc_EN	= $data[4];
            $aliasUpdate    = $data[5];
            $aliasEduMatSer = $data[6];
            $sourceDatabase = array(
                'serial'    => $data[7],
                'name'      => $data[8]
            );
            $aliasColorTag  = $data[9];
            $hospitalMapSer = $data[10];

            $aliasEduMat    = "";
            $hospitalMap    = "";
            $aliasTerms	    = array();

            $checkinDetails = $this->getCheckinDetails($aliasSer, $aliasType);

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

                $termName = $data[0];
                $termDesc = $data[1];
                $termArray = array(
                    'id' => $termName,
                    'description' => $termDesc,
                    'added'=> 1
                );

                array_push($aliasTerms, $termArray);
            }

            if ($aliasEduMatSer) {
                $aliasEduMat = $this->_getEducationalMaterialDetails($aliasEduMatSer);
            }

            if ($hospitalMapSer) {
                $hospitalMap = $hosMapDetails = $this->opalDB->getHospitalMapDetails(intval($hospitalMapSer));
            }

            $aliasDetails = array(
                'name_FR' 		    => $aliasName_FR,
                'name_EN' 		    => $aliasName_EN,
                'serial' 		    => $aliasSer,
                'type'			    => $aliasType,
                'color'             => $aliasColorTag,
                'update'            => $aliasUpdate,
                'eduMatSer'         => $aliasEduMatSer,
                'eduMat'            => $aliasEduMat,
                'description_EN' 	=> $aliasDesc_EN,
                'description_FR' 	=> $aliasDesc_FR,
                'source_db'         => $sourceDatabase,
                'count' 		    => count($aliasTerms),
                'terms' 		    => $aliasTerms,
                'checkin_details'   => $checkinDetails,
                'hospitalMapSer'    => $hospitalMapSer,
                'hospitalMap'       => $hospitalMap
            );

            return $aliasDetails;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
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
            // update cronControlAlias in parallel
            $this->opalDB->updateCronControlAliasInsert($aliasSer, $lastTransferred, $aliasType);

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

            $this->sanitizeEmptyAliases($aliasDetails['user']);

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

    /**
     *
     * Deletes an alias from the database
     *
     * @param integer $aliasSer : the alias serial number
     * @param object $user : the session user
     * @return array $response : response
     */
    public function deleteAlias( $aliasSer, $user ) {
        $this->checkDeleteAccess(array($aliasSer, $user));

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

            
            // update cronControlAlias in parallel
            $this->opalDB->updateCronControlAliasDelete($aliasSer);


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

            $this->sanitizeEmptyAliases($aliasDetails['user']);

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets a list of source databases
     *
     * @return array $sourceDBList : the list of source databases
     */
    public function getSourceDatabases () {
        $this->checkReadAccess();
        $sourceDBList = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT
                    sd.SourceDatabaseSerNum,
                    sd.SourceDatabaseName
                FROM
                    SourceDatabase sd
                WHERE
                    sd.Enabled = 1
                ORDER BY
                    sd.SourceDatabaseSerNum
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $sourceDBArray = array(
                    'serial'    => $data[0],
                    'name'      => $data[1]
                );

                array_push($sourceDBList, $sourceDBArray);

            }

            return $sourceDBList;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets appointment checkin details
     *
     * @param integer $serial : the alias serial number
     * @param string $type : the alias type
     * @return array $checkinDetails : the checkin details
     */
    public function getCheckinDetails ($serial, $type) {

        $checkinDetails = array();
        if ($type != 'Appointment') {
            return $checkinDetails;
        }
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    ac.CheckinPossible,
                    ac.CheckinInstruction_EN,
                    ac.CheckinInstruction_FR
                FROM
                    AppointmentCheckin ac
                WHERE
                    ac.AliasSerNum = $serial
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $checkinPossible    = $data[0];
            $instruction_EN     = $data[1];
            $instruction_FR     = $data[2];

            $checkinDetails = array(
                'checkin_possible'  => $checkinPossible,
                'instruction_EN'    => $instruction_EN,
                'instruction_FR'    => $instruction_FR
            );

            return $checkinDetails;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for aliases. " . $e->getMessage());
        }
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