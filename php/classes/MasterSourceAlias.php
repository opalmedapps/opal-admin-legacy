<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

class MasterSourceAlias extends MasterSourceModule {

    /**
     * Get the list of all undeleted master aliases
     * @return array
     */
    public function getSourceAliases() {
        $this->checkReadAccess();
        return $this->opalDB->getSourceAliases();
    }

    /**
     * Get the source alias details
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 3 bits (value from 0 to 15). Bit informations are from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: record not found
     * @param $post array - contains the externalId, code, source to identity the record
     * @param $typeAlias int - determined if it is an appointment, a task or a document
     * @return array
     */
    protected function _getSourceAliasDetails($post, $typeAlias) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        $this->_validateKeyFields($errCode, $post);

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->getSourceAliasDetails($post["externalId"], $post["source"], $post["code"], $typeAlias);
        if(count($results) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>4));
        else if(count($results) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated entries detected in the records. Please contact your administrator.");
        return $results[0];
    }

    /*
     * Insert an array of source aliases entry after their validation. But if the record already exists, it will run
     * an update because the check is done with externalId and source, NOT the primary key ID. We cannot use a REPLACE
     * with externalID and source or the primary key will be replaced, and we cannot use the primary key because the
     * external sources calling the API dont know the primary key!
     * @params  $post - array that contains every source aliases to enter
     * @return  void or error 422 with lists of failed alias with validation code explanation
     * */
    protected function _insertSourceAliases($post, $typeAlias) {
        $toInsert = array();
        $toUpdate = array();

        $errMsgs = $this->_validateAndSanitizeSourceAliases($post, $toInsert, $toUpdate, $typeAlias);
        if(count($toInsert) > 0)
            $this->opalDB->insertSourceAliases($toInsert);

        foreach ($toUpdate as $item) {
            $this->opalDB->replaceSourceAlias($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }

    /**
     * Update an array of source aliases entry after their validation.
     * @param $post array - contains every source aliases to update
     * @param $typeAlias int - type of alias (task/doc/apointment)
     * @return false
     */
    protected function _updateSourceAliases($post, $typeAlias) {
        $toUpdate = array();
        $errMsgs = $this->_validateAndSanitizeSourceAliasesUpdate($post, $toUpdate, $typeAlias);

        foreach ($toUpdate as $item)
            $this->opalDB->updateSourceAlias($item);

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }

    /**
     * Check if a specific alias exists. Used in the OpalAdmin to warn the user a master source alias with the
     * same external ID and source already exists. Validation done on the incoming data.
     * Validation code :    in case of error returns code 422 with validation code. Error validation code is coded as
     *                      an int of 2 bits (value from 0 to 3). Bit informations are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     * @param $post array       Contains source and externalId
     * @param $typeAlias int    Type of alias
     * @return false|mixed
     */
    protected function _doesAliasExists($post, $typeAlias) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        $this->_validateKeyFields($errCode, $post);

        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->isMasterSourceAliasExists($post["source"], $post["externalId"], $post["code"], $typeAlias);
        if(count($results) <= 0) return $results;
        else if (count($results) == 1) return $results[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
        return false;
    }

    /**
     * Validate and sanitize a list of aliases before an insert. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid aliases. Inserting a record when it exists already, will
     * update some fields. Inserting a record that already exists bu it marked as deleted will update it completly.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the alias in the source database (mandatory)
     *                          code : code of the alias (mandatory)
     *                          description : description of the alias (mandatory)
     *                          creationDate - creation date of the record in the source database (optional)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: creation date (if present) is in invalid format
     *                      6: too many records to process
     *                      7: record already exists (temporary)
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     */
    protected function _validateAndSanitizeSourceAliases(&$post, &$toInsert, &$toUpdate, &$typeAlias) {
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
                    $data = $this->opalDB->isMasterSourceAliasExists($item["source"], $item["externalId"], $item["code"], $typeAlias);

                    if (count($data) < 1) // || (count($data) == 1 && $data[0]["deleted"] == DELETED_RECORD))
                        array_push($toInsert, array(
                            "source" => $item["source"],
                            "externalId" => $item["externalId"],
                            "type" => $typeAlias,
                            "code" => $item["code"],
                            "description" => $item["description"],
                            "creationDate" => $item["creationDate"]
                        ));
/*                    else if (count($data) == 1) {
                        if($data[0]["code"] == $item["code"])
                            array_push($toUpdate, array(
                                "source" => $item["source"],
                                "externalId" => $item["externalId"],
                                "type" => $typeAlias,
                                "code" => $item["code"],
                                "description" => $item["description"],
                                "creationDate" => $item["creationDate"]
                            ));
                        else {
                            $item["validation"] = bindec("100");
                            array_push($errMsgs, $item);
                        }
                    }*/
                    // ignore the duplicate alias error temporarily
                    /*else {
                        //HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                        $item["validation"] = bindec("1000000");
                        array_push($errMsgs, $item);
                    }*/
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
     * Validate and sanitize a list of aliases before an update. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid aliases. Updating a record that does not exists or is deleted
     * returns an error. If the received code is different than the current code, update is rejected.
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 6 bits (value from 0 to 63). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: description invalid or missing
     *                      5: record not found
     *                      6: too many records to process
     * @param $post array       $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the alias in the source database (mandatory)
     *                          code : code of the alias (mandatory)
     *                          description : description of the alias (mandatory)
     * @param $toUpdate array   list of valid source aliases to update
     * @param $typeAlias int    Type of alias
     * @return array            array or error msgs
     */
    protected function _validateAndSanitizeSourceAliasesUpdate(&$post, &$toUpdate, &$typeAlias) {
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

                if (bindec($errCode) == 0) {
                    $data = $this->opalDB->isMasterSourceAliasExists($item["source"], $item["externalId"], $item["code"], $typeAlias);
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
                        "type"=>$typeAlias,
                        "code"=>$item["code"],
                        "description"=>$item["description"],
                    ));
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
     * Validate and sanitize a list of aliases before an update. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid aliases.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the alias in the source database (mandatory)
     *                          code : code of the alias (mandatory)
     *                          description : description of the alias (mandatory)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit informations
     *                      are coded from right to left:
     *                      1: source invalid or missing
     *                      2: externalId invalid or missing
     *                      3: code invalid or missing
     *                      4: alias (task/document/appointment) not found
     *                      5: too many records to process
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     */
    protected function _validateAndSanitizeSourceAliasesDelete(&$post, &$toDelete, &$typeAlias) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        if(count($post) > MAXIMUM_RECORDS_BATCH)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => bindec("10000")));

        foreach ($post as $item) {
            if(is_array($item)) {
                $errCode = "";
                $this->_validateKeyFields($errCode, $item);

                if(bindec($errCode) == 0) {
                    $data = $this->opalDB->isMasterSourceAliasExists($item["source"], $item["externalId"], $item["code"], $typeAlias);
                    if(count($data) < 1 || $data[0]["deleted"] == DELETED_RECORD)
                        $errCode = "1000";
                    else if (count($data) > 1)
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
                }

                $errCode = bindec($errCode);
                if($errCode == 0) {
                    array_push($toDelete, array(
                        "source" => $item["source"],
                        "externalId" => $item["externalId"],
                        "code" => $item["code"],
                        "type" => $typeAlias,
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
     * Mark an alias as deleted. Call by one of the API calls from task, appointment or document
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
    protected function _markAsDeletedSourceAliases($post, $typeAlias) {
        $post = HelpSetup::arraySanitization($post);
        $toDelete = array();
        $errMsgs = $this->_validateAndSanitizeSourceAliasesDelete($post, $toDelete, $typeAlias);

        foreach ($toDelete as $item) {
            $this->opalDB->markAsDeletedSourceAliases($item);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, $errMsgs);

        return false;
    }
}
