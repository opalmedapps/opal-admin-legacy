<?php

/**
 * Post class
 *
 */
class Post {

    /**
     *
     * Updates the post flags in the database
     *
     * @param array $postList : the list of posts
     * @param object $user : the current user in session
     * @return array $response : response
     */    
    public function updatePostFlags( $postList, $user ) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        $userSer    = $user['id'];
        $sessionId  = $user['sessionid'];
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			foreach ($postList as $post) {
				$postDisabled = $post['disabled'];
				$postPublish = $post['publish'];
				$postSer = $post['serial'];
				$sql = "
					UPDATE 
						PostControl 	
					SET 
						PostControl.PublishFlag = $postPublish,
						PostControl.Disabled = $postDisabled,
                        PostControl.LastUpdatedBy = $userSer,
                        PostControl.SessionId = '$sessionId'
					WHERE 
						PostControl.PostControlSerNum = $postSer
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
     * Gets a list of existing posts
     *
     * @return array $postList : the list of existing posts
     */        
	public function getPosts() {
		$postList = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
                SELECT DISTINCT
                    PostControl.PostControlSerNum,
                    PostControl.PostType,
                    PostControl.PostName_EN,
                    PostControl.PostName_FR,
                    PostControl.Body_EN,
                    PostControl.Body_FR,
                    PostControl.PublishFlag,
                    IF(PostControl.PublishDate = '0000-00-00 00:00:00', '--', PostControl.PublishDate),
                    PostControl.Disabled
				FROM 
					PostControl
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$postSer 	            = $data[0];
				$postType	            = $data[1];
				$postName_EN	        = $data[2];
				$postName_FR	        = $data[3];
				$postBody_EN	        = $data[4];
                $postBody_FR            = $data[5];
                $postPublish           	= $data[6];
                $postPublishDate        = $data[7];
                $postDisabled 			= $data[8];
                $postTriggers           = array();

				$sql = "
					SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
					FROM 
						PostControl, 
						Filters 
					WHERE 
                            PostControl.PostControlSerNum 		        = $postSer
                    AND     Filters.ControlTable             = 'PostControl'
                    AND     Filters.ControlTableSerNum       = PostControl.PostControlSerNum
                    AND     Filters.FilterType              != ''
                    AND     Filters.FilterId                != ''
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

					array_push($postTriggers, $triggerArray);
				}

				$postArray = array(
					'name_FR' 		    => $postName_FR, 
					'name_EN' 		    => $postName_EN, 
					'serial' 		    => $postSer, 
                    'type'			    => $postType, 
                    'publish'          	=> $postPublish,
                    'disabled' 			=> $postDisabled,
                    'changed'           => 0,
					'body_EN' 	        => $postBody_EN, 
                    'body_FR' 	        => $postBody_FR,
                    'publish_date'      => $postPublishDate,
					'triggers' 		    => $postTriggers
				);

				array_push($postList, $postArray);
			}
			return $postList;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $postList;
		}
	}

    /**
     *
     * Gets details on a particular post
     *
     * @param integer $postSer : the post serial number
     * @return array $postDetails : the post details
     */    			
    public function getPostDetails ($postSer) {

		$postDetails = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT 
                    PostControl.PostType,
                    PostControl.PostName_EN,
                    PostControl.PostName_FR,
                    PostControl.Body_EN,
                    PostControl.Body_FR,
                    PostControl.PublishFlag,
                    IF(PostControl.PublishDate = '0000-00-00 00:00:00', NULL, PostControl.PublishDate),
                    PostControl.Disabled
				FROM 
					PostControl 
				WHERE 
					PostControl.PostControlSerNum = $postSer
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$postType	        = $data[0];
			$postName_EN	    = $data[1];
			$postName_FR	    = $data[2];
			$postBody_EN	    = $data[3];
            $postBody_FR	    = $data[4];
            $postPublish       	= $data[5];
            $postPublishDate    = $data[6];
            $postDisabled		= $data[7];
			$postTriggers	    = array();

			$sql = "
				SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
					FROM 
						PostControl, 
						Filters 
					WHERE 
                            PostControl.PostControlSerNum   = $postSer 
                    AND     Filters.ControlTable            = 'PostControl'
                    AND     Filters.ControlTableSerNum      = PostControl.PostControlSerNum
                    AND     Filters.FilterType              != ''
                    AND     Filters.FilterId                != ''

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

					array_push($postTriggers, $triggerArray);


            }

			$postDetails = array(
	            'name_FR' 		    => $postName_FR, 
				'name_EN' 		    => $postName_EN, 
				'serial' 		    => $postSer, 
                'type'			    => $postType, 
                'publish'           => $postPublish,
                'disabled'			=> $postDisabled,
				'body_EN' 	        => $postBody_EN, 
                'body_FR' 	        => $postBody_FR,
                'publish_date'      => $postPublishDate,
				'triggers' 		    => $postTriggers
            );
		
			return $postDetails;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $postDetails;
		}
	}

    /**
     *
     * Inserts a post into the database
     *
     * @param array $postDetails : the post details
	 * @return void
     */    
	public function insertPost( $postDetails ) {

		$postName_EN 	= $postDetails['name_EN'];
		$postName_FR 	= $postDetails['name_FR'];
		$postBody_EN	= $postDetails['body_EN'];
		$postBody_FR	= $postDetails['body_FR'];
        $postType	    = $postDetails['type'];
        $postPublishDate= $postDetails['publish_date'];
		$postTriggers	= $postDetails['triggers'];
        $userSer        = $postDetails['user']['id'];
        $sessionId      = $postDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
                    PostControl (
                        PostName_EN,
                        PostName_FR,
                        Body_EN,
                        Body_FR,
                        PostType,
                        PublishDate,
                        DateAdded,
                        LastUpdatedBy,
                        SessionId
					) 
				VALUES (
					\"$postName_EN\", 
					\"$postName_FR\",
					\"$postBody_EN\", 
					\"$postBody_FR\", 
                    '$postType', 
                    '$postPublishDate',
                    NOW(),
                    '$userSer',
                    '$sessionId'
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$postSer = $host_db_link->lastInsertId();

            if (!empty($postTriggers)) {
    			foreach ($postTriggers as $filter) {

                    $filterType = $filter['type'];
                    $filterId   = $filter['id'];

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
                            'PostControl',
                            '$postSer',
                            '$filterType',
                            \"$filterId\",
                            NOW(),
                            '$userSer',
                            '$sessionId'
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
     * Deletes a post from the database
     *
     * @param integer $postSer : the post serial number
     * @param object $user : the current user in session
     * @return array : response
     */        
    public function deletePost( $postSer, $user ) {

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
					PostControl 
				WHERE 
					PostControl.PostControlSerNum = $postSer
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$sql = "
                DELETE FROM
                    Filters
                WHERE
                    Filters.ControlTableSerNum   = $postSer
                AND Filters.ControlTable         = 'PostControl'
			";
			
			$query = $host_db_link->prepare( $sql );
			$query->execute();

            $sql = "
                UPDATE PostControlMH
                SET 
                    PostControlMH.LastUpdatedBy = '$userSer',
                    PostControlMH.SessionId = '$sessionId'
                WHERE
                    PostControlMH.PostControlSerNum = $postSer
                ORDER BY PostControlMH.RevSerNum DESC 
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
     * Updates a post's details in the database
     *
     * @param array $postDetails : the post details
     * @return array : response
     */        
    public function updatePost( $postDetails ) {

		$postName_EN 	    = $postDetails['name_EN'];
		$postName_FR 	    = $postDetails['name_FR'];
		$postBody_EN	    = $postDetails['body_EN'];
		$postBody_FR	    = $postDetails['body_FR'];
        $postSer	        = $postDetails['serial'];
        $postPublishDate    = $postDetails['publish_date'];
		$postTriggers	    = $postDetails['triggers'];
        $userSer            = $postDetails['user']['id'];
        $sessionId          = $postDetails['user']['sessionid'];

        $existingTriggers	= array();

        $detailsUpdated     = $postDetails['details_updated'];
        $triggersUpdated     = $postDetails['triggers_updated'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            if($detailsUpdated) {

    			$sql = "
    				UPDATE 
    					PostControl 
    				SET 
    					PostControl.PostName_EN 		= \"$postName_EN\", 
    					PostControl.PostName_FR 		= \"$postName_FR\", 
    					PostControl.Body_EN	            = \"$postBody_EN\",
                        PostControl.Body_FR	            = \"$postBody_FR\",
                        PostControl.PublishDate         = '$postPublishDate',
                        PostControl.LastUpdatedBy       = '$userSer',
                        PostControl.SessionId           = '$sessionId'
    				WHERE 
    					PostControl.PostControlSerNum = $postSer
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
                        Filters.ControlTableSerNum       = $postSer
                    AND Filters.ControlTable             = 'PostControl'
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
                    // If old triggers not in new, remove from DB
    	    		foreach ($existingTriggers as $existingTrigger) {
                        $id     = $existingTrigger['id'];
                        $type   = $existingTrigger['type'];
                        if (!$this->nestedSearch($id, $type, $postTriggers)) {
    					    $sql = "
                                DELETE FROM 
    	    						Filters
    		    				WHERE
                                    Filters.FilterId            = \"$id\"
                                AND Filters.FilterType          = '$type'
                                AND Filters.ControlTableSerNum   = $postSer
                                AND Filters.ControlTable         = 'PostControl'
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
                                AND FiltersMH.ControlTableSerNum    = $postSer
                                AND FiltersMH.ControlTable          = 'PostControl'
                                ORDER BY FiltersMH.DateAdded DESC 
                                LIMIT 1
                            ";
                            $query = $host_db_link->prepare( $sql );
                            $query->execute();

    			    	}
        			}   
                }
                if (!empty($postTriggers)) {
                    // If new triggers, insert into DB
        			foreach ($postTriggers as $trigger) {
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
                                    'PostControl',
                                    '$postSer',
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

            $response['value'] = 1;
            return $response;
        } catch( PDOException $e) {
            $response['message'] = $e->getMessage();
			return $response;
		}
    }

    /**
     *
     * Gets chart logs of a post or posts
     *
     * @param integer $serial : the post serial number
     * @param string $type : the post type
     * @return array $aliasLogs : the post logs for highcharts
     */
    public function getPostChartLogs ($serial, $type) {
        $postLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            // get all logs for all aliases
            if (!$serial and !$type) {
                $postSeries = array();

                /* ANNOUNCEMENTS */
                $sql = "
                    SELECT DISTINCT
                        anmh.CronLogSerNum,
                        COUNT(anmh.CronLogSerNum),
                        cl.CronDateTime,
                        pc.PostName_EN
                    FROM
                        AnnouncementMH anmh,
                        CronLog cl,
                        PostControl pc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = anmh.CronLogSerNum
                    AND anmh.CronLogSerNum IS NOT NULL
                    AND anmh.PostControlSerNum = pc.PostControlSerNum
                    GROUP BY
                        anmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $postDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($postSeries[$seriesName])) {
                        $postSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($postSeries[$seriesName]['data'], $postDetail);
                }

                /* TX TEAM MESSAGES */
                $sql = "
                    SELECT DISTINCT
                        ttmmh.CronLogSerNum,
                        COUNT(ttmmh.CronLogSerNum),
                        cl.CronDateTime,
                        pc.PostName_EN
                    FROM
                        TxTeamMessageMH ttmmh,
                        CronLog cl,
                        PostControl pc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = ttmmh.CronLogSerNum
                    AND ttmmh.CronLogSerNum IS NOT NULL
                    AND ttmmh.PostControlSerNum = pc.PostControlSerNum
                    GROUP BY
                        ttmmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $postDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($postSeries[$seriesName])) {
                        $postSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($postSeries[$seriesName]['data'], $postDetail);
                }

                /* P4P */
                $sql = "
                    SELECT DISTINCT
                        pfpmh.CronLogSerNum,
                        COUNT(pfpmh.CronLogSerNum),
                        cl.CronDateTime,
                        pc.PostName_EN
                    FROM
                        PatientsForPatientsMH pfpmh,
                        CronLog cl,
                        PostControl pc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = pfpmh.CronLogSerNum
                    AND pfpmh.CronLogSerNum IS NOT NULL
                    AND pfpmh.PostControlSerNum = pc.PostControlSerNum
                    GROUP BY
                        pfpmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $postDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($postSeries[$seriesName])) {
                        $postSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($postSeries[$seriesName]['data'], $postDetail);
                }

                foreach ($postSeries as $seriesName => $series) {
                    array_push($postLogs, $series);
                }
            }
            // get logs for specific alias
            else {
                if ($type == 'Announcement') {

                    $sql = "
                        SELECT DISTINCT
                            anmh.CronLogSerNum,
                            COUNT(anmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            AnnouncementMH anmh,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = anmh.CronLogSerNum
                        AND anmh.CronLogSerNum IS NOT NULL
                        AND anmh.PostControlSerNum = $serial
                        GROUP BY
                            anmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY 
                            cl.CronDateTime ASC 
                    ";

                }

                else if ($type == 'Treatment Team Message') {
                    $sql = "
                        SELECT DISTINCT
                            ttmmh.CronLogSerNum,
                            COUNT(ttmmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            TxTeamMessageMH ttmmh,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = ttmmh.CronLogSerNum
                        AND ttmmh.CronLogSerNum IS NOT NULL
                        AND ttmmh.PostControlSerNum = $serial
                        GROUP BY
                            ttmmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY 
                            cl.CronDateTime ASC 
                    ";
                }

                else if ($type == 'Patients for Patients') {
                    $sql = "
                        SELECT DISTINCT
                            pfpmh.CronLogSerNum,
                            COUNT(pfpmh.CronLogSerNum),
                            cl.CronDateTime
                        FROM
                            PatientsForPatientsMH pfpmh,
                            CronLog cl
                        WHERE
                            cl.CronStatus = 'Started'
                        AND cl.CronLogSerNum = pfpmh.CronLogSerNum
                        AND pfpmh.CronLogSerNum IS NOT NULL
                        AND pfpmh.PostControlSerNum = $serial
                        GROUP BY
                            pfpmh.CronLogSerNum,
                            cl.CronDateTime
                        ORDER BY 
                            cl.CronDateTime ASC 
                    ";
                }
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $postSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $type;
                    $postDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($postSeries[$seriesName])) {
                        $postSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($postSeries[$seriesName]['data'], $postDetail);
                }

                foreach ($postSeries as $seriesName => $series) {
                    array_push($postLogs, $series);
                }
            }
            return $postLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $postLogs;
        }
    }

    /**
     *
     * Gets list logs of posts during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @param string $type : the post type
     * @return array $postLogs : the post logs for table view
     */
    public function getPostListLogs ($serials, $type) {
        $postLogs = array();
        $serials = implode(',', $serials);
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "";
            if (!$type) {
                $sql = "
                    SELECT DISTINCT
                        pc.PostName_EN,
                        anmh.AnnouncementRevSerNum,
                        anmh.CronLogSerNum,
                        anmh.PatientSerNum,
                        anmh.DateAdded,
                        anmh.ReadStatus,
                        anmh.ModificationAction,
                        pc.PostType
                    FROM
                        AnnouncementMH anmh,
                        PostControl pc 
                    WHERE
                        pc.PostControlSerNum = anmh.PostControlSerNum
                    AND anmh.CronLogSerNum IN ($serials)
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array(
                        'post_control_name'     => $data[0],
                        'revision'              => $data[1],
                        'cron_serial'           => $data[2],
                        'patient_serial'        => $data[3],
                        'date_added'            => $data[4],
                        'read_status'           => $data[5],
                        'mod_action'            => $data[6],
                        'type'                  => $data[7]
                    );
                    array_push($postLogs, $logDetails);
                }

                $sql = "
                    SELECT DISTINCT
                        pc.PostName_EN,
                        ttmmh.TxTeamMessageRevSerNum,
                        ttmmh.CronLogSerNum,
                        ttmmh.PatientSerNum,
                        ttmmh.DateAdded,
                        ttmmh.ReadStatus,
                        ttmmh.ModificationAction,
                        pc.PostType
                    FROM
                        TxTeamMessageMH ttmmh,
                        PostControl pc 
                    WHERE
                        pc.PostControlSerNum = ttmmh.PostControlSerNum
                    AND ttmmh.CronLogSerNum IN ($serials)
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array(
                        'post_control_name'     => $data[0],
                        'revision'              => $data[1],
                        'cron_serial'           => $data[2],
                        'patient_serial'        => $data[3],
                        'date_added'            => $data[4],
                        'read_status'           => $data[5],
                        'mod_action'            => $data[6],
                        'type'                  => $data[7]
                    );
                    array_push($postLogs, $logDetails);
                }

                $sql = "
                    SELECT DISTINCT
                        pc.PostName_EN,
                        pfpmh.PatientsForPatientsRevSerNum,
                        pfpmh.CronLogSerNum,
                        pfpmh.PatientSerNum,
                        pfpmh.DateAdded,
                        pfpmh.ReadStatus,
                        pfpmh.ModificationAction
                    FROM
                        PatientsForPatientsMH pfpmh,
                        PostControl pc 
                    WHERE
                        pc.PostControlSerNum = pfpmh.PostControlSerNum
                    AND pfpmh.CronLogSerNum IN ($serials)
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array(
                        'post_control_name'     => $data[0],
                        'revision'              => $data[1],
                        'cron_serial'           => $data[2],
                        'patient_serial'        => $data[3],
                        'date_added'            => $data[4],
                        'read_status'           => $data[5],
                        'mod_action'            => $data[6],
                        'type'                  => $data[7]
                    );
                    array_push($postLogs, $logDetails);
                }

            }
            else {
                if ($type == 'Announcement') {
                    $sql = "
                        SELECT DISTINCT
                            pc.PostName_EN,
                            anmh.AnnouncementRevSerNum,
                            anmh.CronLogSerNum,
                            anmh.PatientSerNum,
                            anmh.DateAdded,
                            anmh.ReadStatus,
                            anmh.ModificationAction
                        FROM
                            AnnouncementMH anmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = anmh.PostControlSerNum
                        AND anmh.CronLogSerNum IN ($serials)
                    ";
                } 
                else if ($type == "Treatment Team Message") {
                    $sql = "
                        SELECT DISTINCT
                            pc.PostName_EN,
                            ttmmh.TxTeamMessageRevSerNum,
                            ttmmh.CronLogSerNum,
                            ttmmh.PatientSerNum,
                            ttmmh.DateAdded,
                            ttmmh.ReadStatus,
                            ttmmh.ModificationAction
                        FROM
                            TxTeamMessageMH ttmmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = ttmmh.PostControlSerNum
                        AND ttmmh.CronLogSerNum IN ($serials)
                    ";
                }
                else if ($type == "Patients for Patients") {
                    $sql = "
                        SELECT DISTINCT
                            pc.PostName_EN,
                            pfpmh.PatientsForPatientsRevSerNum,
                            pfpmh.CronLogSerNum,
                            pfpmh.PatientSerNum,
                            pfpmh.DateAdded,
                            pfpmh.ReadStatus,
                            pfpmh.ModificationAction
                        FROM
                            PatientsForPatientsMH pfpmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = pfpmh.PostControlSerNum
                        AND pfpmh.CronLogSerNum IN ($serials)
                    ";
                }
                
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $logDetails = array(
                        'post_control_name'     => $data[0],
                        'revision'              => $data[1],
                        'cron_serial'           => $data[2],
                        'patient_serial'        => $data[3],
                        'date_added'            => $data[4],
                        'read_status'           => $data[5],
                        'mod_action'            => $data[6]
                    );
                    array_push($postLogs, $logDetails);
                }
            }

            return $postLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $postLogs;
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
