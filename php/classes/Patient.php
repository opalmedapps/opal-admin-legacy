<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Patient class
 *
 */

class Patient extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PATIENT, $guestStatus);
    }

    /**
     * Update the list of patients with their publication
     * @param $post
     */
    public function updatePublishFlags($post) {
        $this->checkWriteAccess($post);
        HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePublishFlag($post);

        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($post["data"] as $item) {
            $this->opalDB->updatePatientPublishFlag($item["serial"], $item["transfer"]);
        }

    }

    /**
     * Validate a list of publication flags for patient.
     *
     * @param $post : array - Contains the following information
     *                          data : array - Contains the following information
     *                              serial : patient serial number (mandatory)
     *                              transfer : patient publish flag (mandatory)
     *
     *  1st bit invalid input format
     *
     * @return string - string to convert in int for error code
     */
    protected function _validatePublishFlag(&$post) {
        $errCode = "";
        if(is_array($post) && array_key_exists("data", $post) && is_array($post["data"])) {
            $errFound = false;
            foreach ($post["data"] as $item) {
                if(!array_key_exists("serial", $item) || $item["serial"] == "" || !array_key_exists("transfer", $item) || $item["transfer"] == "") {
                    $errFound = true;
                    break;
                }
            }
            if($errFound)
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode = "1";
        return $errCode;
    }

    /**
     * return the list of patients available
     * @return array - list of patients
     */
    public function getPatients() {
        $this->checkReadAccess();
        return $this->opalDB->getPatients();
    }

    /**
     * Get the last 20,000 patient activities entries
     * @return array
     */
    public function getPatientActivities() {
        $this->checkReadAccess();
        return $this->opalDB->getPatientActivityLog();
    }

    /**
     * Get patient's Firebase username searched by a site code and MRN
     *
     * @param $mrns : list of dictionaries containing patient's site codes and MRNs
     * @return array - list of the Firebase username(s) matching search
     */
    public function getPatientFirebaseUsername($mrns) {
        $this->checkReadAccess();
        $siteCode = $mrns[0]['site'];
        $mrn = $mrns[0]['mrn'];
        $usernames = $this->opalDB->getPatientFirebaseUsername($siteCode, $mrn);
        return isset($usernames[0]['username']) ? $usernames[0]['username'] : null;
    }

    /**
     * Validate the name search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pname : patient name
     *
     *  1st bit invalid patient name
     *
     * @return $errCode
     */
    protected function _validateName(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if(!array_key_exists("pname", $post) || $post["pname"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post : patient last name case insensitive
     * @return array : details for the given patient(s) matching search criteria
     * @error 422 with array (validation=>integer)
     */
    public function findPatientByName($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateName($post);
        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $results = $this->opalDB->getPatientName($post['pname']);
        $this->_findOtherMRNS($results);
        return $results;
    }

    /**
     * Validate the mrn search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pmrn : patient mrn
     *
     *  1st bit invalid patient mrn
     *
     * @return $errCode
     */
    protected function _validateMRN(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if(!array_key_exists("pmrn", $post) || $post["pmrn"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post : patient mrn
     * @return array : details for the given patient(s) matching search criteria
     *
     */
    public function findPatientByMRN($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateMRN($post);
        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $results = $this->opalDB->getPatientMRN($post['pmrn']);
        $this->_findOtherMRNS($results);
        return $results;
    }

    /**
     * Validate the ramq search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pramq : patient ramq
     *
     *  1st bit invalid patient ramq
     *
     * @return $errCode
     */
    protected function _validateRAMQ(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if(!array_key_exists("pramq", $post) || $post["pramq"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post : patient ramq
     * @return array : details for the given patient(s) matching search criteria
     *
     */
    public function findPatientByRAMQ($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateRAMQ($post);
        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $results = $this->opalDB->getPatientRAMQ($post['pramq']);
        $this->_findOtherMRNS($results);
        return $results;
    }

    /**
     * Search database for list of patient mrns for every patient in the input array
     *
     * @param &$data : array - List of the patient information which contains the following
     *                          psnum : patient serial number
     */
    protected function _findOtherMRNS(&$data) {
        foreach ($data as &$item)
            $item["MRN"] = $this->opalDB->getMrnPatientSerNum($item["psnum"]);
    }

    /**
     * Validate the input parameters for individual patient report
     *  1st bit psnum
     *  2nd bit diagnosis
     *  3rd bit appointments
     *  4th bit questionnaires
     *  5th bit educational material
     *  6th bit test results (legacy)
     *  7th bit patient test results
     *  8th bit notifications
     *  9th bit general
     *  10th bit clinical notes
     *  11th bit treating team messages
     *
     * @param $post array - mrn & featureList
     * @return $errCode
     */
    protected function _validatePatientReport(&$post) {
        $errCode = "";

        if(is_array($post)) {
            //bit 1
            if(!array_key_exists("psnum", $post) || $post["psnum"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 2
            if(!array_key_exists("diagnosis", $post) || $post["diagnosis"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 3
            if(!array_key_exists("appointments", $post) || $post["appointments"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 4
            if(!array_key_exists("questionnaires", $post) || $post["questionnaires"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 5
            if(!array_key_exists("education", $post) || $post["education"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 6
            if(!array_key_exists("testresults", $post) || $post["testresults"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 7
            if(!array_key_exists("pattestresults", $post) || $post["pattestresults"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 8
            if(!array_key_exists("notes", $post) || $post["notes"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 9
            if(!array_key_exists("general", $post) || $post["general"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 10
            if(!array_key_exists("clinicalnotes", $post) || $post["clinicalnotes"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 11
            if(!array_key_exists("treatingteam", $post) || $post["treatingteam"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        } else {
            $errCode = "11111111111";
        }
        return $errCode;
    }

    /**
     *  Generate the patient report given patient serial number & feature list
     * @param $post : array contains parameter to find
     * @return $resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        $resultArray = array();
        if($post['diagnosis'] === "true") {
            $resultArray["diagnosis"] = $this->opalDB->getPatientDiagnosisReport($post['psnum']);
        }
        if($post["appointments"] === "true") {
            $resultArray["appointments"] = $this->opalDB->getPatientAppointmentReport($post['psnum']);
        }
        if($post["questionnaires"] === "true") {
            $resultArray["questionnaires"] = $this->opalDB->getPatientQuestionnaireReport($post['psnum']);
        }
        if($post["education"] === "true") {
            $resultArray["education"] = $this->opalDB->getPatientEducMaterialReport($post['psnum']);
        }
        if($post["testresults"] === "true") {
            $resultArray["testresults"] = $this->opalDB->getPatientLegacyTestReport($post['psnum']);
        }
        if($post["pattestresults"] === "true") {
            $resultArray["pattestresults"] = $this->opalDB->getPatientTestReport($post['psnum']);
        }
        if($post["notes"] === "true") {
            $resultArray["notes"] = $this->opalDB->getPatientNotificationsReport($post['psnum']);
        }
        if($post["clinicalnotes"] === "true") {
            $resultArray["clinicalnotes"] = $this->opalDB->getPatientClinNoteReport($post['psnum']);
        }
        if($post["treatingteam"] === "true") {
            $resultArray["treatingteam"] = $this->opalDB->getPatientTxTeamReport($post['psnum']);
        }
        if($post["general"] === "true") {
            $resultArray["general"] = $this->opalDB->getPatientGeneralReport($post['psnum']);
        }
        return $resultArray;
    }

    /**
     * Validate the educational material search parameter for group reports
     *
     * @param $post : array - Contains the following information
     *                          matType : material type
     *
     *  1st bit invalid material type
     *
     * @return $errCode
     */
    protected function _validateEducType(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if (!array_key_exists("matType", $post) || $post["matType"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     *  Generate list of available educational materials from DB
     * @param $post : array - Contains the following information
     *                          matType : material type
     *
     *  1st bit invalid material type
     * @return array of educational materials
     */
    public function findEducationalMaterialOptions($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateEducType($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }
        return $this->opalDB->getEducMatOptions($post['matType']);
    }

    /**
     * Validate the educational material report parameters
     *
     * @param $post : array - Contains the following information
     *                          type : material type
     *                          name : material name
     *
     *  1st bit invalid material type
     *  2nd bit invalid material name
     *
     * @return $errCode
     */
    protected function _validateEducReport(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if(!array_key_exists("type", $post) || $post["type"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }

            if(!array_key_exists("name", $post) || $post["name"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        } else {
            $errCode = "11";
        }
        return $errCode;
    }

    /**
     * Generate educational materials group report
     *
     * @param $post : array - Contains the following information
     *                          type : material type
     *                          name : material name
     *
     *  1st bit invalid material type
     *  2nd bit invalid material name
     *
     *  @return array of educational material report
     */
    public function getEducationalMaterialReport($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateEducReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }
        return $this->opalDB->getEducMatReport($post['type'], $post['name']);
    }

    /**
     * Generate list of questionnaires available in DB
     * @return array
     */
    public function findQuestionnaireOptions() {
        $this->checkReadAccess();
        return $this->opalDB->getQstOptions();
    }

    /**
     * Validate the questionnaire name search parameter for group reports
     *
     * @param $post : array - Contains the following information
     *                          qstName : questionnaire name
     *
     *  1st bit invalid questionnaire name
     *
     * @return $errCode
     */
    protected function _validateQstReport(&$post) {
        $errCode = "";
        if(is_array($post)) {
            if(!array_key_exists("qstName", $post) || $post["qstName"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Generate questionnaires report given user selected qName
     *
     * @param $post : array - Contains the following information
     *                          qstName : questionnaire name
     *
     *  1st bit invalid questionnaire name
     *
     * @return array: questionnaire report JSON object
     */
    public function getQuestionnaireReport($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateQstReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }
        return $this->opalDB->getQstReport($post['qstName']);
    }

    /**
     *  Generate patient group report
     * @return array: patient group report JSON object
     */
    public function getPatientGroupReport() {
        $this->checkReadAccess();
        return $this->opalDB->getDemoReport();

    }


    /**
     * Validate search patient mandatory fields
     *
     * @params  $post : array - Contains the following information
     *                      mrn : Medical Record Number of the patient (mandatory)
     *                      site : Site acronym of the establishment (mandatory)
     *
     *  1st bit invalid site
     *  2nd bit invalid mrn
     *
     * @return $errCode
     */
    protected function _validatePatientExistParams($post) {

        $errCode = "";

        if(!array_key_exists("site", $post) || $post["site"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("mrn", $post) || $post["mrn"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Determines the existence of a patient
     *
     * @param string $site : Hospital Identifier Type
     * @param string $mrn : Hospital Identifier Value
     *
     *  1st bit invalid site
     *  2nd bit invalid mrn
     *  3nd bit invalid format
     *     * @return array $response : 0 / 1
     */
    public function checkPatientExist($post) {
        $errCode = "";
        $response = array(
            'status' => '',
        );

        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);

        $errCode = $this->_validatePatientExistParams($post) . $errCode;

        if(array_key_exists("mrn", $post)) {
            if(preg_match(REGEX_MRN, $post["mrn"])) {
                $mrn = str_pad($post["mrn"], 7, "0", STR_PAD_LEFT);
                $response['status'] = "Success";
                $errCode = "0" . $errCode;
                $patientSite = $this->opalDB->getPatientSite($mrn, $post["site"]);
                $response['data'] = boolval(count($patientSite));

            } else {
                $errCode = "1" . $errCode;
                $response['status'] = "Error";
                $response['message'] = "Invalid MRN";
            }
        }

        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        return $response;
    }

    /**
     * Validate patient demographics mandatory fields
     *
     * @params  $post : array - Contains the following information
     *                          mrns : List of patient identifiers
     *                              mrn : Medical Record Number of the patient (mandatory)
     *                              site : Site acronym of the establishment (mandatory)
     *                          ramq: Quebec Health Medical Number
     *                          birthdate : Date of birth
     *                          name : LastName and Firstname
     *
     *  1st bit invalid mrn / site
     *  2nd bit invalid ramq
     *  3rd bit date of birth
     *  4th bit name
     *
     * @return $errCode
     */
    protected function _validatePatientParams($post) {
        $errCode = "";

        if(!array_key_exists("mrns", $post) || $post["mrns"] == "" || count($post["mrns"]) <= 0)
            $errCode = "1" . $errCode;
        else {
            $invalidValue = false;
            foreach ($post["mrns"] as $identifier) {
                $invalidValue = !preg_match(REGEX_MRN, $identifier["mrn"]);
            }

            if($invalidValue) {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
        }
        if(!array_key_exists("ramq", $post))
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;


        if(!array_key_exists("birthdate", $post) || $post["birthdate"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("name", $post) || $post["name"] == "" || count($post["name"]) <= 0)
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(array_key_exists("language", $post)) {
            if(!in_array($post["language"], PATIENT_LANGUAGE_ARRAY))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode = "0" . $errCode;

        if(array_key_exists("gender", $post)) {
            if($post["gender"] != null && !in_array($post["gender"], PATIENT_SEX_ARRAY))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode = "0" . $errCode;

        $patientNotFound = true;
        $idList = $post["mrns"];
        $cptIDs = 0;
        $lenIDs = count($idList);

        // Looping patient Identifiers, if no patient found, then Patient does not exist with any identifiers
        while (($identifier = array_shift($idList)) !== NULL) {
            $mrn = str_pad($identifier["mrn"], 7, "0", STR_PAD_LEFT);
            $retrievedPatient = $this->opalDB->getPatientSite($mrn, $identifier["site"]);
            $patientNotFound = !boolVal(count($retrievedPatient)) && $patientNotFound;

            if($patientNotFound) {
                // Return element to Identifier List until Patient Id found
                $idList = array_merge($idList, array($identifier));
                $cptIDs = $cptIDs + 1;
            }

            // Patient does not exist with any identifiers
            if($cptIDs > $lenIDs) {
                break;
            }
        }

        // Patient does not exist with any identifiers
        if($patientNotFound)
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Update Patient information
     *
     * @params  $post : array - Contains the following information
     *                          mrns : List of patient identifiers
     *                              mrn : Medical Record Number of the patient (mandatory)
     *                              site : Site acronym of the establishment (mandatory)
     *                          ramq: Quebec Health Medical Number
     *                          birthdate : Date of birth
     *                          name : LastName and Firstname
     *
     *  1st bit invalid mrn / site
     *  2nd bit invalid ramq
     *  3rd bit date of birth
     *  4th bit name
     *
     * @return  $errCode : int - error code coded on bitwise operation. If 0, no error.
     * @throws Exception
     */
    public function updatePatient($post) {

        $this->checkWriteAccess($post);
        HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientParams($post);

        $errCode = bindec($errCode);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $idList = $post["mrns"];
        $toBeInsertPatientIds = array();
        $patientSerNum = "";
        $cptIDs = 0;
        $lenIDs = count($idList);

        // Looping patient Identifiers
        while (($identifier = array_shift($idList)) !== NULL) {
            $mrn = str_pad($identifier["mrn"], 7, "0", STR_PAD_LEFT);
            $retrievedPatient = $this->opalDB->getPatientSite($mrn, $identifier["site"]);

            if(count($retrievedPatient) == 1) {

                $patientIdArray = $retrievedPatient[0];

                // Entry defined in Identifier List
                $patientSerNum = $patientIdArray["PatientSerNum"];

                // Update entry status in Identifier List
                $patientIdArray["Is_Active"] = $identifier["active"];
            } else {
                // Entry not found in Identifier List
                if($patientSerNum == "") {
                    // Return element to Identifier List until Patient Id found
                    $idList = array_merge($idList, array($identifier));
                    $cptIDs = $cptIDs + 1;
                } else {
                    // Add new entry in Identifier List
                    $patientIdArray = array(
                        "PatientSerNum" => $patientSerNum,
                        "Hospital_Identifier_Type_Code" => $identifier["site"],
                        "MRN" => $mrn,
                        "Is_Active" => $identifier["active"]);
                }
            }

            // Add value for update
            if(!empty($patientIdArray)) {
                array_push($toBeInsertPatientIds, $patientIdArray);
            }

            // Patient does not exist with any identifiers
            if($cptIDs > $lenIDs) {
                break;
            }
        }

        // Get current patient demographic
        $patientData = $this->opalDB->getPatientSerNum($patientSerNum)[0];

        //Update patient demographics
        $patientData["PatientSerNum"] = $patientSerNum;
        $patientData["FirstName"] = $post["name"]["firstName"];
        $patientData["LastName"] = $post["name"]["lastName"];

        if(array_key_exists("birthdate", $post) && !empty($post["birthdate"])) {
            $patientData["DateOfBirth"] = $post["birthdate"];

            $from = new DateTime($patientData["DateOfBirth"]);
            $to = new DateTime('today');
            $age = $from->diff($to)->y;

            $patientData["Age"] = $age;
        }

        if(array_key_exists("alias", $post)) {
            $patientData["Alias"] = $post["alias"];
        }

        if(array_key_exists("gender", $post) && !empty($post["gender"])) {
            $patientData["Sex"] = $post["gender"];
        }

        $patientData["DeathDate"] = NULL;
        // Avoid the case when deceasedDateTime value is valid value
        if(array_key_exists("deceasedDateTime", $post) && (strtotime($post["deceasedDateTime"]) > 0)) {
            $patientData["StatusReasonTxt"] = "Deceased patient";
            $patientData["BlockedStatus"] = 1;
            $this->opalDB->updatePatientPublishFlag($patientSerNum, 0);
            $patientData["DeathDate"] = $post["deceasedDateTime"];
            $uid = $this->getPatientFirebaseUsername($post["mrns"]);
            $firebase = new FirebaseOpal();
            $firebase->disableUser($uid);
        }

        // Deal with the cases when deceasedDateTime values contain empty string, NULL, "0000-00-00" and some invalid date strings
        if(array_key_exists("deceasedDateTime", $post) && (strtotime($post["deceasedDateTime"]) <= 0)) {
            $patientData["StatusReasonTxt"] = "";
            $patientData["BlockedStatus"] = 0;
            $patientData["DeathDate"] = $post["deceasedDateTime"];
            $this->opalDB->updatePatientPublishFlag($patientSerNum, 1);
        }

        unset($patientData["LastUpdated"]);

        if(count($toBeInsertPatientIds) > 0) {
            while(($identifier = array_shift($toBeInsertPatientIds)) !== NULL) {
                if (!empty($identifier["Patient_Hospital_Identifier_Id"])) {
                    $this->opalDB->updatePatientLink($identifier);
                } else {
                    $this->opalDB->insertPatientLink($identifier);
                }
            }
        }

        $this->opalDB->updatePatient($patientData);

        return false;
    }

}
