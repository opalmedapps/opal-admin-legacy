<?php

/**
 * Diagnosis class
 */
class Diagnosis extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_DIAGNOSIS_TRANSLATION, $guestStatus);
    }

    /*
     * Get the details of a specific diagnosis translation, including diagnosis codes and educational material if
     * needed.
     * @params  $diagnosisId : int - ID of the diagnosis translation to get the details
     * @return  $result : array - all the details of the diagnosis translation
     * */
    public function getDiagnosisTranslationDetails($post) {
        $post = HelpSetup::arraySanitization($post);
        $this->checkReadAccess($post);
        $diagnosisId = $post["serial"];
        $result = $this->opalDB->getDiagnosisDetails($diagnosisId);
        $result["diagnoses"] = $this->opalDB->getDiagnosisCodes($diagnosisId);
        $result["count"] = count($result["diagnoses"]);

        if ($result["eduMatSer"] != 0) {
            $result["eduMat"] = $this->_getEducationalMaterialDetails($result["eduMatSer"]);
        }

        return $result;
    }

    /*
     * Get the list of current diagnosis with the codes already assigned.
     * @params  void
     * @return  $resuts : array - list of current diagnostics with already assigned codes
     * */
    public function getDiagnoses() {
        $this->checkReadAccess();

        $assignedDB = $this->_getActiveSourceDatabase();
        $ad = $this->opalDB->getAssignedDiagnoses();
        $assignedDiagnoses = array();
        foreach($ad as $item) {
            $assignedDiagnoses[$item["sourceuid"]] = $item;
        }
        $results = $this->opalDB->getDiagnoses($assignedDB);
        foreach ($results as &$item) {
            $item["added"] = 0;
            if ($assignedDiagnoses[$item["sourceuid"]])
                $item['assigned'] = $assignedDiagnoses[$item["sourceuid"]];
        }

        return $results;
    }

    /*
     * Validate and sanitize the new diagnosis translation received from the user. Names and descriptions in french and
     * english are mandatory, as well a list of at least one diagnosis code. If an educational material is present,
     * it must must a valid one.
     * @params  $post : array - details of the diagnosis translation
     * @return  $validatedDiagnosis : array - validated and sanitized diagnosis translation
     * */
    protected function _validateAndSanitizeDiagnosis($post) {
        $post = HelpSetup::arraySanitization($post);
        $validatedDiagnosis = array();
        if(!$post["name_EN"] || !$post["name_FR"] || !$post["description_EN"] || !$post["description_FR"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing informations.");

        $validatedDiagnosis["name_EN"] = strip_tags($post["name_EN"]);
        $validatedDiagnosis["name_FR"] = strip_tags($post["name_FR"]);
        $validatedDiagnosis["description_EN"] = $post["description_EN"];
        $validatedDiagnosis["description_FR"] = $post["description_FR"];
        $validatedDiagnosis["eduMat"] = null;
        $validatedDiagnosis["diagnoses"] = array();

        if($post["serial"] && $post["serial"] != "")
            $validatedDiagnosis["serial"] = intval($post["serial"]);

        if($post['eduMat'] && isset($post["eduMat"]["serial"])) {
            $tempEdu = $this->opalDB->validateEduMaterialId($post["eduMat"]["serial"]);
            if($tempEdu["total"] != "1")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid educational material.");
            $validatedDiagnosis["eduMat"] = $post["eduMat"]["serial"];
        }

        if(!$post["diagnoses"] || !is_array($post["diagnoses"]) || count($post["diagnoses"]) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Diagnosis codes are missing.");

        foreach ($post["diagnoses"] as $item) {
            array_push($validatedDiagnosis["diagnoses"], array(
                "sourceuid"=>intval($item['sourceuid']),
                "code"=>$item['code'],
                "description"=>$item['description'],
            ));
        }

        return $validatedDiagnosis;
    }

    /*
     * Insert a new diagnosis translation after its sanitization and validation. It inserts (or replace) diagnosis
     * codes.
     * @params  $post : array - details of the diagnosis translation submitted by the user
     * @return  int - last Id inserted
     * */
    public function insertDiagnosisTranslation($post) {
        $this->checkWriteAccess($post);
        $validatedPost = $this->_validateAndSanitizeDiagnosis($post);

        $toInsert = array(
            "Name_EN"=>$validatedPost["name_EN"],
            "Name_FR"=>$validatedPost["name_FR"],
            "Description_EN"=>$validatedPost["description_EN"],
            "Description_FR"=>$validatedPost["description_FR"],
            "EducationalMaterialControlSerNum"=>$validatedPost['eduMat'],
        );

        $diagnosisId = $this->opalDB->insertDiagnosisTranslation($toInsert);
        $toInsert = array();

        foreach($validatedPost["diagnoses"] as $item) {
            array_push($toInsert, array(
                "DiagnosisTranslationSerNum"=>$diagnosisId,
                "SourceUID"=>$item['sourceuid'],
                "DiagnosisCode"=>$item['code'],
                "Description"=>$item['description'],
            ));
        }

        return $this->_insertDiagnosisCodes($validatedPost["diagnoses"], $diagnosisId);
    }

    /*
     * insert/updates the list of diagnosis codes.
     * @params  $codes : array - list of codes passed as reference
     *          $diagnosisId : int - diagnosis translation ID to associate the codes
     * @return  last id inserted.
     * */
    protected function _insertDiagnosisCodes(&$codes, &$diagnosisId) {
        $toInsert = array();
        foreach($codes as $item) {
            array_push($toInsert, array(
                "DiagnosisTranslationSerNum"=>$diagnosisId,
                "SourceUID"=>$item['sourceuid'],
                "DiagnosisCode"=>$item['code'],
                "Description"=>$item['description'],
            ));
        }

        return $this->opalDB->insertMultipleDiagnosisCodes($toInsert);
    }

    /*
     * get the list of all the diagnosis translations.
     * @params  void
     * @return  array - list of diagnosis translations.
     * */
    public function getDiagnosisTranslations() {
        $this->checkReadAccess();
        return $this->opalDB->getDiagnosisTranslations();
    }

    /*
     * Update the diagnosis translation. It validate and sanitize first the data. Then it updates the diagnosis
     * translation only if it was updated. Then it updates the list of diagnosis codes by first deleting the unused
     * codes, and the by inserting/replacing the remaining.
     * @params  $post : array - details of the diagnosis translation to update
     * @return
     * */
    public function updateDiagnosisTranslation($post) {
        $this->checkWriteAccess($post);
        $validatedPost = $this->_validateAndSanitizeDiagnosis($post);

        if(!$validatedPost["serial"] || $validatedPost["serial"] <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Diagnosis translation ID is missing.");

        $toUpdate = array(
            "DiagnosisTranslationSerNum"=>$validatedPost["serial"],
            "Name_EN"=>$validatedPost["name_EN"],
            "Name_FR"=>$validatedPost["name_FR"],
            "Description_EN"=>$validatedPost["description_EN"],
            "Description_FR"=>$validatedPost["description_FR"],
            "EducationalMaterialControlSerNum"=>$validatedPost["eduMat"],
        );

        $this->opalDB->updateDiagnosisTranslation($toUpdate);

        $existingSourceUIDs = array();
        foreach ($validatedPost["diagnoses"] as $diagnosis) {
            array_push($existingSourceUIDs, $diagnosis["sourceuid"]);
        }

        $this->opalDB->deleteDiagnosisCodes($validatedPost["serial"], $existingSourceUIDs);
        return $this->_insertDiagnosisCodes($validatedPost["diagnoses"], $validatedPost["serial"]);
    }

    /**
     * Delete a specific diagnosis translation and the diagnosis codes associated to.
     * @param $post : array - contains the ID of the diagnosis translation to delete.
     */
    public function deleteDiagnosisTranslation($post) {
        $this->checkDeleteAccess($post);
        $total = $this->opalDB->deleteAllDiagnosisCodes($post['serial']);
        $total += $this->opalDB->deleteDiagnosisTranslation($post['serial']);
    }

    /*
     * Get the list of educational materials available
     * @params  void
     * @return  array - List of educational materials
     * */
    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }

    /*
     * Get the list of diagnosis for a specific patient after validating the data. MRN and site name are mandatory. If
     * there is no source, ignore it. If there is a source, add it in the SQL as = if include value is 1 or absent, and
     * != if value is anthing else than 1. Start and end date use the proper value or current date if no value.
     *
     * @params  $post : array - contains the MRN of the patient, the site, source, include, start date and end date.
     * @return  array - contains all the diagnoses of a specific patient.
     * */
    public function getPatientDiagnoses($post) {
        $this->checkReadAccess();
        $post = HelpSetup::arraySanitization($post);
        if(!is_array($post))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Empty request.");

        if(!array_key_exists("mrn", $post) || $post["mrn"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Medical record number is missing.");
        if(!array_key_exists("site", $post) || $post["site"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Site is missing.");
        if(!array_key_exists("source", $post) || $post["source"] == "")
            $include = null;
        else
            $include = ((!array_key_exists("include", $post) || intval($post["include"]) == 1) ? "=" : "!=");

        if(array_key_exists("startDate", $post) && $post["startDate"] != "") {
            if(!HelpSetup::verifyDate($post["startDate"], false, 'Y-m-d'))
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid start date.");
            else
                $startDate = date("Y-m-d", $post["startDate"]);
        } else
            $startDate = SQL_CURRENT_DATE;

        if(array_key_exists("endDate", $post) && $post["endDate"] != "") {
            if(!HelpSetup::verifyDate($post["endDate"], false, 'Y-m-d'))
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid end date.");
            else
                $endDate = date("Y-m-d", $post["endDate"]);
        } else
            $endDate = SQL_CURRENT_DATE;

        return $this->opalDB->getPatientDiagnoses($post["mrn"], $post["site"], $post["source"], $include, $startDate, $endDate);
    }

    /*
     * Insert a patient diagnosis.
     * */
    public function insertPatientDiagnosis($post) {
        return $this->_replacePatientDiagnosis($post);
    }

    public function updatePatientDiagnosis($post) {
        return $this->_replacePatientDiagnosis($post, false);
    }

    protected function _replacePatientDiagnosis($post, $isInsert = true) {
        $this->checkWriteAccess($post);
        $patientSite = null;
        $source = null;

        $errCodes = $this->_validatePatientDiagnosis($post, $patientSite, $source);
        if($errCodes != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, $errCodes);

        $toInsert = array(
            "PatientSerNum"=>$patientSite["PatientSerNum"],
            "SourceDatabaseSerNum"=>$source["SourceDatabaseSerNum"],
            "DiagnosisAriaSer"=>$post["rowId"],
            "DiagnosisCode"=>$post["code"],
            "Description_EN"=>$post["descriptionEn"],
            "Description_FR"=>$post["descriptionFr"],
            "Stage"=>$post["stage"],
            "StageCriteria"=>$post["stageCriteria"],
        );

        if($isInsert)
            $toInsert["CreationDate"] = $post["creationDate"];

        $currentPatientDiagnosis = $this->opalDB->getPatientDiagnosisId($patientSite["PatientSerNum"], $source["SourceDatabaseSerNum"], $post["rowId"]);
        if(count($currentPatientDiagnosis) <= 1) {
            if(count($currentPatientDiagnosis) == 1) {
                $currentPatientDiagnosis = $currentPatientDiagnosis[0];
                $toInsert["DiagnosisSerNum"] = $currentPatientDiagnosis["DiagnosisSerNum"];
                return $this->opalDB->insertPatientDiagnosis($toInsert);
            }
            else
                return $this->opalDB->insertPatientDiagnosis($toInsert);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates patient diagnosis found.");
        return false;
    }

    /*
     * Validate a patient diagnosis on each field.
     * @params  $post : array - Contains the following information
     *                          mrn : Medical Record Number of the patient (mandatory)
     *                          site : Site acronym of the establishment (mandatory)
     *                          source : Source database of the diagnosis (mandatory)
     *                          rowId : External ID of the diagnosis (mandatory)
     *                          code : Diagnosis code (mandatory)
     *                          creationDate : creation date of the record (mandatory)
     *                          descriptionEn : english description of the diagnosis (mandatory)
     *                          stage : no idea, but its for Aria (optional)
     *                          stageCriteria : no idea, but its for Aria (optional)
     * @return  $errCode : int - error code.
     *          $patientSite : array (reference) - site info
     *          $source : array (reference) - source database
     * */
    protected function _validatePatientDiagnosis(&$post, &$patientSite, &$source) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        if(!array_key_exists("mrn", $post) || $post["mrn"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }
        if(!array_key_exists("site", $post) || $post["site"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        if(array_key_exists("mrn", $post) && $post["mrn"] != "" && array_key_exists("site", $post) && $post["site"] != "") {
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

        if(!array_key_exists("source", $post) || $post["source"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);
            if(count($source) != 1) {
                $source = array();
                $errCode = "1" . $errCode;
            }  else {
                $source = $source[0];
                $errCode = "0" . $errCode;
            }
        }

        if(!array_key_exists("rowId", $post) || $post["rowId"] == "") {
            $errCode = "1" . $errCode;
        }  else {
            $post["rowId"] = intval($post["rowId"]);
            $errCode = "0" . $errCode;
        }

        if(!array_key_exists("code", $post) || $post["code"] == "") {
            $errCode = "1" . $errCode;
        }
        else {
            $code = $this->opalDB->getDiagnosisCodeDetails($post["code"], $source["SourceDatabaseSerNum"], $post["rowId"]);
            if(count($code) != 1) {
                $errCode = "1" . $errCode;
            } else {
                $code = $code[0];
                $errCode = "0" . $errCode;
            }
        }

        if(!array_key_exists("creationDate", $post) || $post["creationDate"] == "" || !HelpSetup::verifyDate($post["creationDate"], false, "Y-m-d H:i:s")) {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        if(!array_key_exists("descriptionEn", $post) || $post["descriptionEn"] == "") {
            $errCode = "1" . $errCode;
        } else
            $errCode = "0" . $errCode;

        if(!array_key_exists("descriptionFr", $post)) {
            $errCode = "1" . $errCode;
        } else
            $errCode = "0" . $errCode;

        if(!array_key_exists("stage", $post))
            $post["stage"] = "";
        if(!array_key_exists("stageCriteria", $post))
            $post["stageCriteria"] = "";
        return bindec($errCode);
    }
}