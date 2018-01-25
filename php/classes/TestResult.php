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
     * @return array $response : response
     */
    public function updatePublishFlags( $testResultList ) {
        $response = array(
            'value'     => 0,
            'message'   => ''
        );
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
                        TestResultControl.PublishFlag = $publishFlag
                    WHERE
                        TestResultControl.TestResultControlSerNum = $serial
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

        $groups = array (
            'EN'    => array(),
            'FR'    => array()
        );
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    trc.Group_EN
                FROM
                    TestResultControl trc
                ORDER BY
                    trc.Group_EN
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                array_push($groups['EN'], $data[0]);
            }
            $sql = "
                SELECT DISTINCT
                    trc.Group_FR
                FROM
                    TestResultControl trc
                ORDER BY
                    trc.Group_FR
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                array_push($groups['FR'], $data[0]);
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

                    $testArray = array(
                        'name'      => $data[0],
                        'id'        => $data[0],
                        'added'     => 0
                    );
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
        $eduMatSer          = 0;
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
                        LastPublished
                    )
                VALUES (
                    \"$name_EN\",
                    \"$name_FR\",
                    \"$description_EN\",
                    \"$description_FR\",
                    \"$group_EN\",
                    \"$group_FR\",
                    '$eduMatSer',
                    NOW(),
                    NOW()
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
                            DateAdded
                        )
                    VALUES (
                        '$testResultSer',
                        \"$name\",
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE 
                        TestResultControlSerNum = '$testResultSer'
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
        $eduMatSer          = $testResultDetails['edumatser'];
        $additionalLinks    = $testResultDetails['additional_links'];

        $existingTests      = array();

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
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
                    TestResultControl.EducationalMaterialControlSerNum = '$eduMatSer'
                WHERE
                    TestResultControl.TestResultControlSerNum = $serial
            ";

            $query = $host_db_link->prepare( $sql );
			$query->execute();

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
				}
			}

            // If new test names, insert into database
            foreach ($tests as $test) {
                $testName = $test['name'];
                if(!in_array($test, $existingTests)) {
                    $sql = "
                        INSERT INTO
                            TestResultExpression (
                                TestResultControlSerNum,
                                ExpressionName
                            )
                        VALUES (
                            '$serial',
                            \"$test\"
                        )
                        ON DUPLICATE KEY UPDATE
                            TestResultControlSerNum = '$serial'
                    ";

	                $query = $host_db_link->prepare( $sql );
					$query->execute();
				}
			}

            if ($additionalLinks) {

                // clear existing links
                $sql = "
                    DELECT FROM 
                        TestResultAdditionalLinks
                    WHERE
                        TestResultAdditionalLinks.TestResultControlSerNum = '$serial'
                ";
                $query = $host_db_link->prepare( $sql );
                $query->execute();

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
     * @return array $response : response
     */
    public function deleteTestResult ($testResultSer) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

	    try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    TestResultControl
                WHERE
                    TestResultControl.TestResultControlSerNum = $testResultSer
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

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

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
		}
	}
}

?>





