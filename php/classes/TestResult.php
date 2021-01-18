<?php

/**
 * TestResult class
 *
 */
class TestResult extends Module
{
    public function __construct($guestStatus = false)
    {
        parent::__construct(MODULE_TEST_RESULTS, $guestStatus);

//        $this->ariaDB = new DatabaseAria(
//            ARIA_DB_HOST,
//            ARIA_DB_NAME,
//            ARIA_DB_PORT,
//            ARIA_DB_USERNAME,
//            ARIA_DB_PASSWORD
//        );
    }

    /*
     * Update the list of publish flags for the test results
     * @params  $post - array - contains the list of publication and their publish status
     * @return  void
     * */
    public function updatePublishFlags($post)
    {
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
    public function getTestResultDetails($post)
    {
        $this->checkReadAccess($post);
        $id = intval($post["serial"]);

        $result = $this->opalDB->getTestResultDetails($id);
        if (count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation" => 1)));
        else if (count($result) == 1)
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
    public function getTestResultGroups()
    {
        $this->checkReadAccess();
        return $this->opalDB->getTestResultGroups();
    }

    /**
     *
     * Gets a list of test result names from a source database
     *
     * @return array $testNames : the list of test names
     */
    public function getTestNames()
    {
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
                $query = $source_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $testName = $data[0];
                    $testArray = array(
                        'name' => $testName,
                        'id' => $testName,
                        'added' => 0,
                        'assigned' => null
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
     * Validation code :    Error validation code is coded as an int of 10 bits (value from 0 to 1023). Bit informations
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
     *                      10: serial is missing or invalid (when updating only)
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateTestResult(&$post, $isAnUpdate = false)
    {
        $errCode = "";
        $post = HelpSetup::arraySanitization($post);

        if (is_array($post)) {

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
            if (!array_key_exists("tests", $post) || !is_array($post["tests"]) || count($post["tests"]) <= 0) {
                $errCode = "1" . $errCode;
            } else {
                $allGood = true;
                if (is_array($post["tests"])) {
                    foreach ($post["tests"] as $test) {
                        if (!array_key_exists("name", $test) || $test["name"] == "") {
                            $allGood = false;
                            break;
                        }
                    }
                } else
                    $allGood = false;
                if (!$allGood)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }

            //8th bit
            if (array_key_exists("eduMat", $post) && $post["eduMat"] != "") {
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
                if (is_array($post["additional_links"])) {
                    $allGood = true;
                    $addId = array();
                    foreach ($post["additional_links"] as $link) {
                        if ((!array_key_exists("name_EN", $link) || $link["name_EN"] == "") || (!array_key_exists("name_FR", $link) || $link["name"] == "name_FR") || (!array_key_exists("url_EN", $link) || $link["url_EN"] == "") || (!array_key_exists("url_FR", $link) || $link["url_FR"] == "")) {
                            $allGood = false;
                            break;
                        } else if (array_key_exists("serial", $link) && $link["serial"] != "") {
                            array_push($addId, $link["serial"]);
                        }

                    }
                    if (!$allGood)
                        $errCode = "1" . $errCode;
                    else {
                        if(count($addId) > 0) {
                            $totalCount = $this->opalDB->countTestResultsAdditionalLinks($addId);
                            if($totalCount["total"] != count($addId))
                                $errCode = "1" . $errCode;
                            else
                                $errCode = "0" . $errCode;
                        } else
                            $errCode = "0" . $errCode;
                    }
                } else
                    $errCode = "1" . $errCode;
            } else
                $errCode = "0" . $errCode;

            //10th bit
            if ($isAnUpdate) {
                if (!array_key_exists("serial", $post) || $post["serial"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $result = $this->opalDB->getTestResultDetails($post["serial"]);
                    if (count($result) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($result) == 1)
                        $errCode = "0" . $errCode;
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates test results found.");
                }
            } else
                $errCode = "0" . $errCode;


        } else
            $errCode = "1111111111";
        return $errCode;
    }

    /*
     * Insert a new test result after validation.
     * @params  $post - array - contains the test results details
     * @return  200 or error 422 with array (validation=>integer) for a validation error
     * */
    public function insertTestResult($post)
    {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateTestResult($post);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => $errCode));

        //Insert into test result control
        $toInsert = array(
            "Name_EN" => $post['name_EN'],
            "Name_FR" => $post['name_FR'],
            "Description_EN" => $post['description_EN'],
            "Description_FR" => $post['description_FR'],
            "Group_EN" => $post['group_EN'],
            "Group_FR" => $post['group_FR'],
            "PublishFlag" => 0,
            "EducationalMaterialControlSerNum" => (is_array($post['eduMat']) && isset($post['eduMat']['serial'])) ? $post['eduMat']['serial'] : null,
        );
        $newId = $this->opalDB->insertTestResult($toInsert);

        //Insert into test result expression
        $toInsertMultipleTests = array();
        foreach ($post['tests'] as $test) {
            array_push($toInsertMultipleTests, array(
                "TestResultControlSerNum" => $newId,
                "ExpressionName" => $test['name'],
            ));
        }
        $this->opalDB->insertMultipleTestExpressions($toInsertMultipleTests);

        //Insert into Test Result Additional links
        $toInsertMultipleLinks = array();
        if ($post['additional_links']) {
            foreach ($post['additional_links'] as $link) {
                array_push($toInsertMultipleLinks, array(
                    "TestResultControlSerNum" => $newId,
                    "Name_EN" => $link['name_EN'],
                    "Name_FR" => $link['name_FR'],
                    "URL_EN" => $link['url_EN'],
                    "URL_FR" => $link['url_FR'],
                ));
            }
            if (count($toInsertMultipleLinks) > 0)
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
    public function getTestResults()
    {
        $this->checkReadAccess();
        return $this->opalDB->getTestResults();
    }

    public function deleteTestResult($post)
    {
        $this->checkDeleteAccess($post);

        $errCode = "";
        if (!array_key_exists("serial", $post) || $post["serial"] == "")
            $errCode = "1";
        else {
            $result = $this->opalDB->getTestResultDetails($post["serial"]);
            if (count($result) < 1)
                $errCode = "1";
            else if (count($result) == 1)
                $errCode = "0";
            else
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates test results found.");
        }
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));

        $this->opalDB->deleteTestResultExpressions($post["serial"]);
        $this->opalDB->deleteTestResultAdditionalLinks($post["serial"]);
        $this->opalDB->deleteTestResult($post["serial"]);
        $this->opalDB->updateTestResultMHDeletion($post["serial"]);
    }

    /*
     * Return the global test result chart log or for a specific test result if an ID is specified
     * @params  $post - array : may or may not contain ID of the test result
     * @return  $testResultLogs - array : contains all the logs of test result(s)
     * */
    public function getTestResultChartLogs($post)
    {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);

        if (!array_key_exists("serial", $post) || $post["serial"] == "")
            $id = false;
        else {
            $result = $this->opalDB->getTestResultDetails($post["serial"]);
            if (count($result) < 1)
                $errCode = "1";
            else if (count($result) == 1)
                $errCode = "0";
            else
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates test results found.");
            $errCode = bindec($errCode);
            if ($errCode != 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));
            $id = $post["serial"];
        }

        if(!$id)
            $results = $this->opalDB->getTestResultChartLog();
        else
            $results = $this->opalDB->getTestResultChartLogById($id);

        $testResultLogs = array();
        $testResultSeries = array();
        foreach ($results as $data) {
            $testResultDetail = array(
                'x' => $data["x"],
                'y' => intval($data["y"]),
                'cron_serial' => $data["cron_serial"]
            );
            if (!isset($testResultSeries[$data["name"]])) {
                $testResultSeries[$data["name"]] = array(
                    'name' => $data["name"],
                    'data' => array()
                );
            }
            array_push($testResultSeries[$data["name"]]['data'], $testResultDetail);
        }

        foreach ($testResultSeries as $seriesName => $series) {
            array_push($testResultLogs, $series);
        }

        return $testResultLogs;
    }

    /**
     * Gets list logs of test results during one or many cron sessions
     */
    public function getTestResultListLogs($testResultIds)
    {
        $this->checkReadAccess($testResultIds);
        foreach ($testResultIds as &$id) {
            $id = intval($id);
        }
        return $this->opalDB->getTestResultsLogs($testResultIds);
    }

    /*
     * Update a test result. First it validates its data and structure. If everything is fine, update TestResultControl.
     * Next, it deletes unused test result expressions, then it adds the new one. Next step is the additional links.
     * If there is none, purge any current links for this specific test result. But if there is links, first build the
     * list of links to purge, update and add and execute the command. Finally, unpublish any test result without test
     * expression to avoid the cron crashing.
     * @params  $post - array : contains all the test result data
     * @return  void
     * */
    public function updateTestResult($post)
    {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateTestResult($post, true);
        $linksToKeepAndUpdate = array();
        $linksToNotDelete = array();
        $linksToAdd = array();

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => $errCode));

        $toUpdate = array(
            "name_EN" => $post['name_EN'],
            "name_FR" => $post['name_FR'],
            "description_EN" => $post['description_EN'],
            "description_FR" => $post['description_FR'],
            "group_EN" => $post['group_EN'],
            "group_FR" => $post['group_FR'],
            "EducationalMaterialControlSerNum" => (is_array($post['eduMat']) && isset($post['eduMat']['serial'])) ? $post['eduMat']['serial'] : null,
            "TestResultControlSerNum" => $post['serial'],
        );

        $result = $this->opalDB->updateTestResultControl($toUpdate);
        $result += $this->opalDB->deleteUnusedTestExpression($post['serial'], $post["tests"]);

        $toInsertMultipleTests = array();
        foreach ($post['tests'] as $test) {
            array_push($toInsertMultipleTests, array(
                "TestResultControlSerNum" => $post['serial'],
                "ExpressionName" => $test['name'],
            ));
        }
        $result += $this->opalDB->insertMultipleTestExpressions($toInsertMultipleTests);

        if ((array_key_exists("additional_links", $post)) && (is_array($post["additional_links"]))) {
            foreach($post["additional_links"] as $link) {
                if ($link["serial"] != "") {
                    array_push($linksToNotDelete, $link["serial"]);
                    array_push($linksToKeepAndUpdate, array(
                        "TestResultAdditionalLinksSerNum" => $link["serial"],
                        "Name_EN" => $link["name_EN"],
                        "Name_FR" => $link["name_FR"],
                        "URL_EN" => $link["url_EN"],
                        "URL_FR" => $link["url_FR"]
                    ));
                } else
                    array_push($linksToAdd, array(
                        "TestResultControlSerNum" => $post["serial"],
                        "Name_EN" => $link["name_EN"],
                        "Name_FR" => $link["name_FR"],
                        "URL_EN" => $link["url_EN"],
                        "URL_FR" => $link["url_FR"]
                    ));
            }
        } else
            $linksToNotDelete = array(-1);

        if (count($linksToNotDelete) > 0)
            $result += $this->opalDB->deleteUnusedAddLinks($post['serial'], $linksToNotDelete);
        if (count($linksToKeepAndUpdate) > 0) {
            foreach ($linksToKeepAndUpdate as $link) {
                $result += $this->opalDB->updateTestResultAdditionalLink($link);
            }
        }
        if (count($linksToAdd) > 0) {
            $result += $this->opalDB->insertTestResultAdditionalLinks($linksToAdd);
        }

        // This function sanitize and deactivate the publish flags of test results without any test name, otherwise
        // the cron job will crash (don't ask)
        $this->opalDB->sanitizeEmptyTestResults();
    }

    /**
     *
     * Checks if an expression has been assigned to an test
     *
     * @param string $id : the needle id
     * @param array $array : the key-value haystack
     * @return $assignedTest
     */
    public function assignedSearch($id, $array)
    {
        $assignedTest = null;
        if (empty($array) || !$id) {
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

    public function getEducationalMaterials()
    {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }
}