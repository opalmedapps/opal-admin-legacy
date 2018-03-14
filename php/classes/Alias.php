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
    			        	    'added'     => 0,
                                'assigned'  => null
    		    	        );

                            $assignedExpression = $this->assignedSearch($termName, $assignedExpressions);
                            if ($assignedExpression) {
                                $termArray['added'] = 1;
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
    			            	'added'     => 0,
                                'assigned'  => null
        			        );

                            $assignedExpression = $this->assignedSearch($termName, $assignedExpressions);
                            if ($assignedExpression) {
                                $termArray['added'] = 1;
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
    			        	'added'         => 0,
                            'assigned'      => null
    			        );

                        $assignedExpression = $this->assignedSearch($termName, $assignedExpressions);
                        if ($assignedExpression) {
                            $termArray['added'] = 1;
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
                    Alias.AliasName_EN
                FROM
                    AliasExpression ae,
                    Alias
                WHERE
                    ae.AliasSerNum = Alias.AliasSerNum
                AND Alias.AliasType = '$expressionType'
                AND Alias.SourceDatabaseSerNum = '$sourceDBSer'
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $expressionDetails = array (
                    'id'        => $data[0],
                    'name_EN'   => "$data[1]"   
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

            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
			$response['message'] = $e->getMessage();
			return $response; // Fail
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
						AliasExpression.ExpressionName 
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
					$termArray = array(
						'name' => $termName,
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
                    Alias.ColorTag
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

            $aliasEduMat    = "";
			$aliasTerms	    = array();

			$sql = "
				SELECT DISTINCT 
					AliasExpression.ExpressionName 
				FROM 	
					AliasExpression 
				WHERE 
					AliasExpression.AliasSerNum = $aliasSer
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

					$termName = $data[0];
					$termArray = array(
						'name' => $termName,
						'added'=> 1
					);

					array_push($aliasTerms, $termArray);
			}

            if ($aliasEduMatSer != 0) {
                $eduMatObj = new EduMaterial();
                $aliasEduMat = $eduMatObj->getEducationalMaterialDetails($aliasEduMatSer);
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
				'terms' 		    => $aliasTerms
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
        $aliasEduMatSer = 0;
        if ( is_array($aliasDetails['edumat']) && isset($aliasDetails['edumat']['serial']) ) {
            $aliasEduMatSer = $aliasDetails['edumat']['serial'];
        }
        $sourceDBSer    = $aliasDetails['source_db']['serial'];

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
                        SourceDatabaseSerNum,
                        AliasType, 
                        ColorTag,
                        AliasUpdate,
                        LastUpdatedBy,
                        SessionId
					) 
				VALUES (
					NULL, 
					\"$aliasName_FR\", 
					\"$aliasName_EN\",
					\"$aliasDesc_FR\", 
                    \"$aliasDesc_EN\", 
                    '$aliasEduMatSer',
                    '$sourceDBSer',
                    '$aliasType', 
                    '$aliasColorTag',
                    '0',
                    '$userSer',
                    '$sessionId'
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$aliasSer = $host_db_link->lastInsertId();

			foreach ($aliasTerms as $aliasTerm) {

				$sql = "
                    INSERT INTO 
                        AliasExpression (
                            AliasSerNum,
                            ExpressionName
                        )
                    VALUE (
                        '$aliasSer',
                        \"$aliasTerm\"
                    )
                    ON DUPLICATE KEY UPDATE
                        AliasSerNum = '$aliasSer'
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
        $aliasEduMatSer = $aliasDetails['edumatser'];
      
        $aliasColorTag  = $aliasDetails['color'];

        $userSer        = $aliasDetails['user']['id'];
        $sessionId      = $aliasDetails['user']['sessionid'];

        $existingTerms	= array();

        // Initialize a response array
        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				UPDATE 
					Alias 
				SET 
					Alias.AliasName_EN 		                = \"$aliasName_EN\", 
					Alias.AliasName_FR 		                = \"$aliasName_FR\", 
					Alias.AliasDescription_EN	            = \"$aliasDesc_EN\",
                    Alias.AliasDescription_FR	            = \"$aliasDesc_FR\",
                    Alias.EducationalMaterialControlSerNum  = '$aliasEduMatSer',
                    Alias.ColorTag                          = '$aliasColorTag',
                    Alias.LastUpdatedBy                     = '$userSer',
                    Alias.SessionId                         = '$sessionId'
				WHERE 
					Alias.AliasSerNum = $aliasSer
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$sql = "
				SELECT DISTINCT 
					AliasExpression.ExpressionName 
				FROM 
					AliasExpression 
				WHERE 
					AliasExpression.AliasSerNum = $aliasSer
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				array_push($existingTerms, $data[0]);
			}

            // This loop compares the old terms with the new
            // If old terms not in new, then remove old
			foreach ($existingTerms as $existingTermName) {
				if (!in_array($existingTermName, $aliasTerms)) {
					$sql = "
                        DELETE FROM 
							AliasExpression
						WHERE
                            AliasExpression.ExpressionName = \"$existingTermName\"
                        AND AliasExpression.AliasSerNum = $aliasSer
					";

					$query = $host_db_link->prepare( $sql );
					$query->execute();
				}
			}

            // If new terms, then insert
			foreach ($aliasTerms as $term) {
				if (!in_array($term, $existingTerms)) {
                    $sql = "
                        INSERT INTO 
                            AliasExpression (
                                AliasExpressionSerNum,
                                AliasSerNum,
                                ExpressionName
                            )
                        VALUES (
                            NULL,
                            '$aliasSer',
                            \"$term\"
                        )
                        ON DUPLICATE KEY UPDATE
                            AliasSerNum = '$aliasSer'
					";
					$query = $host_db_link->prepare( $sql );
					$query->execute();
				}
            }

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
     * Checks if an expression has been assigned to an alias
     *
     * @param string $id    : the needle id
     * @param array $array  : the key-value haystack
     * @return $assignedAlias
     */
    public function assignedSearch($id, $array) {
        $assignedAlias = null;
        if(empty($array) || !$id){
            return $assignedAlias;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id) {
                $assignedAlias = $val;
                return $assignedAlias;
            }
        }
        return $assignedAlias;
    }

}
?>
