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
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, array("validation"=>$errCode));
        }
        return $this->opalDB->getPatientName($post['pname']);
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
        $pmrn = HelpSetup::arraySanitization($post['pmrn']);
        return $this->opalDB->getPatientMRN($pmrn);

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
        $ramq = HelpSetup::arraySanitization($post['pramq']);
        return $this->opalDB->getPatientRAMQ($ramq);        
        
    }
    
    /**
     *  Generate the patient report given patient serial number & feature list
     *  @param pnum: selected patient serial number
     *  @param flist: array of report segments each pointing to a truthy/falsy variable for report generation
     *  @return resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($post){
        $this->checkReadAccess($post);
        
        $pnum = HelpSetup::arraySanitization($post['psnum']);
        $flist = array(
            "diagnosis" => $post['diagnosis'],
            "appointments" => $post['appointments'],
            "questionnaires" => $post['questionnaires'],
            "education" => $post['education'],
            "testresults" => $post['testresults'],
            "pattestresults" => $post['pattestresults'],
            "notes" => $post['notes'],
            "treatplan" => $post['treatplan'],
            "clinicalnotes" => $post['clinicalnotes'],
            "treatingteam" => $post['treatingteam'],
            "general" => $post['general']
        );
        
        $resultArray = array();
        if($flist["diagnosis"] === "true"){
            $resultArray["diagnosis"] = $this->opalDB->getPatientDiagnosisReport($pnum);
        }
        if($flist["appointments"] === "true"){
            $resultArray["appointments"] = $this->opalDB->getPatientAppointmentReport($pnum);
        }
        if($flist["questionnaires"] === "true"){
            $resultArray["questionnaires"] = $this->opalDB->getPatientQuestionnaireReport($pnum);
        }
        if($flist["education"] === "true"){
            $resultArray["education"] = $this->opalDB->getPatientEducMaterialReport($pnum);
        }
        if($flist["testresults"] === "true"){
            $resultArray["testresults"] = $this->opalDB->getPatientLegacyTestReport($pnum);
        }
        if($flist["pattestresults"] === "true"){
            $resultArray["pattestresults"] = $this->opalDB->getPatientTestReport($pnum);
        }
        if($flist["notes"] === "true"){
            $resultArray["notes"] = $this->opalDB->getPatientNotificationsReport($pnum);
        }
        if($flist["treatplan"] === "true"){
            $resultArray["treatplan"] = $this->opalDB->getPatientTreatmentPlanReport($pnum);
        }
        if($flist["clinicalnotes"] === "true"){
            $resultArray["clinicalnotes"] = $this->opalDB->getPatientClinNoteReport($pnum);
        }
        if($flist["treatingteam"] === "true"){
            $resultArray["treatingteam"] = $this->opalDB->getPatientTxTeamReport($pnum);
        }
        if($flist["general"] === "true"){
            $resultArray["general"] = $this->opalDB->getPatientGeneralReport($pnum);
        }
        return $resultArray;
    }

    /**
     *  Generate list of available educational materials from DB
     *  @param matType: user selected material type
     *  @return educList: array of educational materials
     */
    public function findEducationalMaterialOptions( $post ){
        $this->checkReadAccess($post);
        $matType = HelpSetup::arraySanitization($post['matType']);
        return $this->opalDB->getEducMatOptions($matType);
    }

    /**
     *  Generate educational materials group report
     *  @param matType: user selected material type
     *  @param matName: user selected material name
     *  @return educReport: educational material report
     */
    public function getEducationalMaterialReport( $post ){
        $this->checkReadAccess($post);
        $matType = HelpSetup::arraySanitization($post['type']);
        $matName = HelpSetup::arraySanitization($post['name']);
        return $this->opalDB->getEducMatReport($matType, $matName);
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
     *  Generate questionnaires report given user selected qName
     *  @param qName: questionnaire name
     *  @return qstReport: questionnaire report JSON object
     */
    public function getQuestionnaireReport( $post ){
        $this->checkReadAccess($post);
        $qName = HelpSetup::arraySanitization($post['qstName']);
        return $this->opalDB->getQstReport($qName);
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