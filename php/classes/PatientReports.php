<?php

/**
 * Patient Reports class
 * @author K. Agnew Dec 2020 - Patient reports refactor - death to Perl
 * 
 */

class PatientReports extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PATIENT, $guestStatus);
    }

    /**
     * Validate the name search parameter for individual reports
     * @param post string - patient last name
     * @return errCode binary - 1st bit for pname
     */
    protected function _validateName(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     * @param name: patient last name case insensitive
     * @return patientList: details for the given patient(s) matching search criteria
     * @error 422 with array (validation=>integer)
     */
    public function findPatientByName( $post ) {
        //check read access before proceeding
        $this->checkReadAccess($post);
        //data validation
        $errCode = $this->_validateName($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientName($post['pname']);
    }

    /**
     * Validate the mrn search parameter for individual reports
     * @param post string - patient mrn
     * @return errCode binary - 1st bit for mrn
     */
    protected function _validateMRN(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     * @param mrn: patient mrn
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByMRN( $post ) {
        $this->checkReadAccess($post);
        //data validation
        $errCode = $this->_validateMRN($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientMRN($post['pmrn']);
    }

    /**
     * Validate the ramq search parameter for individual reports
     * @param post string - patient ramq
     * @return errCode binary - 1st bit for ramq
     */
    protected function _validateRAMQ(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     * @param ramq: patient ramq
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByRAMQ( $post ) {
        $this->checkReadAccess($post);
        //data validation
        $errCode = $this->_validateRAMQ($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientRAMQ($post['pramq']);        
        
    }

    /**
     * Validate the input parameters for individual patient report
     * @param post array - mrn & featureList
     * @return errCode binary
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
     */
    protected function _validatePatientReport(&$post){
        $errCode = "";
        $post = HelpSetup::arraySanitization($post);

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
     *  @param pnum: selected patient serial number
     *  @param flist: array of report segments each pointing to a truthy/falsy variable for report generation
     *  @return resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($post){
        $this->checkReadAccess($post);
        //data validation
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
     * @param post string - matType
     * @return errCode binary - 1st bit for matType
     */
    protected function _validateEducType(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     *  @param matType: user selected material type
     *  @return educList: array of educational materials
     */
    public function findEducationalMaterialOptions( $post ){
        $this->checkReadAccess($post);
        //data validation
        $errCode = $this->_validateEducType($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getEducMatOptions($post['matType']);
    }

    /**
     * Validate the educational material report parameters
     * @param post string - matType
     *             string - matName
     * @return errCode binary - 1st bit for matType
     *                        - 2nd bit for matName
     */
    protected function _validateEducReport(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     *  Generate educational materials group report
     *  @param type: user selected material type
     *  @param name: user selected material name
     *  @return educReport: educational material report
     */
    public function getEducationalMaterialReport( $post ){
        $this->checkReadAccess($post);
        //data validation
        $errCode = $this->_validateEducReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getEducMatReport($post['type'], $post['name']);
    }

    /**
     *  Generate list of questionnaires available in DB
     *  @return qstList: questionnaire names array
     */
    public function findQuestionnaireOptions(){
        $this->checkReadAccess();
        return $this->opalDB->getQstOptions();            
    }

    /**
     * Validate the questionnaire name search parameter for group reports
     * @param post string - qstName questionnaire name
     * @return errCode binary - 1st bit for qstName
     */
    protected function _validateQstReport(&$post){
        $post = HelpSetup::arraySanitization($post);
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
     *  @param qName: questionnaire name
     *  @return qstReport: questionnaire report JSON object
     */
    public function getQuestionnaireReport( $post ){
        $this->checkReadAccess($post);
        $errCode = $this->_validateQstReport($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getQstReport($post['qstName']);
    }

    /**
     *  Generate patient group report
     *  @return ptReport: patient group report JSON object
     */
    public function getPatientGroupReport(){
        $this->checkReadAccess();
        return $this->opalDB->getDemoReport();

    }

}