<?php


class CustomCode extends OpalProject {

    /*
     * Get the list of all custom codes
     * */
    function getCustomCodes() {
        $results = $this->opalDB->getCustomCodes();
        //print_r($results);
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
        if($moduleDetails["ID"] == "" || $moduleDetails["customCode"] != "1")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid module.");

        $this->_validateCustomCode($customCode);
        $toInsert = array(
            "ID"=>strip_tags($customCode["moduleId"]["value"]),
            "code"=>strip_tags(["details"]["code"]),
            "description"=>strip_tags(["details"]["description"]),
        );


        print_r($customCode);
        print_r($toInsert);
        die();

    }

    protected function _validateCustomCode(&$customCode) {
        if($customCode["details"]["code"] == "" || $customCode["details"]["description"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");
    }
}