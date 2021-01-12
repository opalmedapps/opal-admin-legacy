<?php

/**
 * TestResult class
 *
 */
class TestResult extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TEST_RESULTS, $guestStatus);
    }

    /*
     * Update the list of publish flags for the test results
     * @params  $post - array - contains the list of publication and their publish status
     * @return  void
     * */
    public function updatePublishFlags($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post["data"]);

        foreach ($post as $testResult) {
            $this->opalDB->updateTestResultPublishFlag($testResult['serial'], $testResult['publish']);
        }
    }

    /*
     * Get the details of a test results. It includes the expression names, educational materials and additional links
     * if present.
     * @params  $post - array - contains only the serial or ID
     * @return  $result - contains all the details of the test result
     * */
    public function getTestResultDetails ($post) {
        $this->checkReadAccess($post);
        $id = intval($post["serial"]);

        $result = $this->opalDB->getTestResultDetails($id);
        if(count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation"=>1)));
        else if(count($result) == 1)
            $result = $result[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates test results found.");

        $result["tests"] = $this->opalDB->getTestResultExpressionNames($id);
        $result["count"] = count($result["tests"]);
        $result["additional_links"] = $this->opalDB->getTestResultAdditionalLinks($id);

        $result["eduMat"] = array();
        if (intval($result["eduMatSer"]) != 0)
            $result["eduMat"] = $this->_getEducationalMaterialDetails(intval($result["eduMatSer"]));

        return $result;
    }

    /*
     * Get the list of test result groups in french and english
     * @params  void
     * @return  array - list of the result group
     * */
    public function getTestResultGroups () {
        $this->checkReadAccess();
        return $this->opalDB->getTestResultGroups();
    }

    /**
     *
     * Gets a list of test result names from a source database
     *
     * @return array $testNames : the list of test names
     */
    public function getTestNames() {
        $this->checkReadAccess();
        $testNames = array();
        $databaseObj = new Database();
        $assignedTests = $this->opalDB->getAssignedTests();

        try {

            // get already assigned expressions from our database


            // ***********************************
            // ARIA
            // ***********************************
            $sourceDBSer = ARIA_SOURCE_DB;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "
                    SELECT DISTINCT
                        tr.comp_name
                    FROM
                        VARIAN.dbo.test_result tr
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


            return $testNames;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for lab results. " . $e->getMessage());
        }
    }

    /*
     * Validate and sanitize a test result.
     * @params  $post : array - data for the test result to validate
     * Validation code :    Error validation code is coded as an int of 9 bits (value from 0 to 511). Bit informations
     *                      are coded from right to left:
     *                      1: english name missing
     *                      2: french name missing
     *                      3: english description missing
     *                      4: french description missing
     *                      5: english group missing
     *                      6: french group missing
     *                      7: test names missing or invalid
     *                      8: educational material (if present) invalid
     *                      9: additional links (if present) invalid
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateTestResult(&$post) {
        $errCode = "";
        $post = HelpSetup::arraySanitization($post);

        if(is_array($post)) {

            //1st bit
            if (!array_key_exists("name_EN", $post) || $post["name_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //2nd bit
            if (!array_key_exists("name_FR", $post) || $post["name_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //3rd bit
            if (!array_key_exists("description_EN", $post) || $post["description_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //4th bit
            if (!array_key_exists("description_FR", $post) || $post["description_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //5th bit
            if (!array_key_exists("group_EN", $post) || $post["group_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //6th bit
            if (!array_key_exists("group_FR", $post) || $post["group_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            //7th bit
            if (!array_key_exists("tests", $post) || !is_array($post["tests"])) {
                $errCode = "1" . $errCode;
            }
            else {
                $allGood = true;
                foreach($post["tests"] as $test) {
                    if (!array_key_exists("name", $test) || $test["name"] == "") {
                        $allGood = false;
                        break;
                    }
                }
                if(!$allGood)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }

            //8th bit
            if(array_key_exists("eduMat", $post) && $post["eduMat"] != "") {
                if (!is_array($post["eduMat"]) || !array_key_exists("serial", $post["eduMat"]) || $post["eduMat"]["serial"] == "") {
                    $errCode = "1" . $errCode;
                } else {
                    $count = $this->opalDB->doesEduMaterialExists($post["eduMat"]["serial"]);
                    if (count($count) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($count) == 1)
                        $errCode = "0" . $errCode;
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated entries detected in the records. Please contact your administrator.");
                }
            } else
                $errCode = "0" . $errCode;

            //9th bit
            if (array_key_exists("additional_links", $post)) {
                if(is_array($post["additional_links"])) {
                    $allGood = true;
                    foreach ($post["additional_links"] as $link) {

                        if ((!array_key_exists("name_EN", $link) || $link["name_EN"] == "") || (!array_key_exists("name_FR", $link) || $link["name"] == "name_FR") || (!array_key_exists("url_EN", $link) || $link["url_EN"] == "") || (!array_key_exists("url_FR", $link) || $link["url_FR"] == "")) {
                            $allGood = false;
                            break;
                        }
                    }
                    if(!$allGood)
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                } else
                    $errCode = "1" . $errCode;
            } else
                $errCode = "0" . $errCode;
        } else
            $errCode = "111111111";
        return $errCode;
    }

    /*
     * Insert a new test result after validation.
     * @params  $post - array - contains the test results details
     * @return  200 or error 422 with array (validation=>integer) for a validation error
     * */
    public function insertTestResult ($post) {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateTestResult($post);
        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));

        //Insert into test result control
        $toInsert = array(
            "Name_EN"=>$post['name_EN'],
            "Name_FR"=>$post['name_FR'],
            "Description_EN"=>$post['description_EN'],
            "Description_FR"=>$post['description_FR'],
            "Group_EN"=>$post['group_EN'],
            "Group_FR"=>$post['group_FR'],
            "PublishFlag"=>0,
            "EducationalMaterialControlSerNum"=>( is_array($post['eduMat']) && isset($post['eduMat']['serial']) ) ? $eduMatSer = $post['eduMat']['serial'] : 'NULL',
        );
        $newId = $this->opalDB->insertTestResult($toInsert);

        //Insert into test result expression
        $toInsertMultipleTests = array();
        foreach ($post['tests'] as $test) {
            array_push($toInsertMultipleTests, array(
                "TestResultControlSerNum"=>$newId,
                "ExpressionName"=>$test['name'],
            ));
        }
        $this->opalDB->insertMultipleTestExpressions($toInsertMultipleTests);

        //Insert into Test Result Additional links
        $toInsertMultipleLinks = array();
        if($post['additional_links']) {
            foreach($post['additional_links'] as $link){
                array_push($toInsertMultipleLinks, array(
                    "TestResultControlSerNum"=>$newId,
                    "Name_EN"=>$link['name_EN'],
                    "Name_FR"=>$link['name_FR'],
                    "URL_EN"=>$link['url_EN'],
                    "URL_FR"=>$link['url_FR'],
                ));
            }
            if(count($toInsertMultipleLinks) > 0)
                $this->opalDB->insertTestResultAdditionalLinks($toInsertMultipleLinks);
        }

        // This function sanitize and deactivate the publish flags of test results without any test name, otherwise
        // the cron job will crash (don't ask)
        $this->opalDB->sanitizeEmptyTestResults();
    }

    /*
     * Get the list of all test results available
     * @params  void
     * @return  array - list of all test results
     * */
    public function getTestResults () {
        $this->checkReadAccess();
        return $this->opalDB->getTestResults();
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
        $this->checkDeleteAccess(array($testResultSer, $user));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for test result. " . $e->getMessage());
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for test result. " . $e->getMessage());
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
        $this->checkReadAccess($serial);
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for test result. " . $e->getMessage());
        }
    }

    /**
     * Gets list logs of test results during one or many cron sessions
     */
    public function getTestResultListLogs($testResultIds) {
        $this->checkReadAccess($testResultIds);
        foreach ($testResultIds as &$id) {
            $id = intval($id);
        }
        return $this->opalDB->getTestResultsLogs($testResultIds);
    }

    /**
     *
     * Updates test result details in the database
     *
     * @param array $testResultDetails : the test result details
     * @return array : response
     */
    public function updateTestResult($testResultDetails) {
        $this->checkWriteAccess($testResultDetails);
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

            if ($testNamesUpdated) {

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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for test result. " . $e->getMessage());
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

    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }
}