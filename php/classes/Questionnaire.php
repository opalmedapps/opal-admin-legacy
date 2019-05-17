<?php
/**
 *
 * Questionnaire class
 */
class Questionnaire extends QuestionnaireModule {


    /*
     * This function returns a list of questionnaire an user can access
     * @param   void
     * @return  list of questionnaires (array)
     * */
    public function getQuestionnaires(){
        $results = $this->questionnaireDB->fetchAllQuestionnaires();
        foreach($results as &$questionnaire) {
            $questionnaire["locked"] = $this->isQuestionnaireLocked($questionnaire["ID"]);
        }
        return $results;
    }

    /*
     * This function validate and sanitize a questionnaire
     * @param   $questionnaireToSanitize (array)
     * @return  sanitized questionnaire or false if invalid
     * */
    function validateAndSanitize($questionnaireToSanitize) {
        //Sanitize the name of the questionnaire. If it is empty, rejects it.
        $validatedQuestionnaire = array(
            "text_EN"=>htmlspecialchars($questionnaireToSanitize['text_EN'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            "text_FR"=>htmlspecialchars($questionnaireToSanitize['text_FR'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        );
        if ($validatedQuestionnaire["text_EN"] == "" || $validatedQuestionnaire["text_FR"] == "")
            return false;

        //Sanitize the questionnaire ID (if any). IF there was supposed to be an ID and it's empty, reject the
        //questionnaire
        if($questionnaireToSanitize["ID"] != "") {
            $validatedQuestionnaire["ID"] = strip_tags($questionnaireToSanitize["ID"]);
            if($validatedQuestionnaire["ID"] == "")
                return false;
        }

        //Sanitize the list of libraries associated to the questionnaire
        $libraries = array();
        if (count($questionnaireToSanitize['libraries']) > 0)
            foreach($questionnaireToSanitize['libraries'] as $library)
                array_push($libraries, strip_tags($library));

        $validatedQuestionnaire["libraries"] = $libraries;
        $validatedQuestionnaire["private"] = (strip_tags($questionnaireToSanitize['private'])=="true"||strip_tags($questionnaireToSanitize['private'])=="1"?"1":"0");
        $validatedQuestionnaire["final"] = (strip_tags($questionnaireToSanitize['final'])=="true"||strip_tags($questionnaireToSanitize['final'])=="1"?"1":"0");

        //Validate the list of questions
        $options = array();
        $arrIds = array();
        if(!empty($questionnaireToSanitize["questions"]))
            foreach($questionnaireToSanitize["questions"] as $question) {
                $position = intval(strip_tags($question["position"]));
                $questionId = intval(strip_tags($question["questionId"]));
                if ($questionId != 0)
                    array_push($arrIds, $questionId);
                $optional = intval(strip_tags($question["optional"]));

                if($position <= 0 || $questionId <= 0) {
                    return false;
                    break;
                }

                array_push($options, array("order"=>$position,"questionId"=>$questionId,"optional"=>$optional));
            }
        else
            return false;

        //If the number of questions found in the DB is not equal to the number of questions in the questionnaire,
        //reject it
        $questionsInfo = $this->questionnaireDB->fetchQuestionsByIds($arrIds);
        if(count($questionsInfo) != count($options))
            return false;

        //If any question is private, mark down the questionnaire should be private
        $anyPrivateQuestion = false;
        foreach($questionsInfo as $question) {
            if (intval(strip_tags($question["private"])) == 1) $anyPrivateQuestion = true;
        }

        //If the questionnaire has being set to be public with at least one private question, rejects it
        if ($anyPrivateQuestion && $validatedQuestionnaire["private"] != "1")
            return false;

        //sort, reassign the order of questions and returned the sanitized questionnaire
        self::sortOptions($options);
        $validatedQuestionnaire["questions"] = $options;
        return $validatedQuestionnaire;
    }

    /*
     * Check if a specific questionnaire has being sent already
     * @param   $questionnaireId (integer)
     * @return  true or false (boolean)
     * */
    function isQuestionnaireLocked($questionnaireId) {
        $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnaireId);
        $questionLocked = intval($questionLocked["total"]);
        return $questionLocked;
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
    public function insertQuestionnaire($newQuestionnaire){

        $toInsert = array(FRENCH_LANGUAGE=>$newQuestionnaire['text_FR'], ENGLISH_LANGUAGE=>$newQuestionnaire['text_EN']);
        $title = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $nickname = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);
        $description = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);
        $instruction = $this->questionnaireDB->addToDictionary($toInsert, QUESTIONNAIRE_TABLE);

        $toInsert = array(
            "title"=>$title,
            "nickname"=>$nickname,
            "description"=>$description,
            "instruction"=>$instruction,
            "private"=>$newQuestionnaire["private"],
        );

        $questionnaireId = $this->questionnaireDB->insertQuestionnaire($toInsert);

        $toInsert = array(FRENCH_LANGUAGE=>"", ENGLISH_LANGUAGE=>"");
        $titleSection = $this->questionnaireDB->addToDictionary($toInsert, SECTION_TABLE);
        $instructionSection = $this->questionnaireDB->addToDictionary($toInsert, SECTION_TABLE);


        $toInsert = array(
            "questionnaireId"=>$questionnaireId,
            "title"=>$titleSection,
            "instruction"=>$instructionSection,
        );

        $sectionId = $this->questionnaireDB->insertSection($toInsert);
        foreach($newQuestionnaire["questions"] as &$question) {
            $question["sectionId"] = $sectionId;
        }

        $this->questionnaireDB->insertQuestionsIntoSection($newQuestionnaire["questions"]);

//        $name_EN 				= $questionnaireDetails['name_EN'];
//        $name_FR 				= $questionnaireDetails['name_FR'];
//        $private 				= $questionnaireDetails['private'];
//        $publish 				= $questionnaireDetails['publish'];
//        $created_by 			= $questionnaireDetails['created_by'];
//        $last_updated_by 		= $questionnaireDetails['last_updated_by'];
//        $tags 					= $questionnaireDetails['tags'];
//        $questiongroups 		= $questionnaireDetails['questiongroups'];
//        $filters 				= $questionnaireDetails['filters'];
//        $userSer 				= $questionnaireDetails['user']['id'];
//        $sessionId 				= $questionnaireDetails['user']['sessionid'];
//
//        try {
//            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
//            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
//
//            $sql = "
//				INSERT INTO
//					QuestionnaireControlNew(
//						name_EN,
//						name_FR,
//						private,
//						publish,
//						last_updated_by,
//						created_by,
//						session_id
//					)
//				VALUES(
//					\"$name_EN\",
//					\"$name_FR\",
//					'$private',
//					'$publish',
//					'$userSer',
//					'$created_by',
//					'$sessionId'
//				)
//			";
//
//            $query = $host_db_link->prepare( $sql );
//            $query->execute();
//
//            $questionnaire_id =  $host_db_link->lastInsertId();
//
//            foreach($questiongroups as $group){
//                $group_id = $group['questiongroup_serNum'];
//                $optional = $group['optional'];
//                $position = $group['position'];
//
//                $sql = "
//					INSERT INTO
//						Questionnaire_questiongroup(
//							questionnaire_serNum,
//							questiongroup_serNum,
//							position,
//							optional,
//							last_updated_by,
//							created_by
//						)
//					VALUES(
//						'$questionnaire_id',
//						'$group_id',
//						'$position',
//						'$optional',
//						'$last_updated_by',
//						'$created_by'
//					)
//				";
//                $query = $host_db_link->prepare( $sql );
//                $query->execute();
//
//
//            }
//
//            //add tag
//            foreach($tags as $tag){
//                $sql = "
//					INSERT INTO
//						Questionnaire_tag(
//							questionnaire_serNum,
//							tag_serNum,
//							last_updated_by,
//							created_by
//						)
//					VALUES(
//						'$questionnaire_id',
//						'$tag',
//						'$last_updated_by',
//						'$created_by'
//					)
//				";
//                $query = $host_db_link->prepare( $sql );
//                $query->execute();
//            }
//
//            if ($filters) {
//                foreach ($filters as $filter) {
//
//                    $filterType = $filter['type'];
//                    $filterId   = $filter['id'];
//
//                    $sql = "
//                        INSERT INTO
//                            Filters (
//                                ControlTable,
//                                ControlTableSerNum,
//                                FilterType,
//                                FilterId,
//                                DateAdded
//                            )
//                        VALUES (
//                            'QuestionnaireControl',
//                            '$questionnaire_id',
//                            '$filterType',
//                            \"$filterId\",
//                            NOW()
//                        )
//		    		";
//                    $query = $host_db_link->prepare( $sql );
//                    $query->execute();
//                }
//            }
//
//        } catch( PDOException $e) {
//            return $e->getMessage();
//        }
    }

    /**
     * Mark a questionnaire as deleted. First, it get the last time it was updated, check if the user has the proper
     * authorization, and check if the questionnaire was already sent to a patient. Then it checked if the record was
     * updated in the meantime, and if not, it marks the questionnaire as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionnaireId (ID of the questionnaire)
     * @return array $response : response
     */
    public function deleteQuestionnaire($questionnaireId){
        $questionToDelete = $this->questionnaireDB->getQuestionnaireDetails($questionnaireId);
        $questionToDelete = $questionToDelete[0];
        if ($this->questionnaireDB->getUserId() <= 0 || $questionToDelete["deleted"] == 1 || ($questionToDelete["private"] == 1 && $this->questionnaireDB->getUserId() != $questionToDelete["OAUserId"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "User access denied.");

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTIONNAIRE_TABLE, $questionnaireId);

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTIONNAIRE_TABLE, $questionnaireId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $wasQuestionSent = $this->isQuestionnaireLocked($questionnaireId);

        if ($nobodyUpdated && !$wasQuestionSent) {
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["title"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["nickname"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["description"]);
            $this->questionnaireDB->markAsDeletedInDictionary($questionToDelete["instruction"]);
            $this->questionnaireDB->markAsDeleted(QUESTIONNAIRE_TABLE, $questionnaireId);
            $response['value'] = true; // Success
            $response['message'] = 200;
            return $response;
        }
        else if (!$nobodyUpdated) {
            $response['value'] = false; // conflict error. Somebody already updated the question or record does not exists.
            $response['message'] = 409;
            return $response;
        } else {
            $response['value'] = false; // Question locked.
            $response['message'] = 423;
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

}
?>