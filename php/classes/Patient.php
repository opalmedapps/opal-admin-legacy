<?php

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
    public function updatePublishFlags($post){
        $this->checkWriteAccess($post);
        HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePublishFlag($post);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($post["data"] as $item)
            $this->opalDB->updatePatientPublishFlag($item["serial"], $item["transfer"]);
    }

    /**
     * Validate a list of publication flags for patient.
     * @param $post - publish flag to validate
     * @return string - string to convert in int for error code
     */
    protected function _validatePublishFlag(&$post) {
        $errCode = "";
        if (is_array($post) && array_key_exists("data", $post) && is_array($post["data"])) {
            $errFound = false;
            foreach ($post["data"] as $item) {
                if (!array_key_exists("serial", $item) || $item["serial"] == ""|| !array_key_exists("transfer", $item) || $item["transfer"] == "") {
                    $errFound = true;
                    break;
                }
            }
            if ($errFound)
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
     * Validate the name search parameter for individual reports
     * @param $post - patient last name
     * @return $errCode - 1st bit for pname
     */
    protected function _validateName(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("pname", $post) || $post["pname"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post: patient last name case insensitive
     * @return array : details for the given patient(s) matching search criteria
     * @error 422 with array (validation=>integer)
     */
    public function findPatientByName( $post ) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateName($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientName($post['pname']);
    }

    /**
     * Validate the mrn search parameter for individual reports
     * @param post - patient mrn
     * @return $errCode - 1st bit for mrn
     */
    protected function _validateMRN(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("pmrn", $post) || $post["pmrn"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post: patient mrn
     * @return array : details for the given patient(s) matching search criteria
     *
     */
    public function findPatientByMRN( $post ) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateMRN($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientMRN($post['pmrn']);
    }

    /**
     * Validate the ramq search parameter for individual reports
     * @param $post - patient ramq
     * @return $errCode - 1st bit for ramq
     */
    protected function _validateRAMQ(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("pramq", $post) || $post["pramq"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     * Search database for patient
     *
     * @param $post: patient ramq
     * @return array : details for the given patient(s) matching search criteria
     *
     */
    public function findPatientByRAMQ( $post ) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateRAMQ($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientRAMQ($post['pramq']);

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
     *  9th bit treatment planning
     *  10th bit general
     *  11th bit clinical notes
     *  12 bit treating team messages
     *
     * @param $post array - mrn & featureList
     * @return $errCode
     */
    protected function _validatePatientReport(&$post){
        $errCode = "";

        if(is_array($post)){
            //bit 1
            if(!array_key_exists("psnum", $post) || $post["psnum"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 2
            if(!array_key_exists("diagnosis", $post) || $post["diagnosis"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 3
            if(!array_key_exists("appointments", $post) || $post["appointments"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 4
            if(!array_key_exists("questionnaires", $post) || $post["questionnaires"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 5
            if(!array_key_exists("education", $post) || $post["education"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 6
            if(!array_key_exists("testresults", $post) || $post["testresults"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 7
            if(!array_key_exists("pattestresults", $post) || $post["pattestresults"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 8
            if(!array_key_exists("notes", $post) || $post["notes"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 9
            if(!array_key_exists("treatplan", $post) || $post["treatplan"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 10
            if(!array_key_exists("general", $post) || $post["general"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 11
            if(!array_key_exists("clinicalnotes", $post) || $post["clinicalnotes"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 12
            if(!array_key_exists("treatingteam", $post) || $post["treatingteam"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }else{
            $errCode = "111111111111";
        }
        return $errCode;
    }

    /**
     *  Generate the patient report given patient serial number & feature list
     *  @param $post: array contains parameter to find
     *  @return $resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($post){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validatePatientReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        $resultArray = array();
        if($post['diagnosis'] === "true"){
            $resultArray["diagnosis"] = $this->opalDB->getPatientDiagnosisReport($post['psnum']);
        }
        if($post["appointments"] === "true"){
            $resultArray["appointments"] = $this->opalDB->getPatientAppointmentReport($post['psnum']);
        }
        if($post["questionnaires"] === "true"){
            $resultArray["questionnaires"] = $this->opalDB->getPatientQuestionnaireReport($post['psnum']);
        }
        if($post["education"] === "true"){
            $resultArray["education"] = $this->opalDB->getPatientEducMaterialReport($post['psnum']);
        }
        if($post["testresults"] === "true"){
            $resultArray["testresults"] = $this->opalDB->getPatientLegacyTestReport($post['psnum']);
        }
        if($post["pattestresults"] === "true"){
            $resultArray["pattestresults"] = $this->opalDB->getPatientTestReport($post['psnum']);
        }
        if($post["notes"] === "true"){
            $resultArray["notes"] = $this->opalDB->getPatientNotificationsReport($post['psnum']);
        }
        if($post["treatplan"] === "true"){
            $resultArray["treatplan"] = $this->opalDB->getPatientTreatmentPlanReport($post['psnum']);
        }
        if($post["clinicalnotes"] === "true"){
            $resultArray["clinicalnotes"] = $this->opalDB->getPatientClinNoteReport($post['psnum']);
        }
        if($post["treatingteam"] === "true"){
            $resultArray["treatingteam"] = $this->opalDB->getPatientTxTeamReport($post['psnum']);
        }
        if($post["general"] === "true"){
            $resultArray["general"] = $this->opalDB->getPatientGeneralReport($post['psnum']);
        }
        return $resultArray;
    }

    /**
     * Validate the educational material search parameter for group reports
     * @param $post - matType
     * @return $errCode - 1st bit for matType
     */
    protected function _validateEducType(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("matType", $post) || $post["matType"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     *  Generate list of available educational materials from DB
     *  @param $post: user selected material type
     *  @return array of educational materials
     */
    public function findEducationalMaterialOptions( $post ){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateEducType($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getEducMatOptions($post['matType']);
    }

    /**
     * Validate the educational material report parameters
     * @param $post array - contains type and name
     * @return $errCode - validation of the data
     */
    protected function _validateEducReport(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("type", $post) || $post["type"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }

            if(!array_key_exists("name", $post) || $post["name"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }else{
            $errCode = "11";
        }
        return $errCode;
    }

    /**
     * Generate educational materials group report
     * @param $post
     * @return array
     */
    public function getEducationalMaterialReport( $post ){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateEducReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getEducMatReport($post['type'], $post['name']);
    }

    /**
     * Generate list of questionnaires available in DB
     * @return array
     */
    public function findQuestionnaireOptions(){
        $this->checkReadAccess();
        return $this->opalDB->getQstOptions();
    }

    /**
     * Validate the questionnaire name search parameter for group reports
     * @param $post - qstName questionnaire name
     * @return $errCode - 1st bit for qstName
     */
    protected function _validateQstReport(&$post){
        $errCode = "";
        if(is_array($post)){
            if(!array_key_exists("qstName", $post) || $post["qstName"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }

    /**
     *  Generate questionnaires report given user selected qName
     *  @param $post: questionnaire name
     *  @return array: questionnaire report JSON object
     */
    public function getQuestionnaireReport( $post ){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateQstReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getQstReport($post['qstName']);
    }

    /**
     *  Generate patient group report
     *  @return array: patient group report JSON object
     */
    public function getPatientGroupReport(){
        $this->checkReadAccess();
        return $this->opalDB->getDemoReport();

    }

    /**
     *
     * Determines the existence of a patient
     *
     * @param string $site : Hospital Identifier Type
     * @param string $mrn : Hospital Identifier Value
     * @return array $response : 0 / 1
     */
    public function checkPatientExist ($post )
    {
        $response = array(
            'status' => '',
        );

        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);

        $pattern = "/^[0-9]*$/i";

        if (preg_match($pattern,  $post["mrn"] )) {
            $mrn = str_pad( $post["mrn"] ,7,"0",STR_PAD_LEFT);
            $response['status']  = "Success";
            $errCode = 0;
            $patientSite = $this->opalDB->getPatientSite($mrn, $post["site"]);
            $response['data']  = boolval(count($patientSite));

        } else {
            $errCode = 1;
            $response['status']  = "Error";
            $response['message'] = "Invalid MRN";
        }

        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }


        return $response;
    }

    /**
     * Validate patient info
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
    protected function _validatePatientParams($post)
    {
        $validLang = array("EN", "FR", "SN");
        $validGender = array("Male", "Female", "Unknown", "Other");
        $pattern = "/^[0-9]*$/i";
        $errCode = "";

        if (!array_key_exists("mrns", $post) || $post["mrns"] == "" || count($post["mrns"]) <= 0)
            $errCode = "1" . $errCode;
        else {
            $invalidValue = false;
            foreach ($post["mrns"] as $identifier) {
                $invalidValue = !preg_match($pattern, $identifier["mrn"]);
            }

            if ($invalidValue) {
                $errCode = "1" . $errCode;
             } else {
                $errCode = "0" . $errCode;
            }
        }
        if(!array_key_exists("ramq", $post) || $post["ramq"] == "")
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

        if(array_key_exists("language", $post)){
            if (!in_array($post["language"], $validLang))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        }

        if(array_key_exists("gender", $post)){
            if ($post["gender"] != null && !in_array($post["gender"], $validGender))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        }

        print_r($errCode);
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
     * @return  $errCode : int - error code coded on bitwise operation. If 0, no error.
     */
    public function updatePatient($post){

        $response = array(
            'status' => 'Error'
        );

        $errCode = "";
        $this->checkWriteAccess($post);
        HelpSetup::arraySanitization($post);

        $errCode = $this->_validatePatientParams($post) . $errCode;

        $invalidValue = true;
        $mrns = $post["mrns"];
        $toInsertMultiple = array();
        $patientSerNum = "";
        while (($identifier = array_shift($mrns)) !== NULL) {
            $mrn = str_pad($identifier["mrn"] ,7,"0",STR_PAD_LEFT);
            $patientSite = $this->opalDB->getPatientSite($mrn, $identifier["site"]);
            $invalidValue = !boolVal(count($patientSite)) && $invalidValue;
            if (count($patientSite) == 1){
                $patientSerNum = $patientSite[0]["PatientSerNum"];
            } else {
                if ($patientSerNum == ""){
                    $mrns = array_merge($mrns,array($identifier));
                } else {
                    array_push($toInsertMultiple, array("PatientSerNum"=>$patientSerNum,
                        "Hospital_Identifier_Type_Code"=>$identifier["site"],
                        "MRN"=>$mrn,
                        "Is_Active"=>$identifier["active"]));
                }
            }
        }

        if ($invalidValue){
            $errCode = "1" . $errCode;
        } else {
            $response['status']  = "Success";
            $response['data']  = json_encode($patientSite);
        }

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        //Update patient demographics
        $patientdata = $this->opalDB->fetchTriggersData("SELECT * FROM Patient where PatientSerNum=" . $patientSerNum)[0];
        $patientdata["PatientSerNum"] = $patientSerNum;
        $patientdata["FirstName"] = $post["name"]["firstName"];
        $patientdata["LastName"] = $post["name"]["lastName"];
        $patientdata["SSN"] = $post["ramq"];
        $patientdata["DateOfBirth"] = $post["birthdate"];

        $from = new DateTime($patientdata["DateOfBirth"]);
        $to   = new DateTime('today');
        $age  =  $from->diff($to)->y;

        if ($age > 13){
            $patientdata["BlockedStatus"]   = 1;
            $patientdata["StatusReasonTxt"] = "Patient passed 13 years of age";
        }

        if (array_key_exists("alias", $post) && !empty($post["alias"])){
            $patientdata["Alias"] = $post["alias"];
        }

        if (array_key_exists("gender", $post) && !empty($post["gender"])){
            $patientdata["Sex"] = $post["gender"];
        }

        if (array_key_exists("email", $post) && !empty($post["email"])){
            $patientdata["Email"]    = $post["email"];
        }

        if (array_key_exists("phone", $post) && !empty($post["phone"])){
            $patientdata["TelNum"]   = $post["phone"];
        }

        if (array_key_exists("language", $post) && !empty($post["language"])){
            $patientdata["Language"] = $post["language"];
        }

        if(array_key_exists("deceasedDateTime", $post) && $post["deceasedDateTime"] != ""){
            $patientdata["StatusReasonTxt"] = "Deceased patient";
            $patientdata["BlockedStatus"] = 1;
            $this->opalDB->updatePatientPublishFlag($patientSerNum,0);
        }

        if (array_key_exists("deceasedDateTime", $post) && $post["deceasedDateTime"] == null){
            $patientdata["StatusReasonTxt"] = " ";
            $patientdata["BlockedStatus"] = 0;
            $this->opalDB->updatePatientPublishFlag($patientSerNum,0);
        }

        $patientdata["DeathDate"] = $post["deceasedDateTime"];
        unset($patientdata["LastUpdated"]);

        if (count($toInsertMultiple) > 0){
            $this->opalDB->updatePatientLink($toInsertMultiple);
        }

        $this->opalDB->updatePatient($patientdata);

        return $response;
    }

}