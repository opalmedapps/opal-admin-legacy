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

        $this->_validateCustomCode($customCode);
        $toInsert = array(
            "code"=>strip_tags($customCode["details"]["code"]),
            "description"=>strip_tags($customCode["details"]["description"]),
            "source"=>LOCAL_SOURCE_ONLY,
        );

        return $this->opalDB->insertCustomCode($toInsert, $moduleDetails["ID"]);
    }

    protected function _validateCustomCode(&$customCode) {
        if($customCode["details"]["code"] == "" || $customCode["details"]["description"] == "" || $customCode["moduleId"]["value"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid custom code.");
    }
}