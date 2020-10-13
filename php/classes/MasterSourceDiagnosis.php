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
        $toInsert = array();
        $this->checkWriteAccess($post);

        $post = HelpSetup::arraySanitization($post);

        $errMsgs = $this->_validateAndSanitizeMasterSourceDiagnoses($post, $toInsert);

        if(count($toInsert) > 0) {
            $this->opalDB->insertMasterSourceDiagnoses($toInsert);
        }

        if(count($errMsgs) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode($errMsgs));

        return false;
    }

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
                $item["error"] = $errCode;
                array_push($errMsgs, $item);
            }
        }
        return $errMsgs;
    }
}