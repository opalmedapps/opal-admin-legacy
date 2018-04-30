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
     * @param object $user : the current user in session
     * @return array $response : response
     */    
    public function updateLegacyQuestionnairePublishFlags( $legacyQuestionnaireList, $user ) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
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
						QuestionnaireControl.PublishFlag = $legacyQuestionnairePublish,
                        QuestionnaireControl.LastUpdatedBy = $userSer,
                        QuestionnaireControl.SessionId = '$sessionId'
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

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

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

                $sql = "
                    SELECT DISTINCT
                        qc.QuestionnaireControlSerNum
                    FROM
                        QuestionnaireControl qc 
                    WHERE 
                        qc.QuestionnaireDBSerNum = $expressionSer
                ";

                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $secondQuery->execute();

                $secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

                $exists = $secondData[0];

                if (!$exists) {
                    $expressionDetails = array(
                        'serial'    => $expressionSer,
                        'name'      => $expressionName
                    );

                    array_push($expressionList, $expressionDetails);
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
                $questionnaireTriggers      = array();

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

					$triggerType = $secondData[0];
					$triggerId   = $secondData[1];
					$triggerArray = array (
						'type'  => $triggerType,
						'id'    => $triggerId,
						'added' => 1
					);

					array_push($questionnaireTriggers, $triggerArray);
				}

                $occurrenceArray = array(
                    'start_date' => null,
                    'end_date'  => null,
                    'set'       => 0,
                    'frequency' => array (
                        'meta_key'  => null,
                        'meta_value'    => null,
                        'additionalMeta'    => array()
                    )
                );

				$questionnaireArray = array(
					'name_FR' 		    => $questionnaireName_FR, 
					'name_EN' 		    => $questionnaireName_EN, 
					'serial' 		    => $questionnaireControlSer, 
                    'db_serial'			=> $questionnaireDBSer, 
                    'publish'          	=> $questionnairePublish,
                    'changed'           => 0,
                    'expression'        => $questionnaireExpression,
					'triggers' 		    => $questionnaireTriggers,
                    'occurrence'        => $occurrenceArray
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
                    qc.Intro_EN,
                    qc.Intro_FR,
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
            $questionnaireIntro_EN      = $data[3];
            $questionnaireIntro_FR      = $data[4];
            $questionnairePublish       = $data[5];
			$questionnaireTriggers	    = array();

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

					$triggerType = $data[0];
					$triggerId   = $data[1];
					$triggerArray = array (
						'type'  => $triggerType,
						'id'    => $triggerId,
						'added' => 1
					);

					array_push($questionnaireTriggers, $triggerArray);


            }

            $occurrenceArray = array(
                'start_date' => null,
                'end_date'  => null,
                'set'          => 0,
                'frequency' => array (
                    'custom' => 0,
                    'meta_key'  => null,
                    'meta_value'    => null,
                    'additionalMeta'    => array()
                )
            );

            $sql = "
                SELECT DISTINCT
                    fe.CustomFlag,
                    fe.MetaKey,
                    fe.MetaValue 
                FROM 
                    FrequencyEvents fe
                WHERE
                    fe.ControlTable             = 'LegacyQuestionnaireControl'
                AND fe.ControlTableSerNum       = $legacyQuestionnaireSer
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                // if we've entered, then a frequency has been set
                $occurrenceArray['set'] = 1;

                $customFlag     = $data[0];
                // the type of meta key and which content it belongs to is separated by the | delimeter
                list($metaKey, $dontNeed) = explode('|', $data[1]);
                $metaValue      = $data[2];

                if ($metaKey == 'repeat_start') {
                    $occurrenceArray['start_date'] = $metaValue;
                }
                else if ($metaKey == 'repeat_end') {
                    $occurrenceArray['end_date'] = $metaValue;
                }
                // custom non-additional meta (eg. repeat_day, repeat_week ... any meta with one underscore that was custom made)
                else if ($customFlag == 1 and count(explode('_', $metaKey)) == 2) {
                    $occurrenceArray['frequency']['custom'] = 1;
                    $occurrenceArray['frequency']['meta_key'] = $metaKey;
                    $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
                }
                // additional meta (eg. repeat_day_iw, repeat_week_im ... any meta with two underscores)
                else if ($customFlag == 1 and count(explode('_', $metaKey)) == 3) {
                    $occurrenceArray['frequency']['custom'] = 1;
                    $occurrenceArray['frequency']['additionalMeta'][$metaKey] = array_map('intval', explode(',', $metaValue));
                    sort($occurrenceArray['frequency']['additionalMeta'][$metaKey]);   
                }
                else { // should only be one predefined frequency chosen, if chosen 
                    $occurrenceArray['frequency']['meta_key'] = $metaKey;
                    $occurrenceArray['frequency']['meta_value'] = intval($metaValue);
                }
            }
            
			$legacyQuestionnaireDetails = array(
	            'name_FR' 		    => $questionnaireName_FR, 
				'name_EN' 		    => $questionnaireName_EN, 
                'intro_EN'          => $questionnaireIntro_EN,
                'intro_FR'          => $questionnaireIntro_FR,
				'serial' 		    => $legacyQuestionnaireSer, 
                'publish'           => $questionnairePublish,
                'db_serial'         => $questionnaireDBSer,
				'triggers' 		    => $questionnaireTriggers,
                'occurrence'        => $occurrenceArray
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
        $questionnaireIntro_EN  = $legacyQuestionnaireDetails['intro_EN'];
        $questionnaireIntro_FR  = $legacyQuestionnaireDetails['intro_FR'];
        $questionnaireDBSer     = $legacyQuestionnaireDetails['expression']['serial'];

		$questionnaireTriggers	= $legacyQuestionnaireDetails['triggers'];
        $questionnaireOccurrence    = $legacyQuestionnaireDetails['occurrence'];

        $userSer                = $legacyQuestionnaireDetails['user']['id'];
        $sessionId              = $legacyQuestionnaireDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
                    QuestionnaireControl (
                        QuestionnaireDBSerNum,
                        QuestionnaireName_EN,
                        QuestionnaireName_FR,
                        Intro_EN,
                        Intro_FR,
                        DateAdded,
                        LastUpdatedBy,
                        SessionId
					) 
				VALUES (
                    '$questionnaireDBSer',
					\"$questionnaireName_EN\", 
					\"$questionnaireName_FR\",
                    \"$questionnaireIntro_EN\",
                    \"$questionnaireIntro_FR\",
                    NOW(),
                    '$userSer',
                    '$sessionId'
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$questionnaireSer = $host_db_link->lastInsertId();

            if (!empty($questionnaireTriggers)) {
    			foreach ($questionnaireTriggers as $trigger) {

                    $triggerType = $trigger['type'];
                    $triggerId   = $trigger['id'];

    				$sql = "
                        INSERT INTO 
                            Filters (
                                ControlTable,
                                ControlTableSerNum,
                                FilterType,
                                FilterId,
                                DateAdded,
                                LastUpdatedBy,
                                SessionId
                            )
                        VALUE (
                            'LegacyQuestionnaireControl',
                            '$questionnaireSer',
                            '$triggerType',
                            \"$triggerId\",
                            NOW(),
                            '$userSer',
                            '$sessionId'
                        )
    				";
    				$query = $host_db_link->prepare( $sql );
    				$query->execute();
    			}
            }

            if ($questionnaireOccurrence['set']) {

                $occurrenceStart = $questionnaireOccurrence['start_date'];
                $sql = "
                    INSERT INTO 
                        FrequencyEvents (
                            ControlTable,
                            ControlTableSerNum,
                            MetaKey,
                            MetaValue,
                            CustomFlag,
                            DateAdded
                        )
                    VALUES (
                        'LegacyQuestionnaireControl',
                        '$questionnaireSer',
                        'repeat_start',
                        '$occurrenceStart',
                        '0',
                        NOW()
                    )
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();

                $occurrenceEnd = $questionnaireOccurrence['end_date'];
                if ($occurrenceEnd) {
                    $sql = "
                        INSERT INTO 
                            FrequencyEvents (
                                ControlTable,
                                ControlTableSerNum,
                                MetaKey,
                                MetaValue,
                                CustomFlag,
                                DateAdded
                            )
                        VALUES (
                            'LegacyQuestionnaireControl',
                            '$questionnaireSer',
                            'repeat_end',
                            '$occurrenceEnd',
                            '0',
                            NOW()
                        )
                    ";
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                
                }

                // insert defined metas
                $metaKey = $questionnaireOccurrence['frequency']['meta_key'];
                $metaValue = $questionnaireOccurrence['frequency']['meta_value'];
                $customFlag = $questionnaireOccurrence['frequency']['custom'];
                $sql = "
                    INSERT INTO 
                        FrequencyEvents (
                            ControlTable,
                            ControlTableSerNum,
                            MetaKey,
                            MetaValue,
                            CustomFlag,
                            DateAdded
                        )
                    VALUES (
                        'LegacyQuestionnaireControl',
                        '$questionnaireSer',
                        '$metaKey|lqc_$questionnaireSer',
                        '$metaValue',
                        '$customFlag',
                        NOW()
                    )
                ";

                $query = $host_db_link->prepare( $sql );
                $query->execute();

                $additionalMeta = $questionnaireOccurrence['frequency']['additionalMeta'];
                if (!empty($additionalMeta)) {
                    foreach ($additionalMeta as $meta) {

                        $metaKey = $meta['meta_key'];
                        $metaValue = implode(',', $meta['meta_value']);

                        $sql = "
                            INSERT INTO 
                                FrequencyEvents (
                                    ControlTable,
                                    ControlTableSerNum,
                                    MetaKey,
                                    MetaValue,
                                    CustomFlag,
                                    DateAdded
                                )
                            VALUES (
                                'LegacyQuestionnaireControl',
                                '$questionnaireSer',
                                '$metaKey|lqc_$questionnaireSer',
                                '$metaValue',
                                '1',
                                NOW()
                            )
                        ";

                        $query = $host_db_link->prepare( $sql );
                        $query->execute();

                    }
                }
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
     * @param object $user : the current user in session
     * @return array : response
     */        
    public function deleteLegacyQuestionnaire( $questionnaireSer, $user ) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
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

            $sql = "
                DELETE FROM
                    FrequencyEvents
                WHERE
                    FrequencyEvents.ControlTableSerNum  = $questionnaireSer
                AND FrequencyEvents.ControlTable        = 'LegacyQuestionnaireControl'
            ";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE QuestionnaireControlMH
                SET 
                    QuestionnaireControlMH.LastUpdatedBy = '$userSer',
                    QuestionnaireControlMH.SessionId = '$sessionId'
                WHERE
                    QuestionnaireControlMH.QuestionnaireControlSerNum = $questionnaireSer
                ORDER BY QuestionnaireControlMH.RevSerNum DESC 
                LIMIT 1
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
        $questionnaireIntro_EN      = $legacyQuestionnaireDetails['intro_EN'];
        $questionnaireIntro_FR      = $legacyQuestionnaireDetails['intro_FR'];
        $questionnaireSer	        = $legacyQuestionnaireDetails['serial'];
		$questionnaireTriggers	    = $legacyQuestionnaireDetails['triggers'];
        $questionnaireOccurrence    = $legacyQuestionnaireDetails['occurrence'];

        $userSer                    = $legacyQuestionnaireDetails['user']['id'];
        $sessionId                  = $legacyQuestionnaireDetails['user']['sessionid'];

        $existingTriggers	= array();

        $detailsUpdated             = $legacyQuestionnaireDetails['details_updated'];
        $triggersUpdated             = $legacyQuestionnaireDetails['triggers_updated'];

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
    					QuestionnaireControl 
    				SET 
    					QuestionnaireControl.QuestionnaireName_EN 		= \"$questionnaireName_EN\", 
    					QuestionnaireControl.QuestionnaireName_FR 		= \"$questionnaireName_FR\",
                        QuestionnaireControl.Intro_EN                   = \"$questionnaireIntro_EN\",
                        QuestionnaireControl.Intro_FR                   = \"$questionnaireIntro_FR\",
                        QuestionnaireControl.LastUpdatedBy              = '$userSer',
                        QuestionnaireControl.SessionId                  = '$sessionId'
    				WHERE 
    					QuestionnaireControl.QuestionnaireControlSerNum = $questionnaireSer
    			";

    			$query = $host_db_link->prepare( $sql );
    			$query->execute();
            }

            if ($triggersUpdated) {

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

                    $triggerArray = array(
                        'type'  => $data[0],
                        'id'    => $data[1]
                    );
    				array_push($existingTriggers, $triggerArray);
    			}

                if (!empty($existingTriggers)) {
                    // If old filters not in new, remove from DB
    	    		foreach ($existingTriggers as $existingTrigger) {
                        $id     = $existingTrigger['id'];
                        $type   = $existingTrigger['type'];
                        if (!$this->nestedSearch($id, $type, $questionnaireTriggers)) {
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

                            $sql = "
                                UPDATE FiltersMH
                                SET 
                                    FiltersMH.LastUpdatedBy = '$userSer',
                                    FiltersMH.SessionId = '$sessionId'
                                WHERE
                                    FiltersMH.FilterId              = \"$id\"
                                AND FiltersMH.FilterType            = '$type'
                                AND FiltersMH.ControlTableSerNum    = $questionnaireSer
                                AND FiltersMH.ControlTable          = 'LegacyQuestionnaireControl'
                                ORDER BY FiltersMH.DateAdded DESC 
                                LIMIT 1
                            ";
                            $query = $host_db_link->prepare( $sql );
                            $query->execute();
    			    	}
        			}   
                }
                if (!empty($questionnaireTriggers)) {
                    // If new filters, insert into DB
        			foreach ($questionnaireTriggers as $trigger) {
                        $id     = $trigger['id'];
                        $type   = $trigger['type'];
                        if (!$this->nestedSearch($id, $type, $existingTriggers)) {
                            $sql = "
                                INSERT INTO 
                                    Filters (
                                        ControlTable,
                                        ControlTableSerNum,
                                        FilterId,
                                        FilterType,
                                        DateAdded,
                                        LastUpdatedBy,
                                        SessionId
                                    )
                                VALUES (
                                    'LegacyQuestionnaireControl',
                                    '$questionnaireSer',
                                    \"$id\",
                                    '$type',
                                    NOW(),
                                    '$userSer',
                                    '$sessionId'
                                )
    			    		";
    				    	$query = $host_db_link->prepare( $sql );
    					    $query->execute();
        				}
    	    		}
                }
            }

            if (!$questionnaireOccurrence['set']) {
                $sql = "
                    DELETE FROM 
                        FrequencyEvents 
                    WHERE
                        FrequencyEvents.ControlTable        = 'LegacyQuestionnaireControl'
                    AND FrequencyEvents.ControlTableSerNum  = $questionnaireSer
                ";

                $query = $host_db_link->prepare( $sql );
                $query->execute();
            }

            if ($questionnaireOccurrence['set']) {

                $occurrenceStart = $questionnaireOccurrence['start_date'];
                $sql = "
                    INSERT INTO 
                        FrequencyEvents (
                            ControlTable,
                            ControlTableSerNum,
                            MetaKey,
                            MetaValue,
                            CustomFlag,
                            DateAdded
                        )
                    VALUES (
                        'LegacyQuestionnaireControl',
                        '$questionnaireSer',
                        'repeat_start',
                        '$occurrenceStart',
                        '0',
                        NOW()
                    )
                    ON DUPLICATE KEY 
                    UPDATE 
                        MetaValue = '$occurrenceStart'
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();

                $occurrenceEnd = $questionnaireOccurrence['end_date'];
                if (!$occurrenceEnd) {
                    $sql = "
                        DELETE FROM 
                            FrequencyEvents
                        WHERE 
                            FrequencyEvents.ControlTable        = 'LegacyQuestionnaireControl'
                        AND FrequencyEvents.ControlTableSerNum  = $questionnaireSer
                        AND FrequencyEvents.MetaKey             = 'repeat_end'
                    ";
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                }
                else {
                    $sql = "
                        INSERT INTO 
                            FrequencyEvents (
                                ControlTable,
                                ControlTableSerNum,
                                MetaKey,
                                MetaValue,
                                CustomFlag,
                                DateAdded
                            )
                        VALUES (
                            'LegacyQuestionnaireControl',
                            '$questionnaireSer',
                            'repeat_end',
                            '$occurrenceEnd',
                            '0',
                            NOW()
                        )
                        ON DUPLICATE KEY 
                        UPDATE 
                            MetaValue = '$occurrenceEnd'
                    ";
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                }

                // clear all other metas
                $sql = "
                    DELETE FROM
                        FrequencyEvents
                    WHERE
                        FrequencyEvents.ControlTable        = 'LegacyQuestionnaireControl'
                    AND FrequencyEvents.ControlTableSerNum  = $questionnaireSer
                    AND FrequencyEvents.MetaKey             != 'repeat_start'
                    AND FrequencyEvents.MetaKey             != 'repeat_end'
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();

                // insert defined metas
                $metaKey = $questionnaireOccurrence['frequency']['meta_key'];
                $metaValue = $questionnaireOccurrence['frequency']['meta_value'];
                $customFlag = $questionnaireOccurrence['frequency']['custom'];
                $sql = "
                    INSERT INTO 
                        FrequencyEvents (
                            ControlTable,
                            ControlTableSerNum,
                            MetaKey,
                            MetaValue,
                            CustomFlag,
                            DateAdded
                        )
                    VALUES (
                        'LegacyQuestionnaireControl',
                        '$questionnaireSer',
                        '$metaKey|lqc_$questionnaireSer',
                        '$metaValue',
                        '$customFlag',
                        NOW()
                    )
                ";

                $query = $host_db_link->prepare( $sql );
                $query->execute();

                $additionalMeta = $questionnaireOccurrence['frequency']['additionalMeta'];
                if (!empty($additionalMeta)) {
                    foreach ($additionalMeta as $meta) {

                        $metaKey = $meta['meta_key'];
                        $metaValue = implode(',', $meta['meta_value']);

                        $sql = "
                            INSERT INTO 
                                FrequencyEvents (
                                    ControlTable,
                                    ControlTableSerNum,
                                    MetaKey,
                                    MetaValue,
                                    CustomFlag,
                                    DateAdded
                                )
                            VALUES (
                                'LegacyQuestionnaireControl',
                                '$questionnaireSer',
                                '$metaKey|lqc_$questionnaireSer',
                                '$metaValue',
                                '1',
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
     * Gets chart logs of a legacy questionnaire or questionnaires
     *
     * @param integer $serial : the legacy questionnaire serial number
     * @return array $legacyQuestionnaireLogs : the legacy questionnaire logs for highcharts
     */
    public function getLegacyQuestionnaireChartLogs ($serial) {
        $legacyQuestionnaireLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            // get all logs for all legacy questionnaires
            if (!$serial) {
                $sql = "
                    SELECT DISTINCT
                        lqmh.CronLogSerNum,
                        COUNT(lqmh.CronLogSerNum),
                        cl.CronDateTime,
                        qc.QuestionnaireName_EN
                    FROM
                        QuestionnaireMH lqmh,
                        CronLog cl,
                        QuestionnaireControl qc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = lqmh.CronLogSerNum
                    AND lqmh.CronLogSerNum IS NOT NULL
                    AND lqmh.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
                    GROUP BY
                        lqmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $legacyQuestionnaireSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $legacyQuestionnaireDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($legacyQuestionnaireSeries[$seriesName])) {
                        $legacyQuestionnaireSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($legacyQuestionnaireSeries[$seriesName]['data'], $legacyQuestionnaireDetail);
                }

                foreach ($legacyQuestionnaireSeries as $seriesName => $series) {
                    array_push($legacyQuestionnaireLogs, $series);
                }

            }
            // get logs for specific legacy questionnaire
            else {
                $sql = "
                    SELECT DISTINCT
                        lqmh.CronLogSerNum,
                        COUNT(lqmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        QuestionnaireMH lqmh,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = lqmh.CronLogSerNum
                    AND lqmh.CronLogSerNum IS NOT NULL
                    AND lqmh.QuestionnaireControlSerNum = $serial
                    GROUP BY
                        lqmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $legacyQuestionnaireSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = 'Legacy Questionnaire';
                    $legacyQuestionnaireDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($legacyQuestionnaireSeries[$seriesName])) {
                        $legacyQuestionnaireSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($legacyQuestionnaireSeries[$seriesName]['data'], $legacyQuestionnaireDetail);
                }

                foreach ($legacyQuestionnaireSeries as $seriesName => $series) {
                    array_push($legacyQuestionnaireLogs, $series);
                }
            }
            return $legacyQuestionnaireLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $legacyQuestionnaireLogs;
        }
    }

    /**
     *
     * Gets list logs of legacy questionnaires during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @return array $legacyQuestionnaireLogs : the legacy questionnaire logs for table view
     */
    public function getLegacyQuestionnaireListLogs ($serials) {
        $legacyQuestionnaireLogs = array();
        $serials = implode(',', $serials);
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    qc.QuestionnaireName_EN,
                    lqmh.QuestionnaireControlSerNum,
                    lqmh.QuestionnaireRevSerNum,
                    lqmh.CronLogSerNum,
                    lqmh.PatientSerNum,
                    lqmh.PatientQuestionnaireDBSerNum,
                    lqmh.CompletedFlag,
                    lqmh.CompletionDate,
                    lqmh.DateAdded,
                    lqmh.ModificationAction
                FROM
                    QuestionnaireMH lqmh,
                    QuestionnaireControl qc
                WHERE
                    lqmh.QuestionnaireControlSerNum     = qc.QuestionnaireControlSerNum
                AND lqmh.CronLogSerNum                  IN ($serials)
            "; 
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $logDetails = array (
                   'control_name'           => $data[0],
                   'control_serial'         => $data[1],
                   'revision'               => $data[2],
                   'cron_serial'            => $data[3],
                   'patient_serial'         => $data[4],
                   'pt_questionnaire_db'    => $data[5],
                   'completed'              => $data[6],
                   'completion_date'        => $data[7],
                   'date_added'             => $data[8],
                   'mod_action'             => $data[9]
                );
                array_push($legacyQuestionnaireLogs, $logDetails);
            }

            return $legacyQuestionnaireLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $legacyQuestionnaireLogs;
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
