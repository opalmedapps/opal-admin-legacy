<?php

/*
 * Study class objects and method
 * */

class Study extends Module {

    protected $questionnaireDB;

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_STUDY, $guestStatus);
    }

    /*
     * This function connects to the questionnaire database if needed
     * @params  $OAUserId (ID of the user)
     * @returns None
     * */
    protected function _connectQuestionnaireDB() {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            false
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    public function getResearchPatient() {
        $this->checkReadAccess();
        $this->_connectQuestionnaireDB();
        return $this->questionnaireDB->getResearchPatient();
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
        $this->_connectQuestionnaireDB();
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateStudy($post);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $toInsert = array(
            "code"=>$post["code"],
            "title_EN"=>$post["title_EN"],
            "title_FR"=>$post["title_FR"],
            "description_EN"=>$post["description_EN"],
            "description_FR"=>$post["description_FR"],
            "investigator"=>$post["investigator"],
            "phone"=>$post["investigator_phone"],
            "email"=>$post["investigator_email"],
            "consentQuestionnaireId"=>$post["consent_form"]
        );
        if(array_key_exists("investigator_phoneExt", $post) && $post["investigator_phoneExt"] != "")
            $toInsert["phoneExt"] = $post["investigator_phoneExt"];
        if(array_key_exists("start_date", $post) && $post["start_date"] != "")
            $toInsert["startDate"] = gmdate("Y-m-d", $post["start_date"]);
        if(array_key_exists("end_date", $post) && $post["end_date"] != "")
            $toInsert["endDate"] = gmdate("Y-m-d", $post["end_date"]);

        $newStudyId = $this->opalDB->insertStudy($toInsert);

        if(array_key_exists("patients", $post) && is_array($post["patients"]) && count($post["patients"]) > 0) {
            $toInsertMultiple = array();
            foreach ($post["patients"] as $patient)
                array_push($toInsertMultiple, array("patientId"=>$patient, "studyId"=>$newStudyId, "consentStatus"=>1)); //default invited when patient is added to study
            $result = $this->opalDB->insertMultiplePatientsStudy($toInsertMultiple);
        }

        if(array_key_exists("questionnaire", $post) && is_array($post["questionnaire"]) && count($post["questionnaire"]) > 0) {
            $toInsertMultiple = array();
            foreach ($post["questionnaire"] as $questionnaire)
                array_push($toInsertMultiple, array("questionnaireId"=>$questionnaire, "studyId"=>$newStudyId));
            $result = $this->opalDB->insertMultipleQuestionnairesStudy($toInsertMultiple);
        }
    }

    /*
     * Validate and sanitize a study.
     * @params  $post : array - data for the study to validate
     *          $isAnUpdate : array - if the validation must include the ID of the study or not
     * Validation code :    Error validation code is coded as an int of 12 bits (value from 0 to 4095). Bit informations
     *                      are coded from right to left:
     *                      1: study code missing
     *                      2: english title missing
     *                      3: french title missing
     *                      4: english description missing
     *                      5: french description missing
     *                      6: investigator name missing
     *                      7: investigator phone missing
     *                      8: investigator email missing
     *                      9: start date (if present) invalid
     *                      10: end date (if present) invalid
     *                      11: date range (if start date and end date exist) invalid
     *                      12: patient list (if exists) invalid
     *                      13: questionnaire list (if exists) invalid
     *                      14: consent_form missing
     *                      15: patient consent list (if exists) invalid
     *                      16: investigator phone extension (if exists) invalid
     *                      17: study ID is missing or invalid if it is an update
     *                     
     * @return  $toInsert : array - Contains data correctly formatted and ready to be inserted
     *          $errCode : array - contains the invalid entries with an error code.
     * */
    protected function _validateStudy(&$post, $isAnUpdate = false) {
        $errCode = "";
        $startDate = false;
        $endDate = false;

        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("code", $post) || $post["code"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 2nd bit
            if (!array_key_exists("title_EN", $post) || $post["title_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if (!array_key_exists("title_FR", $post) || $post["title_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 4th bit
            if (!array_key_exists("description_EN", $post) || $post["description_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 5th bit
            if (!array_key_exists("description_FR", $post) || $post["description_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 6th bit
            if (!array_key_exists("investigator", $post) || $post["investigator"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 7th bit
            if (!array_key_exists("investigator_phone", $post) || $post["investigator_phone"] == "")
                $errCode = "1" . $errCode;
            else if(HelpSetup::validatePhone($post["investigator_phone"]) == 0){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
                
            // 8th bit
            if (!array_key_exists("investigator_email", $post) || $post["investigator_email"] == "")
                $errCode = "1" . $errCode;
            else if(!(HelpSetup::validateEmail($post["investigator_email"]))){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
               

            // 9th bit
            if (array_key_exists("start_date", $post) && $post["start_date"] != "") {
                if (!HelpSetup::isValidTimeStamp($post["start_date"]))
                    $errCode = "1" . $errCode;
                else {
                    $startDate = true;
                    $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;

            // 10th bit
            if (array_key_exists("end_date", $post) && $post["end_date"] != "") {
                if (!HelpSetup::isValidTimeStamp($post["end_date"]))
                    $errCode = "1" . $errCode;
                else {
                    $endDate = true;
                    $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;

            // 11th bit
            if ($startDate && $endDate) {
                if ((int)$post["end_date"] < (int)$post["start_date"])
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            } else
                $errCode = "0" . $errCode;

            //12th bit
            if (array_key_exists("patients", $post)) {
                if(!is_array($post["patients"]))
                    $errCode = "1" . $errCode;
                else {
                    $tempArray = array();
                    foreach($post["patients"] as $id)
                        array_push($tempArray, intval($id));
                    $post["patients"] = $tempArray;
    
                    $total = $this->opalDB->getPatientsListByIds($post["patients"]);
                    if (count($total) != count($post["patients"]))
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;

            //13th bit
            if (array_key_exists("questionnaire", $post)) {
                if(!is_array($post["questionnaire"]))
                    $errCode = "1" . $errCode;
                else {
                    $tempArray = array();
                    foreach($post["questionnaire"] as $id)
                        array_push($tempArray, intval($id));
                    $post["questionnaire"] = $tempArray;
                    $total = $this->questionnaireDB->getQuestionnairesListByIds($post["questionnaire"]);
                    if (count($total) != count($post["questionnaire"]))
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;

            //14th bit
            if (!array_key_exists("consent_form", $post) || $post["consent_form"] == ""){
                $this->_connectQuestionnaireDB();
                $res = $this->questionnaireDB->getStudyConsentFormTitle(intval($post["consent_form"]));
                if(count($res) != 1){
                    $errCode = "1" . $errCode;
                }else{
                    $errCode = "0" . $errCode;
                }
            }
            else
                $errCode = "0" . $errCode;
            
            //15th bit
            if (array_key_exists("patientConsents", $post)) {
                if(!is_array($post["patientConsents"]))
                    $errCode = "1" . $errCode;
                else {
                    $patConsIds = array();
                    foreach ($post["patientConsents"] as $patient){
                        $id = intval($patient['id']);
                        array_push($patConsIds, $id);
                    }
                    $total = $this->opalDB->getPatientsListByIds($patConsIds);
                    if (count($total) != count($patConsIds))
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;
            
            // 16th bit 
            if (array_key_exists("investigator_phoneExt", $post) && $post["investigator_phoneExt"] != "") {
                if ((HelpSetup::validatePhoneExt($post["investigator_phoneExt"])) == 0)
                    $errCode = "1" . $errCode;
                else {
                    $errCode = "0" . $errCode;
                }
            } else
                $errCode = "0" . $errCode;

            //17th bit
            if($isAnUpdate) {
                if (!array_key_exists("id", $post) || $post["id"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $result = $this->opalDB->getStudyDetails($post["id"]);
                    if (count($result) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($result) == 1)
                        $errCode = "0" . $errCode;
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates studies found.");
                }
            } else
                $errCode = "0" . $errCode;
    
        } else
            $errCode .= "11111111111111111";

        return $errCode;
    }

    /*
     * Get the details of a study
     * @params  $studyId (int) ID of the study
     * @return  (array) details of the study
     * */
    public function getStudyDetails($studyId) {
        $this->checkReadAccess($studyId);

        $result = $this->opalDB->getStudyDetails(intval($studyId));
        if (count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>1));
        else if (count($result) == 1) {
            $result = $result[0];
            $result["patients"] = array();
            $temp = $this->opalDB->getPatientsStudy(intval($studyId));
            foreach($temp as $item)
                array_push($result["patients"], $item["patientId"]);
            $result["questionnaire"] = array();
            $temp = $this->opalDB->getQuestionnairesStudy(intval($studyId));
            foreach($temp as $item)
                array_push($result["questionnaire"], $item["questionnaireId"]);
            if($result["consentQuestionnaireId"]){
                $this->_connectQuestionnaireDB();
                $result["consentQuestionnaireTitle"] = $this->questionnaireDB->getStudyConsentFormTitle(intval($result["consentQuestionnaireId"]));
            }
            return $result;
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates studies found.");
    }

    /*
     * Get the list of patients for
     * @params  $studyId (int) ID of the study
     * @return  (array) details of the study
     * */
    public function getPatientsList() {
        $this->checkReadAccess();
        $result = $this->opalDB->getPatientsList();
        usort($result, "self::_sortName");
        return $result;
    }

    /*
    * Get the list of patient consents for
    * @params $studyId (int) ID of the study
    * @return (array) details of patient consent status
    */
    public function getPatientsConsentList($studyId) {
        $this->checkReadAccess($studyId);
        $result = $this->opalDB->getPatientsStudyConsents($studyId);
        return $result;
    }


    protected static function _sortName($a, $b){
        return strcmp($a["name"], $b["name"]);
    }

    /*
     * Update a study after it is sanitized and validated. It also
     * @params  $post (array) details of the study.
     * @return  (int) number of record updated (should be one!) or an error 500
     * */
    public function updateStudy($post) {
        $this->checkWriteAccess($post);
        $this->_connectQuestionnaireDB();
        $study = HelpSetup::arraySanitization($post);
        $result = $this->_validateStudy($study, true);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Study validation failed. " . implode(" ", $result));

        $toUpdate = array(
            "ID"=>$study["ID"],
            "code"=>$study["code"],
            "title_EN"=>$study["title_EN"],
            "title_FR"=>$study["title_FR"],
            "description_EN"=>$study["description_EN"],
            "description_FR"=>$study["description_FR"],
            "investigator"=>$study["investigator"],
            "phone"=>$post["investigator_phone"],
            "email"=>$post["investigator_email"],
            "consentQuestionnaireId"=>$post["consent_form"]
        );
        if($study["investigator_phoneExt"])
            $toUpdate["phoneExt"] = $study["investigator_phoneExt"];
        else
            $toUpdate['phoneExt'] = null;
        if($study["start_date"])
            $toUpdate["startDate"] = gmdate("Y-m-d", $study["start_date"]);
        else
            $toUpdate["startDate"] = null;
        if($study["end_date"])
            $toUpdate["endDate"] = gmdate("Y-m-d", $study["end_date"]);
        else
            $toUpdate["endDate"] = null;

        $total = $this->opalDB->updateStudy($toUpdate);

        //Update patient-study table
        $currentPatients = array();
        $toKeep = array(-1);
        $toAdd = array();
        if(array_key_exists("patients", $post) && is_array($post["patients"]) && count($post["patients"]) > 0) {
            $temp = $this->opalDB->getPatientsStudy($study["ID"]);
            foreach($temp as $item)
                array_push($currentPatients, $item["patientId"]);
            
            foreach ($study["patients"] as $item) {
                if(in_array($item, $currentPatients))
                    array_push($toKeep, intval($item));
                else
                    array_push($toAdd, intval($item));
            }
        }
        
        $total += $this->opalDB->deletePatientsStudy($study["ID"], $toKeep);
        
        
        if(count($toAdd) > 0) {
            $toInsertMultiple = array();
            foreach ($toAdd as $patient)
                array_push($toInsertMultiple, array("patientId"=>$patient, "studyId"=>$study["ID"], "consentStatus"=>1));
            $total += $this->opalDB->insertMultiplePatientsStudy($toInsertMultiple);
        }

        //update any changed patient consents
        if(array_key_exists("patientConsents", $post) && is_array($post["patientConsents"]) && count($post["patientConsents"]) > 0){
            foreach($post["patientConsents"] as $item){
                if($item['changed'] && $item['consent'] && $item['id']){
                    $this->opalDB->updateStudyConsent($study["ID"], $item['id'], intval($item['consent']));  
                    $total += 1;
                }
            }
        }
        //Update questionnaire-study table
        $currentQuestionnaires = array();
        $toKeep = array(-1);
        $toAdd = array();
        if(array_key_exists("questionnaire", $post) && is_array($post["questionnaire"]) && count($post["questionnaire"]) > 0) {
            $temp = $this->opalDB->getQuestionnairesStudy($study["ID"]);
            foreach($temp as $item)
                array_push($currentQuestionnaires, $item["questionnaireId"]);

            foreach ($study["questionnaire"] as $item) {
                if(in_array($item, $currentQuestionnaires))
                    array_push($toKeep, intval($item));
                else
                    array_push($toAdd, intval($item));
            }
        }
        $total += $this->opalDB->deleteQuestionnairesStudy($study["ID"], $toKeep);

        if(count($toAdd) > 0) {
            $toInsertMultiple = array();
            foreach ($toAdd as $questionnaire)
                array_push($toInsertMultiple, array("questionnaireId"=>$questionnaire, "studyId"=>$study["ID"]));

            $total += $this->opalDB->insertMultipleQuestionnairesStudy($toInsertMultiple);
        }

        return $total;
    }

    /*
     * Get the list of consent forms
     * @return  (array) questionnaire consent forms
     * */
    public function getConsentForms() {
        $this->checkReadAccess();
        $this->_connectQuestionnaireDB();
        $result = $this->questionnaireDB->getConsentForms();
        return $result;
    }

    /**
     * Check if current consent form has been published to questionnaire control
     * @return (array) list of forms found
     */
    public function getConsentPublished($consentId){
        $this->checkreadAccess();
        $result = $this->opalDB->checkConsentFormPublished($consentId);
        return $result;
    }

}