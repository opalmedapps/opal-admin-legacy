<?php

/**
 * Legacy Questionnaire class
 *
 */
class LegacyQuestionnaire {

    /**
     *
     * Updates the legacy questionnaire publish flags in the database
     *
     * @param array $legacyQuestionnaireList : the list of legacy questionnaires
     * @return array $response : response
     */    
    public function updateLegacyQuestionnairePublishFlags( $legacyQuestionnaireList ) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			foreach ($legacyQuestionnaireList as $legacyQuestionnaire) {
				$legacyQuestionnairePublish = $legacyQuestionnaire['publish'];
				$legacyQuestionnaireSer = $legacyQuestionnaire['serial'];
				$sql = "
					UPDATE 
						QuestionnaireControl 	
					SET 
						QuestionnaireControl.PublishFlag = $legacyQuestionnairePublish
					WHERE 
						QuestionnaireControl.QuestionnaireControlSerNum = $legacyQuestionnaireSer
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
     * Gets a list of "expressions" (questionnaires) from the legacy questionnaire database
     *
     * @return array $expressionList : the list of existing expressions
     */
    public function getLegacyQuestionnaireExpressions () {
        $expressionList = array();
        
        try {

            $questionnaires_db_link = new PDO( QUESTIONNAIRE_DB_DSN, QUESTIONNAIRE_DB_USERNAME, QUESTIONNAIRE_DB_PASSWORD );
            $questionnaires_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT 
                    Questionnaire.QuestionnaireSerNum,
                    Questionnaire.QuestionnaireName 
                FROM 
                    Questionnaire 
            ";
            
            $query = $questionnaires_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $expressionSer  = $data[0];
                $expressionName = $data[1];

                $expressionDetails = array(
                    'serial'    => $expressionSer,
                    'name'      => $expressionName
                );

                array_push($expressionList, $expressionDetails);
            } 
            return $expressionList;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $expressionList;
        }
    }

    /**
     *
     * Gets a list of existing legacy questionnaires
     *
     * @return array $legacyQuestionnaireList : the list of existing legacy questionnaires
     */        
	public function getLegacyQuestionnaires() {
		$legacyQuestionnaireList = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $questionnaires_db_link = new PDO( QUESTIONNAIRE_DB_DSN, QUESTIONNAIRE_DB_USERNAME, QUESTIONNAIRE_DB_PASSWORD );
            $questionnaires_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
                SELECT DISTINCT
                    qc.QuestionnaireControlSerNum,
                    qc.QuestionnaireDBSerNum,
                    qc.QuestionnaireName_EN,
                    qc.QuestionnaireName_FR,
                    qc.PublishFlag
                FROM
                    QuestionnaireControl qc
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$questionnaireControlSer    = $data[0];
				$questionnaireDBSer 	    = $data[1];
                $questionnaireName_EN       = $data[2];
                $questionnaireName_FR       = $data[3];
                $questionnairePublish       = $data[4];
                $questionnaireFilters       = array();

                $sql = "
                    SELECT DISTINCT
                        Questionnaire.QuestionnaireName 
                    FROM
                        Questionnaire 
                    WHERE
                        QuestionnaireSerNum = $questionnaireDBSer
                ";

                $secondQuery = $questionnaires_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $secondQuery->execute();

                $secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

                $questionnaireExpression = $secondData[0];

				$sql = "
					SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
					FROM 
						QuestionnaireControl, 
						Filters 
					WHERE 
                            QuestionnaireControl.QuestionnaireControlSerNum     = $questionnaireControlSer
                    AND     Filters.ControlTable                                = 'LegacyQuestionnaireControl'
                    AND     Filters.ControlTableSerNum                          = QuestionnaireControl.QuestionnaireControlSerNum
                    AND     Filters.FilterType                                  != ''
                    AND     Filters.FilterId                                    != ''
				";

				$secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$secondQuery->execute();

				while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

					$filterType = $secondData[0];
					$filterId   = $secondData[1];
					$filterArray = array (
						'type'  => $filterType,
						'id'    => $filterId,
						'added' => 1
					);

					array_push($questionnaireFilters, $filterArray);
				}

				$questionnaireArray = array(
					'name_FR' 		    => $questionnaireName_FR, 
					'name_EN' 		    => $questionnaireName_EN, 
					'serial' 		    => $questionnaireControlSer, 
                    'db_serial'			=> $questionnaireDBSer, 
                    'publish'          	=> $questionnairePublish,
                    'expression'        => $questionnaireExpression,
					'filters' 		    => $questionnaireFilters
				);

				array_push($legacyQuestionnaireList, $questionnaireArray);
			}
			return $legacyQuestionnaireList;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $legacyQuestionnaireList;
		}
	}

    /**
     *
     * Gets details on a particular legacy questionnaire
     *
     * @param integer $legacyQuestionnaireSer : the questionnaire control serial number
     * @return array $legacyQuestionnaireDetails : the legacy questionnaire details
     */    			
    public function getLegacyQuestionnaireDetails ($legacyQuestionnaireSer) {

		$legacyQuestionnaireDetails = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT
                    qc.QuestionnaireDBSerNum,
                    qc.QuestionnaireName_EN,
                    qc.QuestionnaireName_FR,
                    qc.PublishFlag
                FROM
                    QuestionnaireControl qc
                WHERE
                    qc.QuestionnaireControlSerNum = $legacyQuestionnaireSer
            ";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$questionnaireDBSer	        = $data[0];
			$questionnaireName_EN	    = $data[1];
			$questionnaireName_FR	    = $data[2];
            $questionnairePublish       = $data[3];
			$questionnaireFilters	    = array();

			$sql = "
				SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
					FROM 
						QuestionnaireControl, 
						Filters 
					WHERE 
                            QuestionnaireControl.QuestionnaireControlSerNum     = $legacyQuestionnaireSer 
                    AND     Filters.ControlTable                                = 'LegacyQuestionnaireControl'
                    AND     Filters.ControlTableSerNum                          = QuestionnaireControl.QuestionnaireControlSerNum
                    AND     Filters.FilterType                                  != ''
                    AND     Filters.FilterId                                    != ''

			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

					$filterType = $data[0];
					$filterId   = $data[1];
					$filterArray = array (
						'type'  => $filterType,
						'id'    => $filterId,
						'added' => 1
					);

					array_push($questionnaireFilters, $filterArray);


            }

			$legacyQuestionnaireDetails = array(
	            'name_FR' 		    => $questionnaireName_FR, 
				'name_EN' 		    => $questionnaireName_EN, 
				'serial' 		    => $legacyQuestionnaireSer, 
                'publish'           => $questionnairePublish,
                'db_serial'         => $questionnaireDBSer,
				'filters' 		    => $questionnaireFilters
            );
		
			return $legacyQuestionnaireDetails;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $legacyQuestionnaireDetails;
		}
	}

    /**
     *
     * Inserts a legacy questionnaire into the database
     *
     * @param array $legacyQuestionnaireDetails : the legacy questionnaire details
	 * @return void
     */    
	public function insertLegacyQuestionnaire( $legacyQuestionnaireDetails ) {

		$questionnaireName_EN 	= $legacyQuestionnaireDetails['name_EN'];
		$questionnaireName_FR 	= $legacyQuestionnaireDetails['name_FR'];
        $questionnaireDBSer     = $legacyQuestionnaireDetails['expression']['serial'];

		$questionnaireFilters	= $legacyQuestionnaireDetails['filters'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
                    QuestionnaireControl (
                        QuestionnaireDBSerNum,
                        QuestionnaireName_EN,
                        QuestionnaireName_FR,
                        DateAdded
					) 
				VALUES (
                    '$questionnaireDBSer',
					\"$questionnaireName_EN\", 
					\"$questionnaireName_FR\",
                    NOW()
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$questionnaireSer = $host_db_link->lastInsertId();

			foreach ($questionnaireFilters as $filter) {

                $filterType = $filter['type'];
                $filterId   = $filter['id'];

				$sql = "
                    INSERT INTO 
                        Filters (
                            ControlTable,
                            ControlTableSerNum,
                            FilterType,
                            FilterId,
                            DateAdded
                        )
                    VALUE (
                        'LegacyQuestionnaireControl',
                        '$questionnaireSer',
                        '$filterType',
                        \"$filterId\",
                        NOW()
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
     * Deletes a legacy questionnaire from the database
     *
     * @param integer $questionnaireSer : the questionnaire control serial number
     * @return array : response
     */        
    public function deleteLegacyQuestionnaire( $questionnaireSer ) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				DELETE FROM 
					QuestionnaireControl 
				WHERE 
					QuestionnaireControl.QuestionnaireControlSerNum = $questionnaireSer
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$sql = "
                DELETE FROM
                    Filters
                WHERE
                    Filters.ControlTableSerNum   = $questionnaireSer
                AND Filters.ControlTable         = 'LegacyQuestionnaireControl'
			";
			
			$query = $host_db_link->prepare( $sql );
			$query->execute();
		
            $response['value'] = 1;
            return $response;
		} catch( PDOException $e) {
            $response['message'] = $e->getMessage();
			return $response;
		}
	}

    /**
     *
     * Updates a legacy questionnaire details in the database
     *
     * @param array $legacyQuestionnaireDetails : the legacy questionnaire details
     * @return array : response
     */        
    public function updateLegacyQuestionnaire( $legacyQuestionnaireDetails ) {

		$questionnaireName_EN 	    = $legacyQuestionnaireDetails['name_EN'];
		$questionnaireName_FR 	    = $legacyQuestionnaireDetails['name_FR'];
        $questionnaireSer	        = $legacyQuestionnaireDetails['serial'];
		$questionnaireFilters	    = $legacyQuestionnaireDetails['filters'];

        $existingFilters	= array();

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				UPDATE 
					QuestionnaireControl 
				SET 
					QuestionnaireControl.QuestionnaireName_EN 		= \"$questionnaireName_EN\", 
					QuestionnaireControl.QuestionnaireName_FR 		= \"$questionnaireName_FR\"
				WHERE 
					QuestionnaireControl.QuestionnaireControlSerNum = $questionnaireSer
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$sql = "
				SELECT DISTINCT 
                    Filters.FilterType,
                    Filters.FilterId
				FROM 
					Filters
				WHERE 
                    Filters.ControlTableSerNum       = $questionnaireSer
                AND Filters.ControlTable             = 'LegacyQuestionnaireControl'
                AND Filters.FilterType              != ''
                AND Filters.FilterId                != ''
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $filterArray = array(
                    'type'  => $data[0],
                    'id'    => $data[1]
                );
				array_push($existingFilters, $filterArray);
			}

            if (!empty($existingFilters)) {
                // If old filters not in new, remove from DB
	    		foreach ($existingFilters as $existingFilter) {
                    $id     = $existingFilter['id'];
                    $type   = $existingFilter['type'];
                    if (!$this->nestedSearch($id, $type, $questionnaireFilters)) {
					    $sql = "
                            DELETE FROM 
	    						Filters
		    				WHERE
                                Filters.FilterId            = \"$id\"
                            AND Filters.FilterType          = '$type'
                            AND Filters.ControlTableSerNum   = $questionnaireSer
                            AND Filters.ControlTable         = 'LegacyQuestionnaireControl'
    					";
    
	    				$query = $host_db_link->prepare( $sql );
		    			$query->execute();
			    	}
    			}   
            }
            if (!empty($questionnaireFilters)) {
                // If new filters, insert into DB
    			foreach ($questionnaireFilters as $filter) {
                    $id     = $filter['id'];
                    $type   = $filter['type'];
                    if (!$this->nestedSearch($id, $type, $existingFilters)) {
                        $sql = "
                            INSERT INTO 
                                Filters (
                                    ControlTable,
                                    ControlTableSerNum,
                                    FilterId,
                                    FilterType,
                                    DateAdded
                                )
                            VALUES (
                                'LegacyQuestionnaireControl',
                                '$questionnaireSer',
                                \"$id\",
                                '$type',
                                NOW()
                            )
			    		";
				    	$query = $host_db_link->prepare( $sql );
					    $query->execute();
    				}
	    		}
            }

            $response['value'] = 1;
            return $response;
        } catch( PDOException $e) {
            $response['message'] = $e->getMessage();
			return $response;
		}
    }

	/**
     *
     * Does a nested search for match
     *
     * @param string $id    : the needle id
     * @param string $type  : the needle type
     * @param array $array  : the key-value haystack
     * @return boolean
     */
    public function nestedSearch($id, $type, $array) {
        if(empty($array) || !$id || !$type){
            return 0;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id and $val['type'] === $type) {
                return 1;
            }
        }
        return 0;
    }

}
?>
