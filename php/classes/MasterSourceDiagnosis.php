<?php


class MasterSourceDiagnosis extends MasterSourceModule {

    /*
     * Get the list of all undeleted master diagnoses
     * @params  void
     * @return  array - List of master diagnoses
     * */
    public function getMasterSourceDiagnoses() {
        $this->checkReadAccess();
        return $this->opalDB->getMasterSourceDiagnoses();
    }

    public function insertMasterSourceDiagnoses($post) {
        $this->checkWriteAccess($post);
        $toInsert = array();
        $errMsgs = $this->_validateAndSanitizeMasterSourceDiagnoses($post, $toInsert);

        if(count($toInsert) > 0)
            $this->opalDB->insertMasterSourceDiagnoses($toInsert);

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, $errMsgs);

        return false;
    }

    public function isDiagnosisExists($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if(!array_key_exists("source", $post) || $post["source"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;
        if(!array_key_exists("externalId", $post) || $post["externalId"] == "" || !is_numeric($post["externalId"]))
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));

        $results = $this->opalDB->isMasterSourceDiagnosisExists($post["source"], $post["externalId"]);
        if(count($results) <= 0) return $results;
        else if (count($results) == 1) return $results[0];
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicated keys detected in the records. Please contact your administrator.");
    }

    /*
     * Validate and sanitize a list of diagnoses before an insert/update. Returns one array with proper data sanitized
     * and ready, and another array with list of invalid diagnoses.
     * @params  $post : array - $_POST content. Each entry must contains the following:
     *                          source : source database ID. See table SourceDatabase (mandatory)
     *                          externalID : external ID of the diagnosis in the source database (mandatory)
     *                          code : code of the diagnosis (mandatory)
     *                          description : description of the diagnosis (mandatory)
     *                          creationDate - creation date of the record in the source database (optional)
     *  @return $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errMsgs : array - contains the invalid entries with an error code.
     * */
    protected function _validateAndSanitizeMasterSourceDiagnoses(&$post, &$toInsert) {
        $errMsgs = array();
        $post = HelpSetup::arraySanitization($post);

        foreach ($post as $item) {

            $errCode = "";

            if(!array_key_exists("source", $item) || $item["source"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if(!array_key_exists("externalId", $item) || $item["externalId"] == "" || !is_numeric($item["externalId"]))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if(!array_key_exists("code", $item) || $item["code"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if(!array_key_exists("description", $item) || $item["description"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            if(array_key_exists("creationDate", $item) && $item["creationDate"] != "") {
                if(!HelpSetup::verifyDate($item["creationDate"], false, 'Y-m-d H:i:s'))
                    $errCode = "1" . $errCode;
                else {
                    $item["creationDate"] = date("Y-m-d H:i:s", $item["creationDate"]);
                    $errCode = "0" . $errCode;
                }
            } else {
                $item["creationDate"] = date("Y-m-d H:i:s");
                $errCode = "0" . $errCode;
            }

            $errCode = bindec($errCode);
            if($errCode == 0)
                array_push($toInsert, $item);
            else {
                $item["validation"] = $errCode;
                array_push($errMsgs, $item);
            }
        }
        return $errMsgs;
    }
}