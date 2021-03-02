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

        foreach ($post["transferList"] as $item)
            $this->opalDB->updatePatientPublishFlag($item["serial"], $item["transfer"]);
    }

    /**
     *
     * Gets a list of existing patients in the database
     *
     * @return array $patientList : the list of existing patients
     */
    public function getPatients() {
        $this->checkReadAccess();
        return $this->opalDB->getPatients();

        $patientList = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
        SELECT DISTINCT
          pc.PatientSerNum,
          pc.PatientUpdate,
          pt.FirstName,
          pt.LastName,
          pt.PatientId,
          pc.LastTransferred,
					pt.BlockedStatus,
					usr.Username,
					pt.email
        FROM
          PatientControl pc,
          Patient pt,
		      Users usr
        WHERE
          pt.PatientSerNum = pc.PatientSerNum
          AND pt.PatientSerNum 	= usr.UserTypeSerNum
  				AND usr.UserType 		= 'Patient'
        ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $patientArray = array(
                    'serial'          => $data[0],
                    'transfer'        => $data[1],
                    'name'            => "$data[2] $data[3]",
                    'patientid'       => $data[4],
                    'lasttransferred' => $data[5],
                    'disabled' 			  => intval($data[6]),
                    'uid'             => $data[7],
                    'email'           => $data[8]
                );
                array_push($patientList, $patientArray);
            }

            return $patientList;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets a list of patient activities
     *
     * @return array $patientActivityList : the list of patient activities
     */
    public function getPatientActivities() {
        $this->checkReadAccess();
        $patientActivityList = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
        SELECT DISTINCT
          pt.PatientSerNum,
          pt.PatientId,
          pt.SSN,
          pt.FirstName,
          pt.LastName,
          pal.SessionId,
          pal.DateTime AS LoginTime,
          pal.Request,
          pal.DeviceId
        FROM
          Patient pt,
          PatientActivityLog pal,
          Users
        WHERE
          pt.PatientSerNum    = Users.UserTypeSerNum
          AND Users.Username  = pal.Username
          AND Users.UserType  = 'Patient'
          AND pal.Request     = 'Login'
        ORDER BY
          pal.DateTime DESC LIMIT 20000
      ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $tmpPAList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $deviceid = $data[8];

                if ($deviceid == 'browser') {
                    // do nothing
                } else if (strtoupper($deviceid) == $deviceid) {
                    $deviceid = "iOS/".$deviceid;
                } else {
                    $deviceid = "Android/".$deviceid;
                }

                $patientArray = array(
                    $data[5] => array(
                        'serial'    => $data[0],
                        'patientid' => $data[1],
                        'ssn'       => $data[2],
                        'name'      => "$data[3] $data[4]",
                        'sessionid' => $data[5],
                        'login'     => $data[6],
                        'request'   => $data[7],
                        'deviceid'  => $deviceid
                    )
                );

                array_push($tmpPAList, $patientArray);
            }

            $sql = "
        SELECT DISTINCT
          pal.SessionId,
          pal.DateTime AS LogoutTime
        FROM
          PatientActivityLog pal
        WHERE
          pal.Request = 'Logout'
      ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                foreach ($tmpPAList as &$session) {
                    if($data[0] == key($session)){
                        $session[$data[0]]['logout'] = $data[1];
                        break;
                    }
                }
            }

            foreach ($tmpPAList as $session) {
                foreach ($session as $value) {
                    array_push($patientActivityList, $value);
                }
            }

            return $patientActivityList;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets details for one patient
     *
     * @param int $serial : the patient serial number
     * @return array $patientDetails : the patient details
     */
    public function getPatientDetails ($serial) {
        $this->checkReadAccess($serial);
        $patientDetails = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
        SELECT DISTINCT
          pt.FirstName,
          pt.LastName,
          pt.PatientId,
          usr.Username,
          pt.BlockedStatus,
          pt.email
        FROM
          Patient pt,
          Users usr
        WHERE
          pt.PatientSerNum = '$serial'
          AND pt.PatientSerNum = usr.UserTypeSerNum
          AND usr.UserType = 'Patient'
      ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $patientDetails = array(
                'serial'    => $serial,
                'name'      => "$data[0] $data[1]",
                'patientid' => $data[2],
                'uid'       => $data[3],
                'disabled'  => intval($data[4]),
                'email'     => $data[5]
            );

            return $patientDetails;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
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
}