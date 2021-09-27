<?php

/**
 * Appointment class
 *
 */

class Appointment extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }

    /**
     * Validate the input parameters for individual patient appointment
     *  1st bit site
     *  2nd bit mrn
     *
     * @param $post array - mrn & featureList
     * @return $errCode
     */
    protected function _validateAppointment(&$post){
        $errCode = "";

        if(is_array($post)){
            //bit 1
            if(!array_key_exists("site", $post) || $post["site"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 2
            if(!array_key_exists("mrn", $post) || $post["mrn"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }

        } else {
            $errCode = "11";
        }
        return $errCode;
    }

    protected function _validateAppointmentSourceExternalId(&$post, &$patientSite, &$source)  {
        $patientSite = array();
        $errCode = $this->_validateBasicPatientInfo($post, $patientSite);


        // 4th bit - source
        if(!array_key_exists("sourceSystem", $post) || $post["sourceSystem"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $source = $this->opalDB->getSourceDatabaseDetails($post["sourceSystem"]);

            if(count($source) != 1) {
                $source = array();
                $errCode = "1" . $errCode;
            }  else {
                $source = $source[0];
                $errCode = "0" . $errCode;
            }
        }

        return $errCode;
    }

    /**
     * Validate the input parameters for an appointment
     *  1st bit site
     *  2nd bit mrn
     *
     * @param $post array - appointment informations
     * @return $errCode
     */
    protected function _validateInsertAppointment(&$post, &$patientSite, &$source) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        if(is_array($post)){
            $errCode = $this->_validateAppointmentSourceExternalId($post, $patientSite, $source);

            //bit 2
            if(!array_key_exists("sourceId", $post) || $post["sourceId"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }

            //bit 5
            if(!array_key_exists("appointmentTypeCode", $post) || $post["appointmentTypeCode"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 6
            if(!array_key_exists("clinicDescription", $post) || $post["clinicDescription"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 6
            if(!array_key_exists("scheduledTimestamp", $post) || $post["scheduledTimestamp"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 6
            if(!array_key_exists("status", $post) || $post["status"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }

        } else {
            $errCode = "11";
        }
        return $errCode;
    }

    /**
     *  Return an appointment for a patient with or without date range
     *  @param $post: array contains parameter site/mrn
     *  @return array - appointment JSON object
     */
    public function getAppointment($post) {

        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateAppointment($post);
        $errCode = bindec($errCode);

        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        if(!array_key_exists("startDate", $post) || $post["startDate"] == "") {
            $startDate = null;
        } else {
            $startDate = $post["startDate"] . " 00:00:00";
        }

        if(!array_key_exists("endDate", $post) || $post["endDate"] == "") {
            $endDate = null;
        } else {
            $endDate = $post["endDate"] . " 23:59:59";
        }

        return $this->opalDB->getAppointment($post["site"],$post["mrn"],$startDate,$endDate);
    }

    /**
     * Updates the check-in for a particular appointment to checked and send the info to the push notification API. If
     * the call returns an error, a code 502 (bad gateway) is returned to the caller to inform there's a problem with
     * the push notification. Otherwise, a code 200 (all clear) is returned.
     * @param $post array - contains the source name and the external appointment ID
     */
    public function updateAppointmentCheckIn($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updateAppointmentCheckIn($post);
    }

    /**
     * Insert an appointment
     * @param $post array - contains the source name and the external appointment ID
     */
    public function insertAppointment($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        return $this->_replaceAppointment($post);
    }

    protected function _replaceAppointment($post) {
        $patientSite = null;
        $source = null;

        $errCode = $this->_validateInsertAppointment($post, $patientSite, $source);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>$errCode)));

        $toInsert = array(
            "PatientSerNum"=>$patientSite["PatientSerNum"],
            "SourceDatabaseSerNum"=>$source["SourceDatabaseSerNum"],
            "AppointmentAriaSer"=>$post["sourceId"],
            "PrioritySerNum"=>0,
            "DiagnosisSerNum" => 0,
            "Status" => $post["status"],
            "State" => "Active",
            "ScheduledStartTime" => $post["scheduledTimestamp"],
            "ScheduledEndTime" => $post["scheduledTimestamp"],
            "ActualStartDate" => "0000-00-00 00:00:00",
            "ActualEndDate" => "0000-00-00 00:00:00",
            "Location" => "10",
            "RoomLocation_EN" => "",
            "RoomLocation_FR" => "",
            "Checkin" => 0,
            "ChangeRequest" => 0,
            "DateAdded" => date("Y-m-d H:i:s"),
            "ReadStatus" => 0,
            "SessionId"=>$this->opalDB->getSessionId(),
        );

        $appointment = $this->opalDB->findAppointment($source["SourceDatabaseSerNum"],$post["sourceId"]);
        if ($appointment["AppointmentSerNum"] != "") {
            $toInsert["AppointmentSerNum"] = $appointment["AppointmentSerNum"];
        }

        $aliasInfos = $this->opalDB->getAlias('Appointment',$post['appointmentTypeCode'], $post['appointmentTypeDescription']);
        if(count($aliasInfos) <= 1) {
            _updateAppointmentPending($toInsert);

            $toInsert["AliasExpressionSerNum"] = $aliasInfos[0]['AliasExpressionSerNum'];
            return $this->opalDB->insertAppointment($toInsert);
        } else {
            return $this->_insertAppointmentPending($toInsert);
            return $this->_insertAppointmentPendingMH($toInsert);
        }

        return false;
    }

    protected function _updateAppointmentPending($toInsert) {
        $pendingAppointment = $this->opalDB->findPendingAppointment($toInsert["SourceDatabaseSerNum"],$toInsert["sourceId"]);
        $this->opalDB->deleteAppointmentPending($pendingAppointment["AppointmentSerNum"]);
    }

    protected function _insertAppointmentPending($toInsert) {
        $pendingAppointment = $this->opalDB->findPendingAppointment($toInsert["SourceDatabaseSerNum"],$toInsert["sourceId"]);
        $toInsert["Level"]  = 1;
        if(count($pendingAppointment) < 1) {
            $toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];
            $toInsert["DateModified"] = date("Y-m-d H:i:s");
            unset($toInsert["DateAdded"]);
        } 

        return $this->opalDB->insertPendingAppointment($toInsert);
    }

    protected function _insertAppointmentPendingMH($toInsert) {
        $pendingAppointment = $this->opalDB->findPendingMHAppointment($toInsert["SourceDatabaseSerNum"],$toInsert["sourceId"]);
        $toInsert["Level"]  = 1;
        if ($pendingAppointment["AppointmentSerNum"] != "") {
            $toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];
            unset($toInsert["DateAdded"]);
        } else {
            $toInsert["PendingDate"] = date("Y-m-d H:i:s");
        }
        return $this->opalDB->insertPendingMHAppointment($toInsert);
    }
}