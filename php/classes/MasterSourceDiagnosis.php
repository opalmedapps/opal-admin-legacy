<?php


class MasterSourceDiagnosis extends MasterSourceModule {

    /**
     * Get the list of all undeleted master diagnoses
     * @return array - List of master diagnoses
     */
    public function getSourceDiagnoses() {
        $this->checkReadAccess();
        return $this->opalDB->getSourceDiagnoses();
    }

    /**
     * Get the details of a source diagnosis.
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 4 bits (value from 0 to 15). Bit informations are from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: record not found
     * @param array - should contains externalId and source
     * @return array - details of the source diagnosis
     */
    public function getSourceDiagnosisDetails($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        $this->_validateKeyFields($errCode, $post);

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->getSourceDiagnosisDetails($post["externalId"], $post["source"], $post["code"]);
        if(count($results) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>bindec("1000")));
        else if(count($results) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated entries detected in the records. Please contact your administrator.");
        return $results[0];
    }

    /**
     * Insert an array of source diagnoses entry after their validation.
     * If the record already exists, it will run an update because the check is done with externalId and source, NOT
     * the primary key ID. We cannot use a REPLACE with externalID and source or the primary key will be replaced, and
     * we cannot use the primary key because the external sources calling the API dont know the primary key!
     * @param $post array - contains every source diagnoses to enter
     * @return false|array - void or error 422 with lists of failed diagnosis with validation code explanation
     */
    public function insertSourceDiagnoses($post) {
        $this->checkWriteAccess($post);
        $toInsert = array();
        $toUpdate = array();
        $errMsgs = $this->_validateAndSanitizeSourceDiagnoses($post, $toInsert, $toUpdate);

        if(count($toInsert) > 0)
            $this->opalDB->insertSourceDiagnoses($toInsert);

        foreach ($toUpdate as $item) {
            $this->opalDB->replaceSourceDiagnosis($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }

    /**
     * Update an array of source diagnoses entry after their validation.
     * @param $post array that contains every source diagnoses to update
     * @return false|array lists of failed diagnosis with validation code explanation
     */
    public function updateSourceDiagnoses($post) {
        $this->checkWriteAccess($post);
        $toUpdate = array();
        $errMsgs = $this->_validateAndSanitizeSourceDiagnosesUpdate($post, $toUpdate);

        foreach ($toUpdate as $item)
            $this->opalDB->updateSourceDiagnosis($item);

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }

    /**
     * Check if a specific diagnosis exists.
     * Used in the OpalAdmin to warn the user a master source diagnosis with the same external ID and source already
     * exists. Validation done on the incoming data.
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 3 bits (value from 0 to 7). Bit informations are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     * @param $post array - Contains source and externalId
     * @return array - contains the record found if it exists. If not, return empty array
     */
    public function doesDiagnosisExists($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        $this->_validateKeyFields($errCode, $post);

        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->isMasterSourceDiagnosisExists($post["source"], $post["externalId"], $post["code"]);
        if(count($results) <= 0) return $results;
        else if (count($results) == 1) return $results[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
    }

    /**
     * Validate and sanitize a list of diagnoses before an insert.
     * Returns one array with proper data sanitized and ready, and another array with list of invalid diagnoses.
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: creation date (if present) is in invalid format
     *                      6: too many records to process
     * @param $post array - $_POST content. Each entry must contains the following:
     *                      source : source database ID. See table SourceDatabase (mandatory)
     *                      externalID : external ID of the diagnosis in the source database (mandatory)
     *                      code : code of the diagnosis (mandatory)
     *                      description : description of the diagnosis (mandatory)
     *                      creationDate - creation date of the record in the source database (optional)
     * @param $toInsert array - list of diagnoses to insert
     * @param $toUpdate array - list of diagnoeses to replace
     * @return array - contains the list of errors detected
     */
    protected function _validateAndSanitizeSourceDiagnoses(&$post, &$toInsert, &$toUpdate) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => bindec("100000")));

        foreach ($post as $item) {
            if(is_array($item)) {

                $errCode = "";

                $this->_validateKeyFields($errCode, $item);
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
                    $data = $this->opalDB->isMasterSourceDiagnosisExists($item["source"], $item["externalId"], $item["code"]);
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
            } else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => 31));
                break;
            }
        }
        return $errMsgs;
    }

    /**
     * Validate and sanitize a list of diagnoses before an update.
     * Returns one array with proper data sanitized and ready, and another array with list of invalid diagnoses.
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: record not found
     *                      6: too many records to process
     * @param $post array - Contains data correctly formatted and ready to be inserted
     * @param $toUpdate - list of master source diagnoses to update
     * @return array - contains the invalid entries with an error code.
     */
    protected function _validateAndSanitizeSourceDiagnosesUpdate(&$post, &$toUpdate) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => bindec("100000")));

        foreach ($post as $item) {
            if(is_array($item)) {
                $errCode = "";
                $this->_validateKeyFields($errCode, $item);
                if(!array_key_exists("description", $item) || $item["description"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

                $errCode = bindec($errCode);
                if($errCode == 0) {

                    $data = $this->opalDB->isMasterSourceDiagnosisExists($item["source"], $item["externalId"], $item["code"]);
                    if(count($data) < 1 || $data[0]["deleted"] == DELETED_RECORD) {
                        $errCode = "10000";
                    }
                    else if (count($data) == 1) {
                        if($data[0]["code"] != $item["code"])
                            $errCode = "100";
                    }
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");

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
                    $item["validation"] = $errCode;
                    array_push($errMsgs, $item);
                }
            }
            else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => 31));
                break;
            }
        }
        return $errMsgs;
    }

    /**
     * Validate and sanitize a list of diagnoses before a deletion.
     * Returns one array with proper data sanitized and ready, and another array with list of invalid diagnoses.
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: diagnosis not found
     *                      4: too many records to process
     * @param $post array - Contains data correctly formatted and ready to be inserted
     * @param $toDelete - master source diagnoses to delete
     * @return array - list of errors encountered
     */
    protected function _validateAndSanitizeSourceDiagnosesDelete(&$post, &$toDelete) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => bindec("1000")));

        foreach ($post as $item) {
            if(is_array($item)) {

                $errCode = "";
                $this->_validateKeyFields($errCode, $item);

                if(bindec($errCode) == 0) {
                    $data = $this->opalDB->isMasterSourceDiagnosisExists($item["source"], $item["externalId"], $item["code"]);
                    if(count($data) < 1 || $data[0]["deleted"] == DELETED_RECORD)
                        $errCode = "100";
                    else if(count($data) > 1)
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                }
                else
                    $errCode = "0" . $errCode;

                $errCode = bindec($errCode);
                if($errCode == 0) {
                    array_push($toDelete, array(
                        "source" => $item["source"],
                        "code" => $item["code"],
                        "externalId" => $item["externalId"],
                    ));
                }
                else {
                    $item["validation"] = $errCode;
                    array_push($errMsgs, $item);
                }
            }
            else {
                HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => 7));
                break;
            }
        }
        return $errMsgs;
    }

    /**
     * Mark a master source diagnosis as deleted.
     *
     * WARNING!!! No record should be EVER be removed from this table! It should only being marked as
     * being deleted ONLY after it was verified the record is not in used, the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $post array - contains combo of externalId - source - code
     * @return false
     */
    function markAsDeletedSourceDiagnoses($post) {
        $this->checkDeleteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $toDelete = array();
        $errMsgs = $this->_validateAndSanitizeSourceDiagnosesDelete($post, $toDelete);

        foreach ($toDelete as $item)
            $this->opalDB->markAsDeletedSourceDiagnoses($item);

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }
}