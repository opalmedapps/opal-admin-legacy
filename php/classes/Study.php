<?php

/*
 * Study class objects and method
 * */

class Study extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_STUDY, $guestStatus);
    }

    /*
     * This function returns the list of available studies for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of studies
     * */
    public function getStudies() {
        $this->checkReadAccess();
        return $this->opalDB->getStudiesList();
    }

    /*
     * Sanitize, validate and insert a new study into the database.
     * @params  $post (array) data received from the fron end.
     * @return  number of record inserted (should be one) or a code 500
     * */
    public function insertStudy($post) {
        $this->checkWriteAccess($post);
        $study = HelpSetup::arraySanitization($post);
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

    /*
     * Get the details of a study
     * @params  $studyId (int) ID of the study
     * @return  (array) details of the study
     * */
    public function getStudyDetails($studyId) {
        $this->checkReadAccess($studyId);
        return $this->opalDB->getStudyDetails(intval($studyId));
    }

    /*
     * Update a study after it is sanitized and validated.
     * @params  $post (array) details of the study.
     * @return  (int) number of record updated (should be one!) or an error 500
     * */
    public function updateStudy($post) {
        $this->checkWriteAccess($post);
        $study = HelpSetup::arraySanitization($post);
        $result = $this->_validateStudy($study);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Study validation failed. " . implode(" ", $result));

        if(!array_key_exists("ID", $study) || $study["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot identify the study.");

        $currentStudy = $this->opalDB->getStudyDetails(intval($study["ID"]));
        if(!$currentStudy["ID"] || $currentStudy["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot identify the study.");

        $toUpdate = array(
            "ID"=>$study["ID"],
            "code"=>$study["details"]["code"],
            "title"=>$study["details"]["title"],
            "investigator"=>$study["investigator"]["name"]
        );
        if($study["dates"]["start_date"])
            $toUpdate["startDate"] = gmdate("Y-m-d", $study["dates"]["start_date"]);
        else
            $toUpdate["startDate"] = null;
        if($study["dates"]["end_date"])
            $toUpdate["endDate"] = gmdate("Y-m-d", $study["dates"]["end_date"]);
        else
            $toUpdate["endDate"] = null;

        return $this->opalDB->updateStudy($toUpdate);
    }

    /**
     * Mark a study as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the study table! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked and the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its
     * records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params  $studyId (ID of the study)
     * @return  (int) number of record marked or error 500 if an error occurred.
     */
    function deleteStudy($studyId) {
        $this->checkDeleteAccess($studyId);
        $currentStudy = $this->opalDB->getStudyDetails(intval($studyId));
        if(!$currentStudy["ID"] || $currentStudy["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Study not found.");

        return $this->opalDB->markStudyAsDeleted($studyId);
    }
}