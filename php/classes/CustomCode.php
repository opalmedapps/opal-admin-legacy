<?php


class CustomCode extends OpalProject {

    /*
     * Get the list of all custom codes
     * */
    function getCustomCodes() {
        $results = $this->opalDB->getCustomCodes();
        return $results;
    }

    /*
     * gets the list of modules availables where adding custom codes
     * @params  void
     * @return  array of modules
     * */
    public function getAvailableModules() {
        return $this->opalDB->getAvailableModules();
    }

    /*
     * Insert a new custom codes after it was validated. If invalid, return an error 500
     * @params  $customCode (array) custom code to validate and insert.
     * @return  number of record inserted (should be one) or a code 500
     * */
    public function insertCustomCode($customCode) {
        $customCode = $this->arraySanitization($customCode);
        $moduleDetails = $this->opalDB->getModuleSettings($customCode["moduleId"]["value"]);

        $result = $this->_validateCustomCode($customCode, $moduleDetails);
        if(count($result) > 0)
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
     * Validate a custom code.
     * @params  $customCode(array in ref) custom code to validate
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
        if(count($moduleDetails["subModule"]) > 0) {
            foreach ($moduleDetails["subModule"] as $item) {
                if($item["ID"] == $customCode["type"]["ID"])
                    $typeFound = true;
            }
            if(!$typeFound)
                array_push($errMsgs, "Missing type.");
        }
        else {
            if(array_key_exists("type", $customCode) && array_key_exists("ID", $customCode["type"]))
                array_push($errMsgs, "Type should not be there.");
        }
        return $errMsgs;
    }

    function getCustomCodeDetails($customCodeId, $moduleId) {
        $results = $this->opalDB->getCustomCodeDetails($customCodeId, $moduleId);
        if($results["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");
        unset($results["masterSource"]);
        print_R($results);die();
        return $results;
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
     * @return  array $response : response
     */
    function deleteCustomCode($customCodeId, $moduleId) {
        return $this->opalDB->markCustomCodeAsDeleted($customCodeId, $moduleId);
    }
}