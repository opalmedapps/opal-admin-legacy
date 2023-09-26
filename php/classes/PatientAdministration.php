<?php

/**
 * Patient Administration class
 *
 */

class PatientAdministration extends Module {

    protected $firebaseDB;

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PATIENT_ADMINISTRATION, $guestStatus);
//        $this->firebaseDB = new FirebaseOpal();
    }

    /**
     * Validate the name search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pname : patient name
     *                          language : current user language
     *
     *  1st bit invalid patient name
     *  2nd bit invalid user language
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
            if(!array_key_exists("language", $post) || ($post["language"] != "EN" && $post["language"] != "FR")) {
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
     * @param $post : array - Contains the following information
     *                          pname : patient name
     *                          language : current user language
     *
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

        $results = $this->opalDB->getPatientNameAdministration($post['pname'], $post['language']);
        $this->_findOtherMRNS($results);
        return $results;
    }

    /**
     * Validate the mrn search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pmrn : patient mrn
     *                          language : current user language
     *
     *  1st bit invalid patient mrn
     *  2nd bit invalid user language
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
            if(!array_key_exists("language", $post) || ($post["language"] != "EN" && $post["language"] != "FR")) {
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
     * @param $post : array - Contains the following information
     *                          pmrn : patient mrn
     *                          language : current user language
     *
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

        $results = $this->opalDB->getPatientMRNAdministration($post['pmrn'], $post['language']);
        $this->_findOtherMRNS($results);
        return $results;
    }

    /**
     * Validate the ramq search parameter for individual reports
     *
     * @param $post : array - Contains the following information
     *                          pramq : patient ramq
     *                          language : current user language
     *
     *  1st bit invalid patient ramq
     *  2nd bit invalid user language
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
            if(!array_key_exists("language", $post) || ($post["language"] != "EN" && $post["language"] != "FR")) {
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
     * @param $post : array - Contains the following information
     *                          pramq : patient ramq
     *                          language : current user language
     *
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

        $results = $this->opalDB->getPatientRAMQAdministration($post['pramq'], $post['language']);
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
     * Update Patient Email in Database
     *
     * @params  $post : array - Contains the following information
     *                          PatientSerNum : Serial Number of the patient
     *                          email: Patient email address
     *
     *  1st bit invalid PatientSerNum
     *  2nd bit invalid email
     *
     * @return int - number of row modified
     */
    public function updatePatientEmail($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientEmailParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        return $this->opalDB->updatePatientEmail($post["email"], $post["PatientSerNum"]);
    }

    /**
     * Validate the patient record number and email
     *
     * @params  $post : array - Contains the following information
     *                          PatientSerNum : Serial Number of the patient
     *                          email: Patient email address
     *
     *  1st bit invalid PatientSerNum
     *  2nd bit invalid email
     *
     * @return $errCode
     */
    protected function _validatePatientEmailParams($post) {

        $errCode = "";

        if(!array_key_exists("PatientSerNum", $post) || $post["PatientSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("email", $post) || $post["email"] == "" || !filter_var($post["email"], FILTER_VALIDATE_EMAIL))
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Update Patient Password in Database
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          password: Patient account password
     *
     *  1st bit invalid uid
     *  2nd bit invalid password
     *
     * @return int - number of row modified
     */
    public function updatePatientPassword($post) {
        $post2 = $post;
        $post2["password"] = "PASSWORD HIDDEN";

        $this->checkWriteAccess($post2);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientPasswordParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        return $this->opalDB->updatePatientPassword($post["password"], $post["uid"]);
    }

    /**
     * Validate the patient user name and password
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          password: Patient account password
     *
     *  1st bit invalid uid
     *  2nd bit invalid password
     *
     * @return $errCode
     */
    protected function _validatePatientPasswordParams($post) {

        $errCode = "";

        if(!array_key_exists("uid", $post) || $post["uid"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("password", $post) || $post["password"] == "" || !is_string($post["password"]))
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Update Patient Security Question in Database
     *
     * @params  $post : array - Contains the following information
     *                          QuestionSerNum : New security question serial number
     *                          Answer : Security answer of the question
     *                          PatientSerNum : Serial Number of the patient
     *                          OldQuestionSerNum : Old security question serial number
     *
     *  1st bit invalid QuestionSerNum
     *  2nd bit invalid Answer
     *  3rd bit invalid PatientSerNum
     *  4th bit invalid OldQuestionSerNum
     *
     * @return int - number of row modified
     */
    public function updatePatientSecurityAnswer($post) {
        $post2 = $post;
        $post2["Answer"] = "ANSWER HIDDEN";

        $this->checkWriteAccess($post2);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientSecurityAnswerParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        $response = $this->opalDB->updateSecurityAnswers($post["QuestionSerNum"], $post["Answer"], $post["PatientSerNum"], $post["OldQuestionSerNum"]);
        return $response;
    }

    /**
     * Validate patient new security question fields
     *
     * @params  $post : array - Contains the following information
     *                          QuestionSerNum : New security question serial number
     *                          Answer : Security answer of the question
     *                          PatientSerNum : Serial Number of the patient
     *                          OldQuestionSerNum : Old security question serial number
     *
     *  1st bit invalid QuestionSerNum
     *  2nd bit invalid Answer
     *  3rd bit invalid PatientSerNum
     *  4th bit invalid OldQuestionSerNum
     *
     * @return $errCode
     */
    protected function _validatePatientSecurityAnswerParams($post) {

        $errCode = "";

        if(!array_key_exists("QuestionSerNum", $post) || $post["QuestionSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("Answer", $post) || $post["Answer"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("PatientSerNum", $post) || $post["PatientSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("OldQuestionSerNum", $post) || $post["OldQuestionSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Update Patient access level in Database
     *
     * @params  $post : array - Contains the following information
     *                          accessLevel : new patient access level
     *                          PatientSerNum : Serial Number of the patient
     *
     *  1st bit invalid accessLevel
     *  2nd bit invalid PatientSerNum
     *
     * @return int - number of row modified
     */
    public function updatePatientAccessLevel($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientAccessLevelParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        return $this->opalDB->updatePatientAccessLevel($post["accessLevel"], $post["PatientSerNum"]);
    }

    /**
     * Validate patient new access level fields
     *
     * @params  $post : array - Contains the following information
     *                          accessLevel : new patient access level
     *                          PatientSerNum : Serial Number of the patient
     *
     *  1st bit invalid accessLevel
     *  2nd bit invalid PatientSerNum
     *
     * @return $errCode
     */
    protected function _validatePatientAccessLevelParams($post) {

        $errCode = "";

        if(!array_key_exists("accessLevel", $post) || $post["accessLevel"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("PatientSerNum", $post) || $post["PatientSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Get all published security questions in database
     * @return array
     */
    public function getAllSecurityQuestions() {
        $this->checkReadAccess();
        return $this->opalDB->getAllSecurityQuestions();
    }

    /**
     * Get all patient answered security questions
     *
     * @params  $post : array - Contains the following information
     *                          PatientSerNum : Serial Number of the patient
     *
     *  1st bit invalid PatientSerNum
     *
     * @return array
     */
    public function getPatientSecurityQuestions($post) {
        $this->checkReadAccess();
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        if(!array_key_exists("PatientSerNum", $post) || $post["PatientSerNum"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        return $this->opalDB->getPatientSecurityQuestions($post["PatientSerNum"]);
    }

    /**
     * Get all access level in database
     * @return array
     */
    public function getAllAccessLevel() {
        $this->checkReadAccess();
        return $this->opalDB->getAllAccessLevel();
    }

    /**
     * Update Patient Email in Database
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          email: Patient email address
     *
     *  1st bit invalid uid
     *  2nd bit invalid email
     *
     * @throws Exception
     */
    public function updateExternalEmail($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateExternalEmailParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        $this->firebaseDB->updateEmail($post["uid"], $post["email"]);
    }

    /**
     * Validate the patient user name and email
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          email: Patient email address
     *
     *  1st bit invalid uid
     *  2nd bit invalid email
     *
     * @return $errCode
     */
    protected function _validateExternalEmailParams($post) {
        $errCode = "";

        if(!array_key_exists("uid", $post) || $post["uid"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("email", $post) || $post["email"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }

    /**
     * Update Patient Password in External Database
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          password: Patient account password
     *
     *  1st bit invalid uid
     *  2nd bit invalid password
     *
     * @throws Exception
     */
    public function updateExternalPassword($post) {
        $post2 = $post;
        $post2["password"] = "PASSWORD HIDDEN";

        $this->checkWriteAccess($post2);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateExternalPasswordParams($post);
        $errCode = bindec($errCode);
        if($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        $this->firebaseDB->updatePassword($post["uid"], $post["password"]);
    }

    /**
     * Validate the patient user name and password
     *
     * @params  $post : array - Contains the following information
     *                          uid : User name key in system of the patient
     *                          password: Patient account password
     *
     *  1st bit invalid uid
     *  2nd bit invalid password
     *
     * @return $errCode
     */
    protected function _validateExternalPasswordParams($post) {

        $errCode = "";

        if(!array_key_exists("uid", $post) || $post["uid"] == "")
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        if(!array_key_exists("password", $post) || $post["password"] == "" || !is_string($post["password"]) || strlen($post["password"]) < 8
            || !preg_match(REGEX_CAPITAL_LETTER, $post["password"]) || !preg_match(REGEX_LOWWER_CASE_LETTER, $post["password"]) ||
            !preg_match(REGEX_SPECIAL_CHARACTER, $post["password"]) || !preg_match(REGEX_NUMBER, $post["password"]))
            $errCode = "1" . $errCode;
        else
            $errCode = "0" . $errCode;

        return $errCode;
    }
}