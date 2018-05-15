<?php

/**
 * TestResult class
 *
 */
class TestResult {

    /**
     *
     * Updates the publish flag(s) in the database
     *
     * @param array $testResultList : a list of test results
     * @param object $user : the current user in session
     * @return array $response : response
     */
    public function updatePublishFlags( $testResultList, $user ) {
        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            foreach ($testResultList as $testResult) {
                $publishFlag = $testResult['publish'];
                $serial = $testResult['serial'];
                $sql = "
                    UPDATE
                        TestResultControl
                    SET
                        TestResultControl.PublishFlag = $publishFlag,
                        TestResultControl.LastUpdatedBy = $userSer,
                        TestResultControl.SessionId = '$sessionId'
                    WHERE
                        TestResultControl.TestResultControlSerNum = $serial
                ";
	            $query = $host_db_link->prepare( $sql );
				$query->execute();
            }

            $this->sanitizeEmptyTestResults($user);

            $response['value'] = 1; // Success
            return $response;
		} catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

    /**
     *
     * Gets details on a particular test result
     *
     * @param integer $serial : the serial number of the test result
     * @return array $testResultDetails : the test result details
     */
    public function getTestResultDetails ($serial) {
        $testResultDetails = array();
 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    tr.Name_EN,
                    tr.Name_FR,
                    tr.Description_EN,
                    tr.Description_FR,
                    tr.Group_EN,
                    tr.Group_FR,
                    tr.EducationalMaterialControlSerNum
                FROM
                    TestResultControl tr
                WHERE
                    tr.TestResultControlSerNum = $serial
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $name_EN            = $data[0];
            $name_FR            = $data[1];
            $description_EN     = $data[2];
            $description_FR     = $data[3];
            $group_EN           = $data[4];
            $group_FR           = $data[5];
            $eduMatSer          = $data[6];
            $tests              = array();
            $additionalLinks    = array();

            $eduMat         = "";

            $sql = "
                SELECT DISTINCT
                    tre.ExpressionName
                FROM
                    TestResultExpression tre
                WHERE
                    tre.TestResultControlSerNum = $serial
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    	$query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $testArray = array(
                    'name'  => $data[0],
                    'id'    => $data[0],
                    'added' => 1
                );

                array_push($tests, $testArray);
            }

            if ($eduMatSer != 0) {
                $eduMatObj = new EduMaterial();
                $eduMat = $eduMatObj->getEducationalMaterialDetails($eduMatSer);
            }

            $sql = "
                SELECT DISTINCT
                    tral.TestResultAdditionalLinksSerNum,
                    tral.Name_EN,
                    tral.Name_FR,
                    tral.URL_EN,
                    tral.URL_FR
                FROM
                    TestResultAdditionalLinks tral
                WHERE
                    tral.TestResultControlSerNum = $serial
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $linkSer        = $data[0];
                $linkName_EN    = $data[1];
                $linkName_FR    = $data[2];
                $linkURL_EN     = urldecode($data[3]);
                $linkURL_FR     = urldecode($data[4]);

                $linkDetails = array (
                    'serial'        => $linkSer,
                    'name_EN'       => $linkName_EN,
                    'name_FR'       => $linkName_FR,
                    'url_EN'        => $linkURL_EN,
                    'url_FR'        => $linkURL_FR
                );
                array_push($additionalLinks, $linkDetails);
            }

            $testResultDetails = array(
                'name_EN'           => $name_EN,
                'name_FR'           => $name_FR,
                'description_EN'    => $description_EN,
                'description_FR'    => $description_FR,
                'group_EN'          => $group_EN,
                'group_FR'          => $group_FR,
                'serial'            => $serial,
                'eduMatSer'         => $eduMatSer,
                'eduMat'            => $eduMat,
                'count'             => count($tests),
                'tests'             => $tests,
                'additional_links'  => $additionalLinks
            );
            return $testResultDetails;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $testResultDetails;
		}
	}

    /**
     *
     * Gets a list of test result groups
     *
     * @return array $groups : the list of existing test groups
     */
    public function getTestResultGroups () {

        $groups = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    trc.Group_EN,
                    trc.Group_FR
                FROM
                    TestResultControl trc
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $groupDetails = array(
                    'EN'    => $data[0],
                    'FR'    => $data[1]
                );
                array_push($groups, $groupDetails);
            }

            return $groups;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $groups;
		}
    }

    /**
     *
     * Gets a list of test result names from a source database
     *
     * @return array $testNames : the list of test names
     */
    public function getTestNames() {
        $testNames = array();
        $databaseObj = new Database();

        try {

            // get already assigned expressions from our database
            $assignedTests = $this->getAssignedTests();

            // ***********************************
            // ARIA
            // ***********************************
            $sourceDBSer = 1;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "
                    SELECT DISTINCT
                        tr.comp_name
                    FROM
                        varianenm.dbo.test_result tr
                ";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $testName = $data[0];
                    $testArray = array(
                        'name'      => $testName,
                        'id'        => $testName,
                        'added'     => 0,
                        'assigned'  => null
                    );
                    $assignedTest = $this->assignedSearch($testName, $assignedTests);
                    if ($assignedTest) {
                        $testArray['added'] = 0;
                        $testArray['assigned'] = $assignedTest;
                    }
                    array_push($testNames, $testArray);
                }

            }

            // ***********************************
            // WaitRoomManagement
            // ***********************************
            $sourceDBSer = 2;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    // Set appropriate test result data here from query

                    //array_push($testNames, $testArray); // Uncomment for use
                }

            }

            // ***********************************
            // Mosaiq
            // ***********************************
            $sourceDBSer = 3;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    // Set appropriate test result data here from query

                    //array_push($testNames, $testArray); // Uncomment for use
                }

            }


            return $testNames;
  	  	} catch (PDOException $e) {
            echo $e->getMessage();
            return $testNames;
		}
    }

    /**
     *
     * Gets a list of already assigned tests in our database
     *
     * @return array $tests : the list of tests
     */
    public function getAssignedTests () {
        $tests = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT 
                    tre.ExpressionName,
                    trc.Name_EN
                FROM 
                    TestResultExpression tre,
                    TestResultControl trc
                WHERE
                    trc.TestResultControlSerNum = tre.TestResultControlSerNum
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $testResultDetails = array(
                    'id'     => $data[0],
                    'name_EN'       => "$data[1]"
                );
                array_push($tests, $testResultDetails);
            }

            return $tests;

        } catch (PDOException $e) {
            echo $e->getMessage();
            return $tests;
        }
    }

    /**
     *
     * Inserts a test result into the database
     *
     * @param array $testResultDetails : the test result details
	 * @return void
     */
    public function insertTestResult ($testResultDetails) {

        $name_EN            = $testResultDetails['name_EN'];
        $name_FR            = $testResultDetails['name_FR'];
        $description_EN     = $testResultDetails['description_EN'];
        $description_FR     = $testResultDetails['description_FR'];
        $group_EN           = $testResultDetails['group_EN'];
        $group_FR           = $testResultDetails['group_FR'];
        $tests              = $testResultDetails['tests'];
        $additionalLinks    = $testResultDetails['additional_links'];
        $userSer            = $testResultDetails['user']['id'];
        $sessionId          = $testResultDetails['user']['sessionid'];
        $eduMatSer          = 'NULL';
        if ( is_array($testResultDetails['edumat']) && isset($testResultDetails['edumat']['serial']) ) {
            $eduMatSer = $testResultDetails['edumat']['serial'];
        }

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                INSERT INTO
                    TestResultControl (
                        Name_EN,
                        Name_FR,
                        Description_EN,
                        Description_FR,
                        Group_EN,
                        Group_FR,
                        EducationalMaterialControlSerNum,
                        DateAdded,
                        LastPublished,
                        LastUpdatedBy,
                        SessionId
                    )
                VALUES (
                    \"$name_EN\",
                    \"$name_FR\",
                    \"$description_EN\",
                    \"$description_FR\",
                    \"$group_EN\",
                    \"$group_FR\",
                    $eduMatSer,
                    NOW(),
                    NOW(),
                    $userSer,
                    '$sessionId'
                )
            ";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

            $testResultSer = $host_db_link->lastInsertId();

            foreach ($tests as $test) {

                $name   = $test['name'];

                $sql = "
                    INSERT INTO
                        TestResultExpression (
                            TestResultControlSerNum,
                            ExpressionName,
                            DateAdded,
                            LastUpdatedBy,
                            SessionId
                        )
                    VALUES (
                        '$testResultSer',
                        \"$name\",
                        NOW(),
                        '$userSer',
                        '$sessionId'
                    )
                    ON DUPLICATE KEY UPDATE 
                        TestResultControlSerNum = '$testResultSer',
                        LastUpdatedBy = '$userSer',
                        SessionId = '$sessionId'
                ";
	            $query = $host_db_link->prepare( $sql );
				$query->execute();
            }

            if ($additionalLinks) {
                foreach ($additionalLinks as $link) {
                    
                    $linkName_EN        = $link['name_EN'];
                    $linkName_FR        = $link['name_FR'];
                    $linkURL_EN         = $link['url_EN'];
                    $linkURL_FR         = $link['url_FR'];

                    $sql = "
                        INSERT INTO 
                            TestResultAdditionalLinks (
                                TestResultControlSerNum,
                                Name_EN,
                                Name_FR,
                                URL_EN,
                                URL_FR,
                                DateAdded
                            )
                        VALUES (
                            '$testResultSer',
                            \"$linkName_EN\",
                            \"$linkName_FR\",
                            \"$linkURL_EN\",
                            \"$linkURL_FR\",
                            NOW()
                        )
                    ";
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                }
            }

            $this->sanitizeEmptyTestResults($testResultDetails['user']);

        } catch( PDOException $e) {
			return $e->getMessage();
		}

    }

    /**
     *
     * Gets a list of existing test results in the database
     *
     * @return array $testResultList : the list of existing test results 
     */
    public function getExistingTestResults () {

        $testResultList = array();

 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    tr.TestResultControlSerNum,
                    tr.Name_EN,
                    tr.Name_FR,
                    tr.Description_EN,
                    tr.Description_FR,
                    tr.Group_EN,
                    tr.Group_FR,
                    tr.PublishFlag,
                    tr.EducationalMaterialControlSerNum
                FROM
                    TestResultControl tr
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $testResultSer          = $data[0];
                $name_EN                = $data[1];
                $name_FR                = $data[2];
                $description_EN         = $data[3];
                $description_FR         = $data[4];
                $group_EN               = $data[5];
                $group_FR               = $data[6];
                $publishFlag            = $data[7];
                $eduMatSer              = $data[8];
                $eduMat                 = "";
                $tests                  = array();
                $additionalLinks        = array();

                $sql = "
                    SELECT DISTINCT
                        tre.ExpressionName
                    FROM
                        TestResultExpression tre
                    WHERE
                        tre.TestResultControlSerNum = $testResultSer
                ";

                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$secondQuery->execute();

				while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $testNameArray = array(
                        'name'  => $secondData[0],
                        'id'    => $secondData[0],
                        'added' => 1
                    );

                    array_push($tests, $testNameArray);
                }

                if ($eduMatSer != 0) {
                    $eduMatObj = new EduMaterial();
                    $eduMat = $eduMatObj->getEducationalMaterialDetails($eduMatSer);
                }

                $sql = "
                    SELECT DISTINCT
                        tral.TestResultAdditionalLinksSerNum,
                        tral.Name_EN,
                        tral.Name_FR,
                        tral.URL_EN,
                        tral.URL_FR
                    FROM
                        TestResultAdditionalLinks tral
                    WHERE
                        tral.TestResultControlSerNum = $testResultSer
                ";
                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $secondQuery->execute();

                while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $linkSer        = $secondData[0];
                    $linkName_EN    = $secondData[1];
                    $linkName_FR    = $secondData[2];
                    $linkURL_EN     = urldecode($secondData[3]);
                    $linkURL_FR     = urldecode($secondData[4]);

                    $linkDetails = array (
                        'serial'        => $linkSer,
                        'name_EN'       => $linkName_EN,
                        'name_FR'       => $linkName_FR,
                        'url_EN'        => $linkURL_EN,
                        'url_FR'        => $linkURL_FR
                    );
                    array_push($additionalLinks, $linkDetails);
                }

                $testArray = array(
                    'name_EN'           => $name_EN,
                    'name_FR'           => $name_FR,
                    'serial'            => $testResultSer,
                    'description_EN'    => $description_EN,
                    'description_FR'    => $description_FR,
                    'group_EN'          => $group_EN,
                    'group_FR'          => $group_FR,
                    'publish'           => $publishFlag,
                    'changed'           => 0,
                    'eduMatSer'         => $eduMatSer,
                    'eduMat'            => $eduMat,
                    'tests'             => $tests,
                    'count'             => count($tests),
                    'additional_links'  => $additionalLinks
                );

                array_push($testResultList, $testArray);
            }
            return $testResultList;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $testResultList;
		}
	}

    /**
     *
     * Updates test result details in the database
     *
     * @param array $testResultDetails : the test result details
     * @return array : response
     */
    public function updateTestResult ($testResultDetails) {

        $name_EN            = $testResultDetails['name_EN'];
        $name_FR            = $testResultDetails['name_FR'];
        $description_EN     = $testResultDetails['description_EN'];
        $description_FR     = $testResultDetails['description_FR'];
        $group_EN           = $testResultDetails['group_EN'];
        $group_FR           = $testResultDetails['group_FR'];
        $serial             = $testResultDetails['serial'];
        $tests              = $testResultDetails['tests'];
        $eduMatSer          = $testResultDetails['edumatser'] ? $testResultDetails['edumatser'] : 'NULL';
        $additionalLinks    = $testResultDetails['additional_links'];
        $userSer            = $testResultDetails['user']['id'];
        $sessionId          = $testResultDetails['user']['sessionid'];

        $existingTests      = array();

        $detailsUpdated     = $testResultDetails['details_updated'];
        $testNamesUpdated   = $testResultDetails['test_names_updated'];
        $additionalLinksUpdated     = $testResultDetails['additional_links_updated'];

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
                        TestResultControl
                    SET
                        TestResultControl.Name_EN           = \"$name_EN\",
                        TestResultControl.Name_FR           = \"$name_FR\",
                        TestResultControl.Description_EN    = \"$description_EN\",
                        TestResultControl.Description_FR    = \"$description_FR\",
                        TestResultControl.Group_EN          = \"$group_EN\",
                        TestResultControl.Group_FR          = \"$group_FR\",
                        TestResultControl.EducationalMaterialControlSerNum = $eduMatSer,
                        TestResultControl.LastUpdatedBy     = $userSer,
                        TestResultControl.SessionId         = '$sessionId'
                    WHERE
                        TestResultControl.TestResultControlSerNum = $serial
                ";

                $query = $host_db_link->prepare( $sql );
    			$query->execute();
            }

            if (testNamesUpdated) {

                $sql = "
                    SELECT DISTINCT
                        tre.ExpressionName
                    FROM
                        TestResultExpression tre
                    WHERE
                        tre.TestResultControlSerNum = $serial
                ";
    			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    			$query->execute();

    			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    array_push($existingTests, $data[0]);
                }

                // If old test names not in new test names, delete from database
                foreach ($existingTests as $existingTestName) {
                    if (!in_array($existingTestName, $tests)) {
                        $sql = "
                            DELETE FROM
                                TestResultExpression
                            WHERE
                                TestResultExpression.ExpressionName = \"$existingTestName\"
                            AND TestResultExpression.TestResultControlSerNum = $serial
                        ";

    					$query = $host_db_link->prepare( $sql );
    					$query->execute();

                        $sql = "
                            UPDATE TestResultExpressionMH
                            SET 
                                TestResultExpressionMH.LastUpdatedBy = '$userSer',
                                TestResultExpressionMH.SessionId = '$sessionId'
                            WHERE
                                TestResultExpressionMH.ExpressionName = \"$existingTestName\"
                            ORDER BY TestResultExpressionMH.RevSerNum DESC 
                            LIMIT 1
                        ";
                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
    				}
    			}

                // If new test names, insert into database
                foreach ($tests as $test) {
                    if(!in_array($test, $existingTests)) {
                        $sql = "
                            INSERT INTO
                                TestResultExpression (
                                    TestResultControlSerNum,
                                    ExpressionName,
                                    LastUpdatedBy,
                                    SessionId
                                )
                            VALUES (
                                '$serial',
                                \"$test\",
                                '$userSer',
                                '$sessionId'
                            )
                            ON DUPLICATE KEY UPDATE
                                TestResultControlSerNum = '$serial',
                                LastUpdatedBy = '$userSer',
                                SessionId = '$sessionId'
                        ";

    	                $query = $host_db_link->prepare( $sql );
    					$query->execute();
    				}
    			}
            }

            if ($additionalLinksUpdated) {

                // clear existing links
                $sql = "
                    DELETE FROM 
                        TestResultAdditionalLinks
                    WHERE
                        TestResultAdditionalLinks.TestResultControlSerNum = '$serial'
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();

                if ($additionalLinks) {
                    // add new links
                    foreach ($additionalLinks as $link) {
                        
                        $linkName_EN        = $link['name_EN'];
                        $linkName_FR        = $link['name_FR'];
                        $linkURL_EN         = $link['url_EN'];
                        $linkURL_FR         = $link['url_FR'];

                        $sql = "
                            INSERT INTO 
                                TestResultAdditionalLinks (
                                    TestResultControlSerNum,
                                    Name_EN,
                                    Name_FR,
                                    URL_EN,
                                    URL_FR,
                                    DateAdded
                                )
                            VALUES (
                                '$serial',
                                \"$linkName_EN\",
                                \"$linkName_FR\",
                                \"$linkURL_EN\",
                                \"$linkURL_FR\",
                                NOW()
                            )
                        ";
                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
                    }
                }
            }

            $this->sanitizeEmptyTestResults($testResultDetails['user']);

            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

    /**
     *
     * Removes a test result from the database
     *
     * @param integer $testResultSer : the serial number of the test result
     * @param object $user : the current user in session
     * @return array $response : response
     */
    public function deleteTestResult ($testResultSer, $user) {

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
                    TestResultExpression
                WHERE
                    TestResultExpression.TestResultControlSerNum = $testResultSer
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                DELETE FROM
                    TestResultAdditionalLinks
                WHERE
                    TestResultAdditionalLinks.TestResultControlSerNum = $testResultSer
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                DELETE FROM
                    TestResultControl
                WHERE
                    TestResultControl.TestResultControlSerNum = $testResultSer
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE TestResultControlMH
                SET 
                    TestResultControlMH.LastUpdatedBy = '$userSer',
                    TestResultControlMH.SessionId = '$sessionId'
                WHERE
                    TestResultControlMH.TestResultControlSerNum = $testResultSer
                ORDER BY TestResultControlMH.RevSerNum DESC 
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
     * Removes publish flag for test results without assigned tests
     *
     * @param object $user : the session user
     * @return void
     */
    public function sanitizeEmptyTestResults($user) {
        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                UPDATE 
                    TestResultControl 
                LEFT JOIN 
                    TestResultExpression 
                ON  TestResultControl.TestResultControlSerNum = TestResultExpression.TestResultControlSerNum
                SET 
                    TestResultControl.PublishFlag       = 0, 
                    TestResultControl.LastUpdatedBy     = $userSer,
                    TestResultControl.SessionId         = '$sessionId'
                WHERE  
                    TestResultExpression.TestResultControlSerNum IS NULL 
            ";

            $query = $host_db_link->prepare( $sql );
            $query->execute();
            return;
        } catch( PDOException $e) {
            return $e->getMessage(); // Fail
        }
    }

    /**
     *
     * Gets chart logs of a test result or results
     *
     * @param integer $serial : the test result serial number
     * @return array $testResultLogs : the test result logs for highcharts
     */
    public function getTestResultChartLogs ($serial) {
        $testResultLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            // get all logs for all test results
            if (!$serial) {

                $sql = "
                    SELECT DISTINCT
                        trmh.CronLogSerNum,
                        COUNT(trmh.CronLogSerNum),
                        cl.CronDateTime,
                        trc.Name_EN
                    FROM
                        TestResultMH trmh,
                        TestResultExpression tre,
                        CronLog cl,
                        TestResultControl trc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = trmh.CronLogSerNum
                    AND trmh.CronLogSerNum IS NOT NULL
                    AND trmh.TestResultExpressionSerNum = tre.TestResultExpressionSerNum
                    AND tre.TestResultControlSerNum = trc.TestResultControlSerNum
                    GROUP BY
                        trmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $testResultSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $testResultDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($testResultSeries[$seriesName])) {
                        $testResultSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($testResultSeries[$seriesName]['data'], $testResultDetail);
                }

                foreach ($testResultSeries as $seriesName => $series) {
                    array_push($testResultLogs, $series);
                }

            }
            // get logs for specific test results
            else {
                $sql = "
                    SELECT DISTINCT
                        trmh.CronLogSerNum,
                        COUNT(trmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        TestResultMH trmh,
                        TestResultExpression tre,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = trmh.CronLogSerNum
                    AND trmh.CronLogSerNum IS NOT NULL
                    AND trmh.TestResultExpressionSerNum = tre.TestResultExpressionSerNum
                    AND tre.TestResultControlSerNum = $serial
                    GROUP BY
                        trmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $testResultSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = 'Test Result';
                    $testResultDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($testResultSeries[$seriesName])) {
                        $testResultSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($testResultSeries[$seriesName]['data'], $testResultDetail);
                }

                foreach ($testResultSeries as $seriesName => $series) {
                    array_push($testResultLogs, $series);
                }
            }
            return $testResultLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $testResultLogs;
        }
    }

     /**
     *
     * Gets list logs of test results during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @return array $testResultLogs : the test result logs for table view
     */
    public function getTestResultListLogs ($serials) {
        $testResultLogs = array();
        $serials = implode(',', $serials);
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    tre.ExpressionName,
                    trmh.TestResultRevSerNum,
                    trmh.CronLogSerNum,
                    trmh.PatientSerNum,
                    sd.SourceDatabaseName,
                    trmh.TestResultAriaSer,
                    trmh.AbnormalFlag,
                    trmh.TestDate,
                    trmh.MaxNorm,
                    trmh.MinNorm,
                    trmh.TestValue,
                    trmh.UnitDescription,
                    trmh.ValidEntry,
                    trmh.DateAdded,
                    trmh.ReadStatus,
                    trmh.ModificationAction
                FROM
                    TestResultMH trmh,
                    TestResultExpression tre,
                    SourceDatabase sd
                WHERE
                    trmh.TestResultExpressionSerNum     = tre.TestResultExpressionSerNum
                AND trmh.SourceDatabaseSerNum           = sd.SourceDatabaseSerNum
                AND trmh.CronLogSerNum                  IN ($serials)
            "; 
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $logDetails = array (
                   'expression_name'        => $data[0],
                   'revision'               => $data[1],
                   'cron_serial'            => $data[2],
                   'patient_serial'         => $data[3],
                   'source_db'              => $data[4],
                   'source_uid'             => $data[5],
                   'abnormal_flag'          => $data[6],
                   'test_date'              => $data[7],
                   'max_norm'               => $data[8],
                   'min_norm'               => $data[9],
                   'test_value'             => $data[10],
                   'unit'                   => $data[11],
                   'valid'                  => $data[12],
                   'date_added'             => $data[13],
                   'read_status'            => $data[14],
                   'mod_action'             => $data[15]
                );
                array_push($testResultLogs, $logDetails);
            }

            return $testResultLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $testResultLogs;
        }
    }  


    /**
     *
     * Checks if an expression has been assigned to an test
     *
     * @param string $id    : the needle id
     * @param array $array  : the key-value haystack
     * @return $assignedTest
     */
    public function assignedSearch($id, $array) {
        $assignedTest = null;
        if(empty($array) || !$id){
            return $assignedTest;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id) {
                $assignedTest = $val;
                return $assignedTest;
            }
        }
        return $assignedTest;
    }
}

?>





