<?php

/*
 * Study class objects and method
 * */

class Study extends OpalProject {

    /*
     * This function returns the list of available studies for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of studies
     * */
    public function getStudies() {
        return $this->opalDB->getStudiesList();
    }

    /*
     * Sanitize, validate and insert a new study into the database.
     * @params  $post (array) data received from the fron end.
     * @return  number of record inserted (should be one) or a code 500
     * */
    public function insertStudy($post) {
        $study = $this->arraySanitization($post);
        $result = $this->_validateStudy($study);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Study validation failed. " . implode(" ", $result));

        $toInsert = array(
            "code"=>$study["details"]["code"],
            "title"=>$study["details"]["title"],
            "investigator"=>$study["investigator"]["name"]
        );
        if($study["dates"]["start_date"])
            $toInsert["startDate"] = gmdate("Y-m-d", $study["dates"]["start_date"]);
        if($study["dates"]["end_date"])
            $toInsert["endDate"] = gmdate("Y-m-d", $study["dates"]["end_date"]);

        return $this->opalDB->insertStudy($toInsert);
    }

    /*
     * Validate a study structure. Returns an array of errors if data are missing or not correctly formatted.
     * @params  $study (array in ref) study to validate
     * @return  $errMsgs (array) the list of errors found in the validation.
     * */
    protected function _validateStudy(&$study) {
        $errMsgs = array();

        if(!$study["details"] || !$study["details"]["code"] || !$study["details"]["title"] || !$study["investigator"] || !$study["investigator"]["name"])
            array_push($errMsgs, "Missing study info.");

        if($study["dates"]) {
            if($study["dates"]["start_date"] && $study["dates"]["end_date"]) {
                if(!HelpSetup::isValidTimeStamp($study["dates"]["start_date"]) || (!HelpSetup::isValidTimeStamp($study["dates"]["end_date"])))
                    array_push($errMsgs, "Invalid date format.");
                if((int) $study["dates"]["end_date"] < (int) $study["dates"]["start_date"])
                    array_push($errMsgs, "Invalid date range.");
            }
            else if($study["dates"]["start_date"]) {
                if(!HelpSetup::isValidTimeStamp($study["dates"]["start_date"]))
                    array_push($errMsgs, "Invalid date format.");
            }
            else if($study["dates"]["end_date"]) {
                if(!HelpSetup::isValidTimeStamp($study["dates"]["end_date"]))
                    array_push($errMsgs, "Invalid date format.");
            }
        }

        return $errMsgs;
    }

}