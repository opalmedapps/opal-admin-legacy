<?php

/**
 *   Alias class
 *
 */
class Alias {

    /**
     *
     * Gets a list of expressions from a source database
     *
     * @param int $sourceDBSer : the serial number of the source database
     * @param string $expressionType : the type of expressions to look out for
     * @return array $expressionList : the list of existing expressions
     */
	public function getExpressions ($sourceDBSer, $expressionType) {
        $expressionList = array();
        $databaseObj = new Database();
        
        try {

            // get already assigned expressions from our database
            $assignedExpressions = $this->getAssignedExpressions($sourceDBSer, $expressionType);

            // ***********************************
            // ARIA 
            // ***********************************
            if ($sourceDBSer == 1) {

                $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);

                if ($source_db_link) {

                    if ($expressionType != "Document") {
                        $sql = "
                            SELECT DISTINCT
    			    	        vv_ActivityLng.Expression1
        			        FROM  
    	    			        variansystem.dbo.vv_ActivityLng vv_ActivityLng 
                            ORDER BY 
                                vv_ActivityLng.Expression1
                        ";
						
                        $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                        $query->execute();

                        while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                            $termName = $data[0];
                            $termArray = array(
    				           	'name'      => $termName,
                                'id'        => $termName, 
                                'description' => $termName,
    			        	    'added'     => 0,
                                'assigned'  => null
    		    	        );

                            $assignedExpression = $this->assignedSearch($termName, $termName, $assignedExpressions);
                            if ($assignedExpression) {
                                $termArray['added'] = 0;
                                $termArray['assigned'] = $assignedExpression;
                            }

                            array_push($expressionList, $termArray);

                        }

                    } else {

                        $sql = "
                            SELECT DISTINCT
                                RTRIM(note_typ.note_typ_desc)
                            FROM 
                                varianenm.dbo.note_typ note_typ
                            ORDER BY
                                RTRIM(note_typ.note_typ_desc)
                        ";
        
                        $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                        $query->execute();

                        while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                            $termName = $data[0];
                                
                            $termArray = array(
    				           	'name'      => $termName,
                                'id'        => $termName,
                                'description'   => $termName,
    			            	'added'     => 0,
                                'assigned'  => null
        			        );

                            $assignedExpression = $this->assignedSearch($termName, $termName, $assignedExpressions);
                            if ($assignedExpression) {
                                $termArray['added'] = 0;
                                $termArray['assigned'] = $assignedExpression;
                            }
        
                            array_push($expressionList, $termArray);
                        }
                    }
                }

            }

            // ***********************************
            // WaitRoomManagement 
            // ***********************************
            if ($sourceDBSer == 2) {

                $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);

                if ($source_db_link) {
            
                    $sql = "
                        SELECT DISTINCT 
                            mval.AppointmentCode,
                            mval.ResourceDescription
                        FROM
                            MediVisitAppointmentList mval
                        ORDER BY
                            mval.AppointmentCode
                    ";

                    $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                    $query->execute();

                    while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    
                        $termName   = $data[0];
                        $termDesc   = $data[1];

                        $termArray = array(
    				       	'name'          => "$termName ($termDesc)",
                            'id'            => $termName,
                            'description'   => $termDesc,
    			        	'added'         => 0,
                            'assigned'      => null
    			        );

                        $assignedExpression = $this->assignedSearch($termName, $termDesc, $assignedExpressions);
                        if ($assignedExpression) {
                            $termArray['added'] = 0;
                            $termArray['assigned'] = $assignedExpression;
                        }

                        array_push($expressionList, $termArray);
                    }
                }
            }

            // ***********************************
            // Mosaiq 
            // ***********************************   
            if ($sourceDBSer == 3) {

                $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);

                if ($source_db_link) {

                    $sql = "SELECT 'QUERY_HERE'";

                    $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                    $query->execute();

                    while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                        // Set data from query here

                        //array_push($expressionList, $termArray); // Uncomment for use
                    }
                }

            }        

			return $expressionList;

		} catch (PDOException $e) {
			echo $e->getMessage();
			return $expressionList;
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
            echo $e->getMessage();
            return $expressions;
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
            }

            $this->sanitizeEmptyAliases($user);

            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
			$response['message'] = $e->getMessage();
			return $response; // Fail
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
            }
            return;
        } catch( PDOException $e) {
            return $e->getMessage(); // Fail
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
			echo $e->getMessage();
			return $colorTags;
		}
    }

    /**
     *
     * Gets a list of existing aliases
     *
     * @return array $aliasList : the list of existing aliases
     */
	public function getAliases() {
		$aliasList = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

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
                    $eduMatObj = new EduMaterial();
                    $aliasEduMat = $eduMatObj->getEducationalMaterialDetails($aliasEduMatSer);
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
			echo $e->getMessage();
			return $aliasList;
		}
	}

    /**
     *
     * Gets details for one particular alias
     *
     * @param integer $aliasSer : the alias serial number
     * @return array $aliasDetails : the alias details
     */			
    public function getAliasDetails ($aliasSer) { 

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
                $eduMatObj = new EduMaterial();
                $aliasEduMat = $eduMatObj->getEducationalMaterialDetails($aliasEduMatSer);
            }

            if ($hospitalMapSer) {
                $hospitalMapObj = new HospitalMap();
                $hospitalMap = $hospitalMapObj->getHospitalMapDetails($hospitalMapSer);
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
			echo $e->getMessage();
			return $aliasDetails;
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

        $lastTransferred = 'NOW()';
        if ($aliasType == 'Appointment') {
            $lastTransferred = "'2000-01-01 00:00:00'";
        }

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
			return $e->getMessage();
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
            $response['message'] = $e->getMessage();
			return $response; // Fail
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
                    UPDATE
                        AppointmentCheckin
                    SET
                        AppointmentCheckin.CheckinPossible          = '$checkinPossible',
                        AppointmentCheckin.CheckinInstruction_EN    = \"$instruction_EN\",
                        AppointmentCheckin.CheckinInstruction_FR    = \"$instruction_FR\",
                        AppointmentCheckin.LastUpdatedBy            = '$userSer',
                        AppointmentCheckin.SessionId                = '$sessionId'
                    WHERE 
                        AppointmentCheckin.AliasSerNum = $aliasSer
                ";

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
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

    /**
     *
     * Gets a list of source databases 
     *
     * @return array $sourceDBList : the list of source databases
     */
	public function getSourceDatabases () {
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
			echo $e->getMessage();
			return $sourceDBList;
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
            echo $e->getMessage();
            return $checkinDetails;
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
            echo $e->getMessage();
            return $aliasLogs;
        }
    }

    /**
     *
     * Gets list logs of appointments/documents/tasks during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @param string $type : the alias type
     * @return array $aliasLogs : the alias logs for table view
     */
    public function getAliasListLogs ($serials, $type) {
        $aliasLogs = array();
        $serials = implode(',', $serials);
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            if (!$type) {
                $sql = "
                    SELECT DISTINCT 
                        al.AliasType,
                        ae.ExpressionName,
                        ae.Description,
                        apmh.AppointmentRevSerNum,
                        apmh.CronLogSerNum,
                        apmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        apmh.AppointmentAriaSer,
                        apmh.DateAdded,
                        apmh.ReadStatus,
                        apmh.ModificationAction
                    FROM
                        AppointmentMH apmh,
                        AliasExpression ae,
                        SourceDatabase sd,
                        Alias al
                    WHERE
                        apmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum              = al.AliasSerNum
                    AND apmh.SourceDatabaseSerNum   = sd.SourceDatabaseSerNum
                    AND apmh.CronLogSerNum          IN ($serials)
                ";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                        'type'                      => $data[0],
                        'expression_name'           => $data[1],
                        'expression_description'    => $data[2],
                        'revision'                  => $data[3],
                        'cron_serial'               => $data[4],
                        'patient_serial'            => $data[5],
                        'source_db'                 => $data[6],
                        'source_uid'                => $data[7],
                        'date_added'                => $data[8],
                        'read_status'               => $data[9],
                        'mod_action'                => $data[10]
                    );
                    array_push($aliasLogs, $logDetails);
                }

                $sql = "
                    SELECT DISTINCT 
                        al.AliasType,
                        ae.ExpressionName,
                        ae.Description,
                        docmh.DocumentRevSerNum,
                        docmh.CronLogSerNum,
                        docmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        docmh.DocumentId,
                        docmh.DateAdded,
                        docmh.ReadStatus,
                        docmh.ModificationAction
                    FROM
                        DocumentMH docmh,
                        AliasExpression ae,
                        SourceDatabase sd,
                        Alias al
                    WHERE
                        docmh.AliasExpressionSerNum     = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum                  = al.AliasSerNum
                    AND docmh.SourceDatabaseSerNum      = sd.SourceDatabaseSerNum
                    AND docmh.CronLogSerNum             IN ($serials)
                ";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                        'type'                      => $data[0],
                        'expression_name'           => $data[1],
                        'expression_description'    => $data[2],
                        'revision'                  => $data[3],
                        'cron_serial'               => $data[4],
                        'patient_serial'            => $data[5],
                        'source_db'                 => $data[6],
                        'source_uid'                => $data[7],
                        'date_added'                => $data[8],
                        'read_status'               => $data[9],
                        'mod_action'                => $data[10]
                    );
                    array_push($aliasLogs, $logDetails);
                }

                $sql = "
                    SELECT DISTINCT 
                        al.AliasType,
                        ae.ExpressionName,
                        ae.Description,
                        tmh.TaskRevSerNum,
                        tmh.CronLogSerNum,
                        tmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        tmh.TaskAriaSer,
                        tmh.DateAdded,
                        tmh.ModificationAction
                    FROM
                        TaskMH tmh,
                        AliasExpression ae,
                        SourceDatabase sd,
                        Alias al
                    WHERE
                        tmh.AliasExpressionSerNum   = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum              = al.AliasSerNum
                    AND tmh.SourceDatabaseSerNum    = sd.SourceDatabaseSerNum
                    AND tmh.CronLogSerNum           IN ($serials)
                ";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                        'type'                      => $data[0],
                        'expression_name'           => $data[1],
                        'expression_description'    => $data[2],
                        'revision'                  => $data[3],
                        'cron_serial'               => $data[4],
                        'patient_serial'            => $data[5],
                        'source_db'                 => $data[6],
                        'source_uid'                => $data[7],
                        'date_added'                => $data[8],
                        'read_status'               => 'N/A',
                        'mod_action'                => $data[9]
                    );
                    array_push($aliasLogs, $logDetails);
                }
            }
            else if ($type == 'Appointment') {
                $sql = "
                    SELECT DISTINCT
                        ae.ExpressionName,
                        ae.Description,
                        apmh.AppointmentRevSerNum,
                        apmh.CronLogSerNum,
                        apmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        apmh.AppointmentAriaSer,
                        apmh.Status,
                        apmh.State,
                        apmh.ScheduledStartTime,
                        apmh.ScheduledEndTime,
                        apmh.ActualStartDate,
                        apmh.ActualEndDate,
                        apmh.RoomLocation_EN,
                        apmh.RoomLocation_FR,
                        apmh.Checkin,
                        apmh.DateAdded,
                        apmh.ReadStatus,
                        apmh.ModificationAction
                    FROM
                        AppointmentMH apmh,
                        AliasExpression ae,
                        SourceDatabase sd
                    WHERE
                        apmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum
                    AND apmh.SourceDatabaseSerNum   = sd.SourceDatabaseSerNum
                    AND apmh.CronLogSerNum          IN ($serials)
                "; 
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                       'expression_name'        => $data[0],
                       'expression_description' => $data[1],
                       'revision'               => $data[2],
                       'cron_serial'            => $data[3],
                       'patient_serial'         => $data[4],
                       'source_db'              => $data[5],
                       'source_uid'             => $data[6],
                       'status'                 => $data[7],
                       'state'                  => $data[8],
                       'scheduled_start'        => $data[9],
                       'scheduled_end'          => $data[10],
                       'actual_start'           => $data[11],
                       'actual_end'             => $data[12],
                       'room_EN'                => $data[13],
                       'room_FR'                => $data[14],
                       'checkin'                => $data[15],
                       'date_added'             => $data[16],
                       'read_status'            => $data[17],
                       'mod_action'             => $data[18]
                    );
                    array_push($aliasLogs, $logDetails);
                }
            }
            else if ($type == 'Document') {
                $sql = "
                    SELECT DISTINCT
                        ae.ExpressionName,
                        ae.Description,
                        docmh.DocumentRevSerNum,
                        docmh.CronLogSerNum,
                        docmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        docmh.DocumentId,
                        Staff1.LastName,
                        docmh.CreatedTimeStamp,
                        Staff2.LastName,
                        docmh.ApprovedTimeStamp,
                        Staff3.LastName,
                        docmh.DateOfService,
                        docmh.Revised,
                        docmh.ValidEntry,
                        docmh.OriginalFileName,
                        docmh.FinalFileName,
                        docmh.TransferStatus,
                        docmh.TransferLog,
                        docmh.DateAdded,
                        docmh.ReadStatus,
                        docmh.ModificationAction
                    FROM
                        DocumentMH docmh,
                        AliasExpression ae,
                        SourceDatabase sd
                    LEFT JOIN
                        Staff Staff1
                    ON Staff1.StaffSerNum = docmh.CreatedBySerNum
                    LEFT JOIN 
                        Staff Staff2
                    ON Staff2.StaffSerNum = docmh.ApprovedBySerNum
                    LEFT JOIN 
                        Staff Staff3
                    ON Staff3.StaffSerNum = docmh.AuthoredBySerNum
                    WHERE 
                        docmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum
                    AND docmh.SourceDatabaseSerNum   = sd.SourceDatabaseSerNum
                    AND docmh.CronLogSerNum          IN ($serials)
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                       'expression_name'        => $data[0],
                       'expression_description' => $data[1],
                       'revision'               => $data[2],
                       'cron_serial'            => $data[3],
                       'patient_serial'         => $data[4],
                       'source_db'              => $data[5],
                       'source_uid'             => $data[6],
                       'created_by'             => $data[7],
                       'created_time'           => $data[8],
                       'approved_by'            => $data[9],
                       'approved_time'          => $data[10],
                       'authored_by'            => $data[11],
                       'dateofservice'          => $data[12],
                       'revised'                => $data[13],
                       'valid'                  => $data[14],
                       'original_file'          => $data[15],
                       'final_file'             => $data[16],
                       'transfer'               => $data[17],
                       'transfer_log'           => $data[18],
                       'date_added'             => $data[19],
                       'read_status'            => $data[20],
                       'mod_action'             => $data[21]
                    );
                    array_push($aliasLogs, $logDetails);
                }
            }
            else if ($type == 'Task') {
                $sql = "
                    SELECT DISTINCT
                        ae.ExpressionName,
                        ae.Description,
                        tmh.TaskRevSerNum,
                        tmh.CronLogSerNum,
                        tmh.PatientSerNum,
                        sd.SourceDatabaseName,
                        tmh.TaskAriaSer,
                        tmh.Status,
                        tmh.State,
                        tmh.DueDateTime,
                        tmh.CreationDate,
                        tmh.CompletionDate,
                        tmh.DateAdded,
                        tmh.ModificationAction
                    FROM
                        TaskMH tmh,
                        AliasExpression ae,
                        SourceDatabase sd
                    WHERE
                        tmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum
                    AND tmh.SourceDatabaseSerNum   = sd.SourceDatabaseSerNum
                    AND tmh.CronLogSerNum          IN ($serials)
                ";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array (
                       'expression_name'        => $data[0],
                       'expression_description' => $data[1],
                       'revision'               => $data[2],
                       'cron_serial'            => $data[3],
                       'patient_serial'         => $data[4],
                       'source_db'              => $data[5],
                       'source_uid'             => $data[6],
                       'status'                 => $data[7],
                       'state'                  => $data[8],
                       'due_date'               => $data[9],
                       'creation'               => $data[10],
                       'completed'              => $data[11],
                       'date_added'             => $data[12],
                       'read_status'            => 'N/A',
                       'mod_action'             => $data[13]
                    );

                    array_push($aliasLogs, $logDetails);
                }
            }

            return $aliasLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $aliasLogs;
        }
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

}
?>
