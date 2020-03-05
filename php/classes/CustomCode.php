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

    protected function _validateCustomCode(&$customCode, &$moduleDetails) {
        $errMsgs = array();                                             //By default, no error message
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
}