<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

class CustomCode extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_CUSTOM_CODE, $guestStatus);
    }

    /*
     * Get the list of all custom codes
     * */
    function getCustomCodes() {
        $this->checkReadAccess();
        $results = $this->opalDB->getCustomCodes();
        return $results;
    }

    protected function _isCodeExists($tableName, $code, $description) {
        $result = $this->opalDB->getCountCustomCodes($tableName, $code, $description);

        if(intval($result["locked"]) == 0)
            return false;
        else
            return true;
    }

    /*
     * gets the list of modules availables where adding custom codes
     * @params  void
     * @return  array of modules
     * */
    public function getAvailableModules() {
        $this->checkReadAccess();
        return $this->opalDB->getAvailableModules();
    }

    /*
     * Insert a new custom codes after it was validated. If invalid, return an error 500
     * @params  $customCode (array) custom code to validate and insert.
     * @return  number of record inserted (should be one) or a code 500
     * */
    public function insertCustomCode($customCode) {
        $this->checkWriteAccess($customCode);
        $customCode = HelpSetup::arraySanitization($customCode);
        $moduleDetails = $this->opalDB->getModuleSettings($customCode["moduleId"]["value"]);

        $result = $this->_validateCustomCode($customCode, $moduleDetails);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Custom code validation failed. " . implode(" ", $result));

        $toInsert = array(
            "code"=>strip_tags($customCode["details"]["code"]),
            "description"=>strip_tags($customCode["details"]["description"]),
            "source"=>LOCAL_SOURCE_ONLY,
        );
        if(array_key_exists("type", $customCode) && array_key_exists("ID", $customCode["type"]))
            $toInsert["type"] = $customCode["type"]["ID"];

        return $this->opalDB->insertCustomCode($toInsert, $moduleDetails["ID"]);
    }

    /*
     * Update custom codes after it was validated. If invalid, return an error 500
     * @params  $customCode (array) custom code to validate and update.
     * @return  number of record inserted (should be one) or a code 500
     * */
    public function updateCustomCode($customCode) {
        $this->checkWriteAccess($customCode);
        $customCode = HelpSetup::arraySanitization($customCode);
        if(!array_key_exists("ID", $customCode) || $customCode["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot identify the custom code");

        $details = $this->_getCustomCodeDetails($customCode["ID"], $customCode["moduleId"]["value"]);

        if($details["locked"] > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Custom code is in use and cannot be modified.");

        if($details["source"] > LOCAL_SOURCE_ONLY)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot modify a code from an outside source.");

        if($details["code"] == $customCode["details"]["code"] && $details["description"] == $customCode["details"]["description"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "No change detected.");

        $moduleDetails = $this->opalDB->getModuleSettings($customCode["moduleId"]["value"]);

        $result = $this->_validateCustomCode($customCode, $moduleDetails);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Custom code validation failed. " . implode(" ", $result));

        $toUpdate = array(
            "ID"=>strip_tags($customCode["ID"]),
            "code"=>strip_tags($customCode["details"]["code"]),
            "description"=>strip_tags($customCode["details"]["description"]),
        );

        return $this->opalDB->updateCustomCode($toUpdate, $moduleDetails["ID"]);
    }

    /*
     * Validate a custom code.
     * @params  $customCode (array in ref) custom code to validate
     *          $moduleDetails (array in ref) details of the module to which the custom code is associated
     * @return  $errMsgs (array) the list of errors found in the validation.
     * */
    protected function _validateCustomCode(&$customCode, &$moduleDetails) {
        $errMsgs = array();
        $typeFound = false;

        if($customCode["details"]["code"] == "" || $customCode["details"]["description"] == "" || $customCode["moduleId"]["value"] == "") {
            array_push($errMsgs, "Missing custom code info.");
        }

        $moduleDetails["subModule"] = json_decode($moduleDetails["subModule"], true);
        if(is_array($moduleDetails["subModule"]) &&  count($moduleDetails["subModule"]) > 0) {
            foreach ($moduleDetails["subModule"] as $item) {
                if($item["ID"] == $customCode["type"]["ID"]) {
                    $typeFound = true;
                }
            }
            if(!$typeFound)
                array_push($errMsgs, "Missing type.");
        }
        else {
            if(array_key_exists("type", $customCode) && array_key_exists("ID", $customCode["type"]))
                array_push($errMsgs, "Type should not be there.");
        }

        if($this->_isCodeExists($moduleDetails["masterSource"], $customCode["details"]["code"], $customCode["details"]["description"]))
            array_push($errMsgs, "Code already exists.");

        return $errMsgs;
    }

    /*
     * Return the custom code details base on its ID and which module it is attached.
     *
     * @param   $customCodeId (int) ID of the custom code
     *          $moduleId (int) ID of the module to which is associated the custom code
     * @return  array : details of the custom code
     * */
    protected function _getCustomCodeDetails($customCodeId, $moduleId) {
        $customCodeId = intval($customCodeId);
        $moduleId = intval($moduleId);
        $results = $this->opalDB->getCustomCodeDetails($customCodeId, $moduleId);
        if($results["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");
        unset($results["masterSource"]);
        return $results;
    }

    function getCustomCodeDetailsAPI($customCodeId, $moduleId) {
        $this->checkReadAccess(array($customCodeId, $moduleId));
        return $this->_getCustomCodeDetails($customCodeId, $moduleId);
    }

    /**
     * Mark a custom code as being deleted. First, it checks if the code is valid, is not locked or an imported code
     * from another source DB. Then it marks the code has being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the customCode table! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked and the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its
     * records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param   $customCodeId (ID of the question)
     * @return  boolean : response
     */
    function deleteCustomCode($customCodeId, $moduleId) {
        $this->checkDeleteAccess(array($customCodeId, $moduleId));
        $details = $this->opalDB->getCustomCodeDetails($customCodeId, $moduleId);
        if($details["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");
        if($details["locked"] != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Custom code is locked.");

        return $this->opalDB->markCustomCodeAsDeleted($customCodeId, $details["masterSource"]);
    }
}