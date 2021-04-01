<?php


class MasterSourceTestResult extends MasterSourceModule {

    /*
     * Get the list of all undeleted test results
     * @params  void
     * @return  array - List of source tests results
     * */
    public function getSourceTestResults() {
        $this->checkReadAccess();
        return $this->opalDB->getSourceTestResults();
    }

    /*
     * Get the details of a source test result.
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 2 bits (value from 0 to 8). Bit informations are from right to left:
     *                      1: source invalid or missing
     *                      2: code invalid or missing
     *                      3: record not found
     * @params  $post - array - should contains externalId and source
     * @return  array - details of the source test result
     * */
    public function getSourceTestResultDetails($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        if(!array_key_exists("source", $post) || $post["source"] == "")
            $errCode = "1" . $errCode;
        else {
            $data = $this->opalDB->getSourceId($post["source"]);
            if(count($data) != 1)
                $errCode = "1" . $errCode;
            else {
                $post["source"] = $data[0]["ID"];
                $errCode = "0" . $errCode;
            }
        }
        if(!array_key_exists("code", $post) || $post["code"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->getSourceTestResultDetails($post["source"], $post["code"]);

        if(count($results) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>4));
        else if(count($results) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated entries detected in the records. Please contact your administrator.");
        return $results[0];
    }

    /*
     * Insert an array of source test results entry after their validation. But if the record already exists, it will
     * run an update because the check is done with code and source, NOT the primary key ID. We cannot use a
     * REPLACE with code and source or the primary key will be replaced, and we cannot use the primary key because
     * the external sources calling the API dont know the primary key!
     * @params  $post - array that contains every source test results to enter
     * @return  void or error 422 with lists of failed test results with validation code explanation
     * */
    public function insertSourceTestResult($post) {
        $this->checkWriteAccess($post);
        $toInsert = array();
        $toUpdate = array();
        $errMsgs = $this->_validateAndSanitizeTestResults($post, $toInsert, $toUpdate);

        if(count($toInsert) > 0)
            $this->opalDB->insertSourceTestResults($toInsert);

        foreach ($toUpdate as $item) {
            $this->opalDB->replaceSourceTestResult($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, $errMsgs);

        return false;
    }

    /*
     * Update an array of source test results entry after their validation.
     * @params  $post - array that contains every source test results to update
     * @return  void or error 422 with lists of failed test results with validation code explanation
     * */
    public function updateSourceTestResults($post) {
        $this->checkWriteAccess($post);
        $toUpdate = array();
        $errMsgs = $this->_validateAndSanitizeSourceTestResultsUpdate($post, $toUpdate);

        foreach ($toUpdate as $item) {
            $this->opalDB->updateSourceTestResult($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, $errMsgs);

        return false;
    }

    /*
     * Check if a specific test result exists. Used in the OpalAdmin to warn the user a master source test result with the
     * same external ID and source already exists. Validation done on the incoming data.
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 2 bits (value from 0 to 3). Bit informations are coded from right to left:
     *                      1: source invalid or missing
     *                      2: code invalid or missing
     * @params  $post - array. Contains source and externalId
     * @return  array - contains the record found if it exists. If not, return empty array
     * */
    public function doesTestResultExists($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if(!array_key_exists("source", $post) || $post["source"] == "")
            $errCode = "1" . $errCode;
        else {
            $data = $this->opalDB->getSourceId($post["source"]);
            if(count($data) != 1)
                $errCode = "1" . $errCode;
            else {
                $post["source"] = $data[0]["ID"];
                $errCode = "0" . $errCode;
            }
        }
        if(!array_key_exists("code", $post) || $post["code"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->isSourceTestResultsExists($post["source"], $post["code"]);
        if(count($results) <= 0) return $results;
        else if (count($results) == 1) return $results[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
    }

    /*
     * Validate and sanitize a list of test results before an insert. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid diagnoses.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the test result in the source database (optional)
     *                          code : code of the test result (mandatory)
     *                          description : description of the test result (mandatory)
     *                          creationDate - creation date of the record in the source database (optional)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId (if present) invalid
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: creation date (if present) is in invalid format
     *                      6: too many records to process
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateAndSanitizeTestResults(&$post, &$toInsert, &$toUpdate) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        foreach ($post as $item) {
            if(is_array($item)) {

                $errCode = "";

                if(!array_key_exists("source", $item) || $item["source"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $data = $this->opalDB->getSourceId($item["source"]);
                    if(count($data) != 1)
                        $errCode = "1" . $errCode;
                    else {
                        $item["source"] = $data[0]["ID"];
                        $errCode = "0" . $errCode;
                    }
                }

                if(!array_key_exists("externalId", $item) || $item["externalId"] == "") {
                    $item["externalId"] = -1;
                }
                $errCode = "0" . $errCode;

                if (!array_key_exists("code", $item) || $item["code"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

                if (!array_key_exists("description", $item) || $item["description"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

                if (array_key_exists("creationDate", $item) && $item["creationDate"] != "") {
                    if (!HelpSetup::verifyDate($item["creationDate"], false, 'Y-m-d H:i:s'))
                        $errCode = "1" . $errCode;
                    else {
                        $item["creationDate"] = date("Y-m-d H:i:s", strtotime($item["creationDate"]));
                        $errCode = "0" . $errCode;
                    }
                } else {
                    $item["creationDate"] = date("Y-m-d H:i:s");
                    $errCode = "0" . $errCode;
                }

                $errCode = bindec($errCode);
                if ($errCode == 0) {
                    $data = $this->opalDB->isSourceTestResultsExists($item["source"], $item["code"]);
                    if (count($data) < 1 || (count($data) == 1 && $data[0]["deleted"] == DELETED_RECORD))
                        array_push($toInsert, array(
                            "source" => $item["source"],
                            "externalId" => $item["externalId"],
                            "code" => $item["code"],
                            "description" => $item["description"],
                            "creationDate" => $item["creationDate"]
                        ));
                    else if (count($data) == 1) {
                        if($data[0]["code"] == $item["code"])
                            array_push($toUpdate, array(
                                "source" => $item["source"],
                                "externalId" => $item["externalId"],
                                "code" => $item["code"],
                                "description" => $item["description"],
                                "creationDate" => $item["creationDate"]
                            ));
                        else {
                            $item["validation"] = bindec("100");
                            array_push($errMsgs, $item);
                        }
                    }
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                }
                else {
                    $item["validation"] = $errCode;
                    array_push($errMsgs, $item);
                }
            }
            else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => 31));
                break;
            }
        }
        return $errMsgs;
    }

    /*
     * Validate and sanitize a list of diagnoses before an update. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid diagnoses.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the test result in the source database (optional)
     *                          code : code of the test result (mandatory)
     *                          description : description of the test result (mandatory)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId (if present) invalid
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: record not found
     *                      6: to much records to process
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateAndSanitizeSourceTestResultsUpdate(&$post, &$toUpdate) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => bindec("100000")));

        foreach ($post as $item) {
            if(is_array($item)) {
                $errCode = "";
                if(!array_key_exists("source", $item) || $item["source"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $data = $this->opalDB->getSourceId($item["source"]);
                    if(count($data) != 1)
                        $errCode = "1" . $errCode;
                    else {
                        $item["source"] = $data[0]["ID"];
                        $errCode = "0" . $errCode;
                    }
                }

                if(!array_key_exists("externalId", $item) || $item["externalId"] == "") {
                    $item["externalId"] = -1;
                }
                $errCode = "0" . $errCode;

                if(!array_key_exists("code", $item) || $item["code"] == "") {
                    $errCode = "1" . $errCode;
                }
                else
                    $errCode = "0" . $errCode;
                if(!array_key_exists("description", $item) || $item["description"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

                if (bindec($errCode) == 0) {
                    $data = $this->opalDB->isSourceTestResultsExists($item["source"], $item["code"]);
                    if(count($data) < 1 || $data[0]["deleted"] == DELETED_RECORD)
                        $errCode = "10000";
                    else if (count($data) == 1) {
                        if($data[0]["code"] != $item["code"])
                            $errCode = "100";
                    }
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                }

                $errCode = bindec($errCode);
                if($errCode == 0)
                    array_push($toUpdate, array(
                        "source"=>$item["source"],
                        "externalId"=>$item["externalId"],
                        "code"=>$item["code"],
                        "description"=>$item["description"],
                    ));
                else {
                    $item["validation"] = $errCode;
                    array_push($errMsgs, $item);
                }
            }
            else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => 31));
                break;
            }
        }
        return $errMsgs;
    }

    /*
     * Validate and sanitize a list of test results before an update. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid test results.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          code : code of the test result (mandatory)
     *                          description : description of the test result (mandatory)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: code invalid or missing
     *                      3: test result not found
     *                      4: no much records to process
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateAndSanitizeSourceTestResultsDelete(&$post, &$toDelete) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => bindec("1000")));

        foreach ($post as $item) {
            if(is_array($item)) {
                $errCode = "";
                if(!array_key_exists("source", $item) || $item["source"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $data = $this->opalDB->getSourceId($item["source"]);
                    if(count($data) != 1)
                        $errCode = "1" . $errCode;
                    else {
                        $item["source"] = $data[0]["ID"];
                        $errCode = "0" . $errCode;
                    }
                }

                if(!array_key_exists("code", $item) || $item["code"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

                if (bindec($errCode) == 0) {
                    $count = $this->opalDB->isSourceTestResultsExists($item["source"], $item["code"]);
                    if(count($count) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($count) == 1)
                        $errCode = "0" . $errCode;
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                } else
                    $errCode = "0" . $errCode;

                $errCode = bindec($errCode);
                if($errCode == 0) {
                    array_push($toDelete, array(
                        "source" => $item["source"],
                        "code" => $item["code"],
                    ));
                }
                else {
                    $item["validation"] = $errCode;
                    array_push($errMsgs, $item);
                }
            }
            else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation" => 7));
                break;
            }
        }
        return $errMsgs;
    }

    /**
     * Mark a source test result as deleted.
     *
     * WARNING!!! No record should be EVER be removed from this table! It should only being marked as
     * being deleted ONLY after it was verified the record is not in used, the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params $post (arrays that contains combo of externalId - source)
     * @return false
     */
    function markAsDeletedSourceTestResults($post) {
        $this->checkDeleteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $toDelete = array();
        $errMsgs = $this->_validateAndSanitizeSourceTestResultsDelete($post, $toDelete);

        foreach ($toDelete as $item) {
            $this->opalDB->markAsDeletedSourceTestResults($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, $errMsgs);

        return false;
    }
}