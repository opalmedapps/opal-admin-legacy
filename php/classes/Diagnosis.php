<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

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
        $result["deactivated"] = $this->opalDB->getdeactivatedDiagnosesCodes($diagnosisId);
        $result["count"] = count($result["diagnoses"]);

        if ($result["eduMatSer"] != 0) {
            $result["eduMat"] = $this->_getEducationalMaterialDetails($result["eduMatSer"]);
        }

        return $result;
    }

    /*
     * Get the list of current diagnosis with the codes already assigned.
     * @params  void
     * @return  $results : array - list of current diagnostics with already assigned codes
     * */
    public function getDiagnoses() {
        $this->checkReadAccess();

        $assignedDB = $this->_getActiveSourceDatabase();
        $ad = $this->opalDB->getAssignedDiagnoses();
        $assignedDiagnoses = array();
        foreach($ad as $item) {
            $assignedDiagnoses[$item["SourceUID"]] = $item;
        }
        $results = $this->opalDB->getDiagnoses($assignedDB);

        foreach ($results as &$item) {
            $item["added"] = 0;
            if ($assignedDiagnoses[$item["ID"]])
                $item['assigned'] = $assignedDiagnoses[$item["ID"]];
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
        $listDiagnoses = array();
        $validatedDiagnosis = array();
        if(!$post["name_EN"] || !$post["name_FR"] || !$post["description_EN"] || !$post["description_FR"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing information.");

        $validatedDiagnosis["name_EN"] = strip_tags($post["name_EN"]);
        $validatedDiagnosis["name_FR"] = strip_tags($post["name_FR"]);
        $validatedDiagnosis["description_EN"] = $post["description_EN"];
        $validatedDiagnosis["description_FR"] = $post["description_FR"];
        $validatedDiagnosis["eduMat"] = null;
        $validatedDiagnosis["diagnoses"] = array();

        if($post["serial"] && $post["serial"] != "")
            $validatedDiagnosis["serial"] = intval($post["serial"]);

        if($post['eduMat'] && isset($post["eduMat"])) {
            $post["eduMat"] = intval($post["eduMat"]);
            $tempEdu = $this->opalDB->validateEduMaterialId($post["eduMat"]);
            if($tempEdu["total"] != "1")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid educational material.");
            $validatedDiagnosis["eduMat"] = $post["eduMat"];
        }

        if(!$post["diagnoses"] || !is_array($post["diagnoses"]) || count($post["diagnoses"]) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Diagnosis codes are missing.");

        foreach ($post["diagnoses"] as $item)
            array_push($listDiagnoses, intval($item));

        $diagCode = $this->opalDB->getListDiagnosisCodes($listDiagnoses);
        if(count($diagCode) != count($post["diagnoses"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid diagnosis codes list.");

        $validatedDiagnosis["diagnoses"] = $diagCode;

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
            'DiagnosisCode' => '',
        );

        $diagnosisId = $this->opalDB->insertDiagnosisTranslation($toInsert);

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
                "SourceUID"=>$item['ID'],
                "DiagnosisCode"=>$item['code'],
                "Description"=>$item['description'],
                "Source"=>$item['source'],
            ));
        }

        return $this->opalDB->insertMultipleDiagnosisCodes($toInsert);
    }

    /**
     * get the list of all the diagnosis translations.
     * @param  void
     * @return  array - list of diagnosis translations.
     */
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
        foreach ($validatedPost["diagnoses"] as $diagnosis)
            array_push($existingSourceUIDs, $diagnosis["ID"]);

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
        return $total;
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
     * != if value is anything else than 1. Start and end date use the proper value or current date if no value.
     *
     * @params  $post : array - contains the MRN of the patient, the site, source, include, start date and end date.
     * @return  array - contains all the diagnoses of a specific patient.
     * */
    public function getPatientDiagnoses($post) {
        $this->checkReadAccess();
        $include = $startDate = $endDate = "";
        $errCode = $this->_validatePatientInfo($post, $include, $startDate, $endDate);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>$errCode)));

        return $this->opalDB->getPatientDiagnoses($post["mrn"], $post["site"], $post["source"], $include, $startDate, $endDate);
    }

    /*
     * Validate patient info before getting his/her diagnosis.
     * @params  $post : array - Contains the following information
     *                          mrn : Medical Record Number of the patient (mandatory)
     *                          site : Site acronym of the establishment (mandatory)
     *                          source : Source database of the diagnosis (optional)
     *                          include : if 0 exclude (!=). If 1, include(=) (optional, default 1)
     *                          startDate : starting date (optional, default today date)
     *                          endDate : ending date (optional, default today date)
     * @return  $errCode : int - error code coded on bitwise operation. If 0, no error.
     *          $include : string (reference) - include sign (= or !=)
     *          $startDate : string (reference) - validated starting date
     *          $endDate : string (reference) - validated ending date
     * */
    protected function _validatePatientInfo(&$post, &$include, &$startDate, &$endDate) {
        $errCode = "";
        $post = HelpSetup::arraySanitization($post);

        if(!array_key_exists("mrn", $post) || $post["mrn"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;
        if(!array_key_exists("site", $post) || $post["site"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;
        if(!array_key_exists("source", $post) || $post["source"] == "")
            $include = null;
        else
            $include = ((!array_key_exists("include", $post) || intval($post["include"]) == 1) ? "=" : "!=");

        if(array_key_exists("startDate", $post) && $post["startDate"] != "") {
            if(!HelpSetup::verifyDate($post["startDate"], false, 'Y-m-d'))
                $errCode = "1" . $errCode;
            else {
                $startDate = date("Y-m-d", strtotime($post["startDate"]));
                $errCode = "0" . $errCode;
            }
        } else {
            $errCode = "0" . $errCode;
            $startDate = SQL_CURRENT_DATE;
        }

        if(array_key_exists("endDate", $post) && $post["endDate"] != "") {
            if(!HelpSetup::verifyDate($post["endDate"], false, 'Y-m-d'))
                $errCode = "1" . $errCode;
            else {
                $endDate = date("Y-m-d", strtotime($post["endDate"]));
                $errCode = "0" . $errCode;
            }
        } else {
            $errCode = "0" . $errCode;
            $endDate = SQL_CURRENT_DATE;
        }
        return bindec($errCode);
    }

    /*
     * Insert a patient diagnosis.
     * @params  $post : array - details of the patient diagnosis to insert.
     * @return  int : last entered diagnosis ID.
     * */
    public function insertPatientDiagnosis($post) {
        return $this->_replacePatientDiagnosis($post);
    }

    /*
     * Insert a patient diagnosis.
     * @params  $post : array - details of the patient diagnosis to update.
     * @return  int : number of array modified.
     * */
    public function updatePatientDiagnosis($post) {
        return $this->_replacePatientDiagnosis($post);
    }

    /*
     * This function insert or update a patient diagnosis after its validation.
     * @params  $post : array - details of the patient diagnosis to insert/update.
     * @return  int : number of array modified or ID of last entered diagnosis.
     * */
    protected function _replacePatientDiagnosis($post) {
        $this->checkWriteAccess($post);
        $patientSite = null;
        $source = null;

        $errCode = $this->_validatePatientDiagnosis($post, $patientSite, $source);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>$errCode)));

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

        $toInsert["CreationDate"] = $post["creationDate"];

        $currentPatientDiagnosis = $this->opalDB->getPatientDiagnosisId($patientSite["PatientSerNum"], $source["SourceDatabaseSerNum"], $post["rowId"]);
        if(count($currentPatientDiagnosis) <= 1) {
            if(count($currentPatientDiagnosis) == 1) {
                $currentPatientDiagnosis = $currentPatientDiagnosis[0];
                $toInsert["DiagnosisSerNum"] = $currentPatientDiagnosis["DiagnosisSerNum"];
            }
            return $this->opalDB->replacePatientDiagnosis($toInsert);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates patient diagnosis found.");
        return false;
    }

    /*
     * Delete a specific patient diagnosis.
     * @params  $post : array - contains the following info:
     *                          mrn : Medical Record Number of the patient (mandatory)
     *                          site : Site acronym of the establishment (mandatory)
     *                          source : Source database of the diagnosis (mandatory)
     *                          rowId : External ID of the diagnosis (mandatory)
     * @return  int - number of records deleted
     * */
    public function deletePatientDiagnosis($post) {
        $patientSite = null;
        $source = null;
        $this->checkDeleteAccess($post);
        $post = HelpSetup::arraySanitization($post);

        if (is_array($post))
            $errCode = $this->_validatePatientSourceExternalId($post, $patientSite, $source);
        else
            $errCode = 31;

        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>$errCode)));

        $currentPatientDiagnosis = $this->opalDB->getPatientDiagnosisId($patientSite["PatientSerNum"], $source["SourceDatabaseSerNum"], $post["rowId"]);
        if(count($currentPatientDiagnosis) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates patient diagnosis found.");
        else if(count($currentPatientDiagnosis) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>32)));
        $currentPatientDiagnosis = $currentPatientDiagnosis[0];
        $this->opalDB->deletePatientDiagnosis($currentPatientDiagnosis["DiagnosisSerNum"]);
    }

    /*
     * Validate basic information of a specific patient and source.
     * @params  $post : array - Contains the following information
     *                          mrn : Medical Record Number of the patient (mandatory)
     *                          site : Site acronym of the establishment (mandatory)
     *                          source : Source database of the diagnosis (mandatory)
     *                          rowId : External ID of the diagnosis (mandatory)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 5 bits (value from 0 to 31). Bit information
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     *                      4: source invalid or missing
     *                      5: rowId invalid or missing
     * @return  $errCode : int - error code.
     *          $patientSite : array (reference) - site info
     *          $source : array (reference) - source database
     * */
    protected function _validatePatientSourceExternalId(&$post, &$patientSite, &$source) {
        $patientSite = array();
        $errCode = $this->_validateBasicPatientInfo($post, $patientSite);

        // 4th bit - source
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

        // 5th bit - external ID
        if(!array_key_exists("rowId", $post) || $post["rowId"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;
        return $errCode;
    }

    /*
     * Validate a patient diagnosis on each field.
     * @params  $post : array - Contains the following information
     *                          mrn : Medical Record Number of the patient (mandatory)
     *                          site : Site acronym of the establishment (mandatory)
     *                          source : Source database of the diagnosis (mandatory)
     *                          externalId : External ID of the diagnosis code (mandatory)
     *                          rowId : External ID of the diagnosis code (mandatory)
     *                          code : Diagnosis code (mandatory)
     *                          creationDate : creation date of the record (mandatory)
     *                          descriptionEn : english description of the diagnosis (mandatory)
     *                          descriptionFr : french description of the diagnosis (optional)
     *                          stage : no idea, but its for Aria (optional)
     *                          stageCriteria : no idea, but its for Aria (optional)
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 11 bits (value from 0 to 2047). Bit information
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site does not exists
     *                      4: source invalid or missing
     *                      5: rowId invalid or missing
     *                      6: externalId of the diagnosis code invalid or missing
     *                      7: diagnosis code invalid or missing
     *                      8: source diagnosis cannot be found
     *                      9: creationDate invalid or missing
     *                      10: descriptionEn invalid or missing
     *                      11: descriptionFr invalid
     * @return  $errCode : int - error code.
     *          $patientSite : array (reference) - site info
     *          $source : array (reference) - source database
     * */
    protected function _validatePatientDiagnosis(&$post, &$patientSite, &$source) {
        $post = HelpSetup::arraySanitization($post);

        if (is_array($post)) {
            $errCode = $this->_validatePatientSourceExternalId($post, $patientSite, $source);
            // 6th bit - externalId of the diagnosis code
            if (!array_key_exists("externalId", $post) || $post["externalId"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 7th bit - diagnosis code
            if (!array_key_exists("code", $post) || $post["code"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 8th bit - the combo code/source/ExternalId exists as a master source diagnosis
            if (bindec($errCode) != 0)
                $errCode = "1" . $errCode;
            else {
                $code = $this->opalDB->getDiagnosisCodeDetails($post["code"], $source["SourceDatabaseName"], $post["externalId"]);
                if (count($code) != 1)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }

            // 9th bit - creation date
            if (!array_key_exists("creationDate", $post) || $post["creationDate"] == "" || !HelpSetup::verifyDate($post["creationDate"], false, "Y-m-d H:i:s"))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 10th bit - description EN
            if (!array_key_exists("descriptionEn", $post) || $post["descriptionEn"] == "") {
                $errCode = "1" . $errCode;
            } else
                $errCode = "0" . $errCode;

            // 11th bit - description FR
            if (!array_key_exists("descriptionFr", $post)) {
                $errCode = "1" . $errCode;
            } else
                $errCode = "0" . $errCode;

            if (!array_key_exists("stage", $post))
                $post["stage"] = "";
            if (!array_key_exists("stageCriteria", $post))
                $post["stageCriteria"] = "";
        } else
            $errCode = "11111111111";

        return bindec($errCode);
    }
}
