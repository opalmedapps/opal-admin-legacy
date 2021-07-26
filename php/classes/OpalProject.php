<?php


abstract class OpalProject
{
    protected $opalDB;

    protected function _insertAudit($module, $method, $arguments, $access, $username = false) {
        $toInsert = array(
            "module"=>$module,
            "method"=>$method,
            "argument"=>json_encode($arguments),
            "access"=>$access,
            "ipAddress"=>HelpSetup::getUserIP(),
        );
        if($username) {
            $toInsert["createdBy"] = $username;
            $this->opalDB->insertAuditForceUser($toInsert);
        }
        else
            $this->opalDB->insertAudit($toInsert);
    }

    /*
 * Get the list of educational materials. Protected function so any module can call it the same way when needed
 * without having to call the module educational materials itself, but cannot be called from outside.
 * @params  void
 * @return  $result - array - list of educational materials
 * */
    protected function _getListEduMaterial() {
        $results = $this->opalDB->getEducationalMaterial();
        foreach($results as &$row) {
            $row["tocs"] = $this->opalDB->getTocsContent($row["serial"]);
        }

        return $results;
    }

    /*
     * Get the details of aneducational material. Protected function so any module can call it the same way when needed
     * without having to call the module educational materials itself, but cannot be called from outside.
     * @params  void
     * @return  $result - array - list of educational materials
     * */
    protected function _getEducationalMaterialDetails($eduId) {
        $results = $this->opalDB->getEduMaterialDetails($eduId);
        $results["tocs"] = $this->opalDB->getTocsContent($results["serial"]);
        return $results;
    }

    /*
     * Get the activate source database (Aria, ORMS, local, etc...)
     * @params  void
     * @return  $assignedDB : array - source database ID
     * */
    protected function _getActiveSourceDatabase(){
        $assigned = $this->opalDB->getActiveSourceDatabase();
        $assigned = HelpSetup::arraySanitization($assigned);
        $assignedDB = array();
        foreach($assigned as $item) {
            array_push($assignedDB, $item["SourceDatabaseSerNum"]);
        }
        return $assignedDB;
    }

    /**
     * Validate basic information info of patient and site and make sure they exist
     * @param $post - contain MRN and site to validate
     * @param $patientSite - hospital site info
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit informations
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     * @return string - validation code in binary
     */
    protected function _validateBasicPatientInfo(&$post, &$patientSite) {
        $errCode = "";

        // 1st bit - MRN
        if(!array_key_exists("mrn", $post) || $post["mrn"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 2nd bit - Site
        if(!array_key_exists("site", $post) || $post["site"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 3rd bit - MRN and site combo must exists
        if(bindec($errCode) != 0) {
            $patientSite = array();
            $errCode = "1" . $errCode;
        } else {
            $patientSite = $this->opalDB->getPatientSite($post["mrn"], $post["site"]);
            if(count($patientSite) != 1) {
                $patientSite = array();
                $errCode = "1" . $errCode;
            }
            else {
                $patientSite = $patientSite[0];
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }
}