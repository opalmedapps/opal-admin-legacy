<?php
/**
 *
 * Questionnaire class 
 */
class Questionnaire{

	/**
     *
     * Gets a list of existing questionnaires for a particular user
     *
     * @param integer $userid    : the user id
     * @return array $questionnaires : the list of existing questionnaires
     */
	public function getQuestionnaires($userid){
		$questionnaires = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					serNum,
					name_EN,
					name_FR,
					private,
					publish,
					last_updated,
					created_by
				FROM
					QuestionnaireControlNew
				WHERE
					private = 0 
				OR
					created_by = $userid
				
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			// fetch
			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serNum 		= $data[0];
	 			$name_EN   		= $data[1];
	 			$name_FR		= $data[2];
	 			$private 		= $data[3];
	 			$publish 		= $data[4];
	 			$last_updated	= $data[5];
	 			$created_by 	= $data[6];
				$filters 		= array();

	 			$questionnaireArray = array(
	 				'serNum'		=> $serNum,
	 				'name_EN'		=> $name_EN,
	 				'name_FR'		=> $name_FR,
	 				'private'		=> $private,
	 				'publish'		=> $publish,
	 				'changed'		=> 0,
	 				'last_updated'	=> $last_updated,
	 				'created_by'	=> $created_by,
	 				'tags' 			=> array()
	 			);

				$sql = "
                    SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
                    FROM
                        QuestionnaireControlNew que,
                        Filters
                    WHERE   
                        que.serNum     							= $serNum
                    AND Filters.ControlTable                    = 'QuestionnaireControl'
                    AND Filters.ControlTableSerNum              = que.serNum
                    AND Filters.FilterType                      != ''
                    AND Filters.FilterId                        != ''
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
    
    				array_push($filters, $filterArray);
                }
	 			
	 			// get tags
				$tagsql = "
					SELECT
						tag_serNum,
						name_EN,
						name_FR
					FROM
						Questionnaire_tag,
						QuestionnaireTag
					WHERE
						Questionnaire_tag.tag_serNum = QuestionnaireTag.serNum
					AND
						Questionnaire_tag.questionnaire_serNum = $serNum
				";

				$tagquery = $host_db_link->prepare($tagsql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$tagquery->execute();

				while ($tagdata = $tagquery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

					$tag_serNum = $tagdata[0];
					$tagName_EN = $tagdata[1];
					$tagName_FR = $tagdata[2];

					$tagArray = array(
						'serNum'	=> $tag_serNum,
						'name_EN'	=> $tagName_EN,
						'name_FR'	=> $tagName_FR
					);

					array_push($questionnaireArray['tags'], $tagArray);
				}
				array_push($questionnaires, $questionnaireArray);
	 		}
	 		return $questionnaires; 
	 	}
	 	catch (PDOException $e) {
 			echo $e->getMessage();
 			return $questionnaires;
	 	}
	}

	/**
     *
     * Gets questionnaire details
     *
     * @param integer $questionnaireSerNum : the questionnaire serial number
     * @return array $questionnaireDetails : the questionnaire details
     */
	public function getQuestionnaireDetails($questionnaireSerNum){

		$questionnaireDetails = array();

		try{
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					serNum,
					name_EN,
					name_FR,
					private,
					publish,
					last_updated_by
				FROM
					QuestionnaireControlNew
				WHERE
					serNum = $questionnaireSerNum
			";

			$query = $host_db_link->prepare($sql);
			$query->execute();

			// fetch
			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

 			$serNum 		= $data[0];
 			$name_EN   		= $data[1];
 			$name_FR		= $data[2];
 			$private 		= $data[3];
 			$publish 		= $data[4];
 			$last_updated_by= $data[5];
			$filters 		= array();

			$sql = "
                SELECT DISTINCT 
                    Filters.FilterType,
                    Filters.FilterId
                FROM
                    QuestionnaireControlNew que,
                    Filters
                WHERE
                    que.serNum     							= $serNum
                AND Filters.ControlTable                    = 'QuestionnaireControl'
                AND Filters.ControlTableSerNum              = que.serNum
                AND Filters.FilterType                      != ''
                AND Filters.FilterId                        != ''
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
    
	    		array_push($filters, $filterArray);
            }

 			$questionnaireDetails = array(
 				'serNum'			=> $serNum,
 				'name_EN'			=> $name_EN,
 				'name_FR'			=> $name_FR,
 				'private'			=> $private,
 				'publish'			=> $publish,
 				'last_updated_by'	=> $last_updated_by,
 				'groups'			=> array(),
 				'tags' 				=> array(),
				'filters'			=> $filters
 			);
 			
 			// get groups
 			$groupsql = "
 				SELECT
 					Questionnaire_questiongroup.position,
 					Questionnaire_questiongroup.questiongroup_serNum,
 					Questionnaire_questiongroup.optional,
 					Questionnaire_questiongroup.last_updated_by,
 					Questiongroup.name_EN,
 					Questiongroup.name_FR 					
 				FROM
 					Questionnaire_questiongroup,
 					Questiongroup
 				WHERE
 					Questionnaire_questiongroup.questionnaire_serNum = $questionnaireSerNum
 				AND
 					Questiongroup.serNum = Questionnaire_questiongroup.questiongroup_serNum
 				ORDER BY
 					Questionnaire_questiongroup.position
 			";

 			$query = $host_db_link->prepare($groupsql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();
 			
 			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
 				$questiongroup = array(
 					'serNum'			=> $data[1],
 					'position'			=> intval($data[0]),
 					'optional'			=> $data[2],
 					'last_updated_by'	=> $data[3],
 					'name_EN'			=> $data[4],
 					'name_FR'			=> $data[5]
 				);
 				array_push($questionnaireDetails['groups'], $questiongroup);
 			}

 			// get tag
 			$tagsql = "
 				SELECT
 					Questionnaire_tag.tag_serNum,
 					Questionnaire_tag.last_updated_by,
 					QuestionnaireTag.name_EN,
 					QuestionnaireTag.name_FR 					
 				FROM
 					Questionnaire_tag,
 					QuestionnaireTag
 				WHERE
 					Questionnaire_tag.questionnaire_serNum = $questionnaireSerNum
 				AND
 					QuestionnaireTag.serNum = Questionnaire_tag.tag_serNum
 			";

 			$query = $host_db_link->prepare($tagsql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();
 			
 			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
 				$tag = array(
 					'serNum'			=> $data[0],
 					'name_EN'			=> $data[2],
 					'name_FR'			=> $data[3],
 					'last_updated_by'	=> $data[1]
 				);
 				array_push($questionnaireDetails['tags'], $tag);
 			}

 			return $questionnaireDetails;
		} catch( PDOException $e) {
			return $e->getMessage();
			return $questionnaireDetails;
		}
	}

	/**
     *
     * Inserts a questionnaire into our database
     *
     * @param array $questionnaireDetails  : the questionnaire details
     * @return void
     */
	public function insertQuestionnaire($questionnaireDetails){

		$name_EN 				= $questionnaireDetails['name_EN'];
		$name_FR 				= $questionnaireDetails['name_FR'];
		$private 				= $questionnaireDetails['private'];
		$publish 				= $questionnaireDetails['publish'];
		$created_by 			= $questionnaireDetails['created_by'];
		$last_updated_by 		= $questionnaireDetails['last_updated_by'];
		$tags 					= $questionnaireDetails['tags'];
		$questiongroups 		= $questionnaireDetails['questiongroups'];
		$filters 				= $questionnaireDetails['filters'];
		$userSer 				= $questionnaireDetails['user']['id'];
		$sessionId 				= $questionnaireDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				INSERT INTO
					QuestionnaireControlNew(
						name_EN,
						name_FR,
						private,
						publish,
						last_updated_by,
						created_by,
						session_id
					)
				VALUES(
					\"$name_EN\",
					\"$name_FR\",
					'$private',
					'$publish',
					'$userSer',
					'$created_by',
					'$sessionId'
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$questionnaire_id =  $host_db_link->lastInsertId();
			
			foreach($questiongroups as $group){
				$group_id = $group['questiongroup_serNum'];
				$optional = $group['optional'];
				$position = $group['position'];

				$sql = "
					INSERT INTO
						Questionnaire_questiongroup(
							questionnaire_serNum,
							questiongroup_serNum,
							position,
							optional,
							last_updated_by,
							created_by
						)
					VALUES(
						'$questionnaire_id',
						'$group_id',
						'$position',
						'$optional',
						'$last_updated_by',
						'$created_by'
					)
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();

				
			}

			//add tag
			foreach($tags as $tag){
				$sql = "
					INSERT INTO
						Questionnaire_tag(
							questionnaire_serNum,
							tag_serNum,
							last_updated_by,
							created_by
						)
					VALUES(
						'$questionnaire_id',
						'$tag',
						'$last_updated_by',
						'$created_by'
					)
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}

			if ($filters) {
                foreach ($filters as $filter) {
    
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
                        VALUES (
                            'QuestionnaireControl',
                            '$questionnaire_id',
                            '$filterType',
                            \"$filterId\",
                            NOW()
                        )
		    		";
			    	$query = $host_db_link->prepare( $sql );
				    $query->execute();
                }
            }

		} catch( PDOException $e) {
			return $e->getMessage();
		}
	}

	/**
     *
     * Deletes a questionnaire 
     *
     * @param integer $questionnaire_serNum : the questionnaire serial number
     * @param object $user : the current user in the session
     * @return array $response : response
     */
	public function deleteQuestionnaire($questionnaire_serNum, $user){
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
                    Filters
                WHERE
                    Filters.ControlTableSerNum   = $questionnaire_serNum
                AND Filters.ControlTable         = 'QuestionnaireControl'
            ";
            $query = $host_db_link->prepare( $sql );
			$query->execute();

			//delete from questionnaire_questiongroup
			$sql1 = "
				DELETE FROM
					Questionnaire_questiongroup
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql1 );
			$query->execute();
			//delete from questionnaire_user
			$sql2 = "
				DELETE FROM
					Questionnaire_user
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql2 );
			$query->execute();
			//delete from questionnaire_tag
			$sql3 = "
				DELETE FROM
					Questionnaire_tag
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql3 );
			$query->execute();
			//delete from questionnaire
			$sql4 = "
				DELETE FROM
					QuestionnaireControlNew
				WHERE
					serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql4 );
			$query->execute();

			$sql = "
                UPDATE QuestionnaireControlNewMH
                SET 
                    QuestionnaireControlNewMH.last_updated_by = '$userSer',
                    QuestionnaireControlNewMH.session_id = '$sessionId'
                WHERE
                    QuestionnaireControlNewMH.questionnaire_serNum = $questionnaire_serNum
                ORDER BY QuestionnaireControlNewMH.rev_serNum DESC 
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

			$response['value'] = 1; // Success
            return $response;

		} catch(PDOException $e) {
			$response['message'] = $e->getMessage();
			return $response;
		}
	}

	/**
     *
     * Inerts a question group to a questionnaire
     *
     * @param array $questionGroupDetails  : the question group details
     * @return array $response : response
     */
	public function insertQuestionGroupToQuestionnaire($questionGroupDetails){
		$groups = $questionGroupDetails['groups'];
		$response = array(
            'value'     => 0,
            'message'   => ''
        );
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			foreach($groups as $group) {
				$questionnaire_serNum = $group['questionnaire_serNum'];
				$questiongroup_serNum = $group['questiongroup_serNum'];
				$optional = $group['optional'];
				$position = $group['position'];
				$last_updated_by = $group['last_updated_by'];
				$created_by = $group['created_by'];

				$sql = "
					INSERT INTO
						Questionnaire_questiongroup(
							questionnaire_serNum,
							questiongroup_serNum,
							optional,
							position,
							created_by,
							last_updated_by
						)
					VALUES(
						'$questionnaire_serNum',
						'$questiongroup_serNum',
						'$optional',
						'$position',
						'$created_by',
						'$last_updated_by'
					)
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}

			$response['value'] = 1; // Success
            return $response;

		} catch (PDOException $e) {
			$response['message'] = $e->getMessage();
			return $response;
	 	}

	}
	
	/**
     *
     * Sets a questionnaire publish flag for a particular questionnaire
     *
     * @param integer $questionnaire_serNum : the questionnaire serial number
     * @return void
     */
	public function publishQuestionnaire($questionnaire_serNum){
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				UPDATE
					QuestionnaireControlNew
				SET
					publish = 1
				WHERE
					serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}

	/**
     *
     * Updates a questionnaire
     *
     * @param array $questionnaireDetails  : the questionnaire details
     * @return array $response : response
     */
	public function updateQuestionnaire($questionnaireDetails){

		$serNum 			= $questionnaireDetails['serNum'];
		$name_EN 			= $questionnaireDetails['name_EN'];
		$name_FR 			= $questionnaireDetails['name_FR'];
		$private 			= $questionnaireDetails['private'];
		$publish 			= $questionnaireDetails['publish'];
		$last_updated_by 	= $questionnaireDetails['last_updated_by'];
		$tags 				= $questionnaireDetails['tags'];
		$questiongroups 	= $questionnaireDetails['groups'];
		$filters 			= $questionnaireDetails['filters'];
		$userSer 			= $questionnaireDetails['user']['id'];
		$sessionId 			= $questionnaireDetails['user']['sessionid'];
		$existingFilters 	= array();

		$response = array(
            'value'     => 0,
            'message'   => ''
        );

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				UPDATE
					QuestionnaireControlNew
				SET
					QuestionnaireControlNew.name_EN 		= \"$name_EN\",
					QuestionnaireControlNew.name_FR 		= \"$name_FR\",
					QuestionnaireControlNew.private 		= '$private',
					QuestionnaireControlNew.publish 		= '$publish',
					QuestionnaireControlNew.last_updated_by = '$userSer',
					QuestionnaireControlNew.session_id 		= '$sessionId'
				WHERE
					QuestionnaireControlNew.serNum = $serNum
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$existingGroups = array();

			$sql = "
				SELECT DISTINCT 
					qg.questiongroup_serNum
				FROM
					Questionnaire_questiongroup qg 
				WHERE
					qg.questionnaire_serNum = '$serNum'
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();
 			
 			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
 				array_push($existingGroups, $data[0]);
 			}
			
			foreach($existingGroups as $existingGroup) {
				// if old groups not in new, delete it
				if (!$this->nestedSearch($existingGroup, $questiongroups)) {
					$sql = "
						DELETE FROM 
							Questionnaire_questiongroup  
						WHERE 
							Questionnaire_questiongroup.questionnaire_serNum = '$serNum'
						AND Questionnaire_questiongroup.questiongroup_serNum = '$existingGroup'
					";
					$query = $host_db_link->prepare( $sql );
					$query->execute();
				}
			}
			
			foreach($questiongroups as $group){

				$group_id = $group['serNum'];
				$optional = $group['optional'];
				$position = $group['position'];

				$sql = "
					INSERT INTO 
						Questionnaire_questiongroup(
							questionnaire_serNum,
							questiongroup_serNum,
							position,
							optional,
							last_updated_by,
							created_by
						)
					VALUES (
						'$serNum',
						'$group_id',
						'$position',
						'$optional',
						'$last_updated_by',
						'$last_updated_by'
					)
					ON DUPLICATE KEY UPDATE
						position 		= VALUES(position),
						optional 		= VALUES(optional),
						last_updated_by = VALUES(last_updated_by)
				";

				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}

			// add or delete tag
			foreach($tags as $tag){
				$added = $tag['added'];
				$tagSerNum = $tag['serNum'];
				// insert new tags
				if($added){
					$sql = "
						INSERT INTO
							Questionnaire_tag (
								questionnaire_serNum,
								tag_serNum,
								created_by,
								last_updated_by
							)
						VALUES(
							'$serNum',
							'$tagSerNum',
							'$last_updated_by',
							'$last_updated_by'
						)
					";
					$query = $host_db_link->prepare( $sql );
					$query->execute();
				} else { //delete
					$sql = "
						DELETE FROM
							Questionnaire_tag
						WHERE
							tag_serNum = $tagSerNum
						AND
							questionnaire_serNum = $serNum
					";
					$query = $host_db_link->prepare( $sql );
					$query->execute();
				}
			}

			$sql = "
		        SELECT DISTINCT 
                    Filters.FilterType,
                    Filters.FilterId
    			FROM     
    				Filters
		    	WHERE 
                    Filters.ControlTableSerNum       = $serNum
                AND Filters.ControlTable             = 'QuestionnaireControl'
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

            if($existingFilters) { 

                // If old filters not in new filter list, then remove
    	    	foreach ($existingFilters as $existingFilter) {
                    $id     = $existingFilter['id'];
                    $type   = $existingFilter['type'];
                    if (!$this->nestedSearchFilter($id, $type, $filters)) {
				    	$sql = "
                            DELETE FROM 
    					    	Filters
    	    				WHERE
                                Filters.FilterId            = \"$id\"
                            AND Filters.FilterType          = '$type'
                            AND Filters.ControlTableSerNum   = $serNum
                            AND Filters.ControlTable         = 'QuestionnaireControl'
		    		    ";  
            
	    	    		$query = $host_db_link->prepare( $sql );
		    	    	$query->execute();
                    }
                }
	    	}

            if($filters) {

                // If new filters (i.e. not in old list), then insert
    			foreach ($filters as $filter) {
                    $id     = $filter['id'];
                    $type   = $filter['type'];
                    if (!$this->nestedSearchFilter($id, $type, $existingFilters)) {
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
                                'QuestionnaireControl',
                                '$serNum',
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

			$response['value'] = 1; // Success
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
     * @param array $array  : the key-value haystack
     * @return boolean
     */
	public function nestedSearch($id, $array) {
		if (empty($array) || !$id) {
			return 0;
		}
		foreach ($array as $key => $val) {
			if ($val['questiongroup_serNum'] == $id) {
				return 1;
			}
		}
		return 0;
	}

	/**
     *
     * Does a nested search for filter match
     *
     * @param string $id    : the needle id
     * @param string $type  : the needle type
     * @param array $array  : the key-value haystack
     * @return boolean
     */
    public function nestedSearchFilter($id, $type, $array) {
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

	/**************************************
	 * TODO
	 **************************************/
	 
	/* Get patients' completed Questionnaire
	 * @param: $post(questionnaire_patient serial number, questionnaire serial number, created_by(doctor id))
	 * @return questionGroups as array
	 */
	// public function getCompletedQuestionnaire($post){
	// 	$qpSerNum = $post['qpSerNum'];
	// 	$qSerNum = $post['qSerNum'];
	// 	$created_by = $post['created_by'];

	// 	$questionGroups = array();

	// 	try {
	// 		$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
	// 		$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	// 		$sql = "
	// 			SELECT
	// 				Questiongroup.serNum,
	// 				Questiongroup.name_EN
	// 			FROM
	// 				Questionnaire,
	// 				Questionnaire_questiongroup,
	// 				Questiongroup
	// 			WHERE
	// 				Questionnaire.serNum = Questionnaire_questiongroup.questionnaire_serNum
	// 			AND
	// 				Questionnaire_questiongroup.questiongroup_serNum = questiongroup_serNum
	// 			AND
	// 				Questionnaire.serNum = $qSerNum
	// 		";
	// 		$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 		$query->execute();

	// 		while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
	// 			$questiongroup_serNum = $data[0];
	// 			$questiongroup_name = $data[1];
	// 			$questiongroup = new Group($questiongroup_serNum, $questiongroup_name);
	// 			array_push($questionGroups,$questiongroup) ;

	// 			//get questions from each question group
	// 			$qSQL = "
	// 				SELECT
	// 					QuestionnaireQuestion.serNum,
	// 					QuestionnaireQuestion.text_EN,
	// 					QuestionnaireQuestion.answertype_serNum
	// 				FROM
	// 					QuestionnaireQuestion
	// 				WHERE
	// 					QuestionnaireQuestion.questiongroup_serNum = $questiongroup_serNum
	// 			";
	// 			$qResult = $host_db_link->prepare($qSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 			$qResult->execute();

	// 			while($row = $qResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
	// 				$question_serNum = $row[0];
	// 				$question_text = $row[1];
	// 				$type_serNum = $row[2];
					
	// 				//read in answer type
	// 				$qtSQL = "
	// 					SELECT
	// 						serNum,
	// 						name_EN,
	// 						private,
	// 						category_EN,
	// 						created_by
	// 					FROM
	// 						QuestionnaireAnswerType
	// 					WHERE
	// 						serNum = $type_serNum
	// 				";
	// 				$qtResult = $host_db_link->prepare($qtSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 				$qtResult->execute();

	// 				$qtrow = $qtResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
	// 				$qtSerNum = $qtrow[0];
	// 				$qtName = $qtrow[1];
	// 				$qtPrivate = $qtrow[2];
	// 				$qtCat = $qtrow[3];

	// 				//get patients' answer for the question
	// 				$pSQL = "
	// 					SELECT
	// 						QuestionnaireAnswerOption.text_EN
	// 					FROM
	// 						QuestionnaireAnswer,
	// 						QuestionnaireAnswerOption,
	// 						Questionnaire_patient
	// 					WHERE
	// 						Questionnaire_patient.serNum = $qpSerNum
	// 					AND
	// 						Questionnaire_patient.serNum = QuestionnaireAnswer.questionnaire_patient_serNum
	// 					AND
	// 						QuestionnaireAnswer.answeroption_serNum = QuestionnaireAnswerOption.serNum
	// 					AND
	// 						QuestionnaireAnswer.question_serNum = $question_serNum
	// 				";
	// 				$pResult = $host_db_link->prepare($pSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 				$pResult->execute();

	// 				//store answertype
	// 				$curr_qt;
	// 				//if answer exists
	// 				if($prow = $pResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
	// 					$answer_text = $prow[0];
	// 					if($qtCat == 'Short Answer'){
	// 						$optionSQL = "
	// 							SELECT
	// 								QuestionnaireAnswerText.answer_text
	// 							FROM
	// 								QuestionnaireAnswerText,
	// 								QuestionnaireAnswerOption
	// 							WHERE
	// 								QuestionnaireAnswerText.answer_serNum = QuestionnaireAnswerOption.serNum
	// 							AND 
	// 								QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
	// 						";
	// 						$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 						$optionResult->execute();

	// 						$optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
	// 						$answer_text = $optionrow[0];
	// 					}
	// 					$curr_qt = new AnswerType($qtSerNum, $qtName, $qtPrivate, $qtCat, $answer_text);
	// 				}
	// 				//no answer
	// 				else{
	// 					$curr_qt = new AnswerType($qtSerNum, $qtName, $qtPrivate, $qtCat, null);
	// 				}

	// 				$question = new Question($question_serNum, $question_text, $curr_qt);
	// 				$questionGroup->addQuestion($question);

	// 				//read in options for each anwser type
	// 				if($qtCat = 'Linear Scale'){
	// 					$optionSQL = "
	// 						SELECT
	// 							text_EN,
	// 							caption_EN
	// 						FROM
	// 							QuestionnaireAnswerOption
	// 						WHERE
	// 							QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
	// 					";
	// 					$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 					$optionResult->execute();

	// 					$min = null;
	// 					while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
	// 						$text_EN = $optionrow[0];
	// 						$caption_EN = $optionrow[1];

	// 						$curr_qt->addOption($text_EN);
	// 						//set min if 1st caption
	// 						if($caption_EN!=null && $min==null){
	// 							$min = $caption_EN;
	// 							$curr_qt->setMinCaption($caption_EN);
	// 						}
	// 						//check if caption is max and set
	// 						else if($caption_EN!=null && $min!=null){
	// 							if($min>$text_EN){
	// 								$curr_qt->setAndSwitchMinCaption($caption_EN);
	// 							}
	// 							else {
	// 								$curr_qt->setMaxCaption($caption_EN);
	// 							}
	// 						}
	// 					}
	// 				}
	// 				else if($qtCat = 'Short Answer'){
	// 					// no option
	// 				}
	// 				else {
	// 					$optionSQL = "
	// 						SELECT
	// 							text_EN
	// 						FROM
	// 							QuestionnaireAnswerOption
	// 						WHERE
	// 							QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
	// 					";
	// 					$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	// 					$optionResult->execute();

	// 					while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
	// 						$curr_qt->addOption($optionrow[0]);
	// 					}
	// 				}
	// 			}
	// 		}
	// 		return $questionGroups;
	// 	} catch(PDOException $e) {
	//  		echo $e->getMessage();
	//  		return $questionGroups;
	//  	}
	// }


}
?>