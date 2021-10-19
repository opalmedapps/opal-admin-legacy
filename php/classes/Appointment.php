<?php

/**
 * Appointment class
 *
 */

class Appointment extends Module
{

    public function __construct($guestStatus = false)
    {
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
    protected function _validateAppointment(&$post): string
    {
        $errCode = "";

        if (is_array($post)) {
            //bit 1
            if (!array_key_exists("site", $post) || $post["site"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $errCode = "0" . $errCode;
            }
            //bit 2
            if (!array_key_exists("mrn", $post) || $post["mrn"] == "") {
                $errCode = "1" . $errCode;
            } else {
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
            //bit 7
            if(!array_key_exists("scheduledTimestamp", $post) || $post["scheduledTimestamp"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 8
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
     * @param $post : array contains parameter site/mrn
     * @return array - appointment JSON object
     */
    public function getAppointment($post)
    {

        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateAppointment($post);
        $errCode = bindec($errCode);

        if ($errCode != 0) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        if (!array_key_exists("startDate", $post) || $post["startDate"] == "") {
            $startDate = null;
        } else {
            $startDate = $post["startDate"] . " 00:00:00";
        }

        if (!array_key_exists("endDate", $post) || $post["endDate"] == "") {
            $endDate = null;
        } else {
            $endDate = $post["endDate"] . " 23:59:59";
        }

        return $this->opalDB->getAppointment($post["site"], $post["mrn"], $startDate, $endDate);
    }

    /**
     * Updates the check-in for a particular appointment to checked and send the info to the push notification API. If
     * the call returns an error, a code 502 (bad gateway) is returned to the caller to inform there's a problem with
     * the push notification. Otherwise, a code 200 (all clear) is returned.
     * @param $post array - contains the source name and the external appointment ID
     */
    public function updateAppointmentCheckIn($post)
    {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updateAppointmentCheckIn($post);
    }

    /**
     * Delete a specific appointment.
     * @params  $post : array - contains the following info:
     *                          sourceSystem : Source database of appointment (i.e. Aria, Medivisit, Mosaic, etc.)
     *                          sourceId : Source system unique appointment ID (i.e. YYYYA9999999, 9999999)
     * @return  int - number of records deleted
     * */
    public function deleteAppointment($post)
    {
        $source = null;
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateDeleteAppointment($post, $source);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        $currentAppointment = $this->opalDB->findAppointment($source["SourceDatabaseSerNum"],$post["sourceId"]);
        $pendingAppointment = $this->opalDB->findPendingAppointment($source["SourceDatabaseName"],$post["sourceId"]);

        if (count($currentAppointment) > 1){
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates appointment found.");                    
        } else if (count($currentAppointment) == 1){
            $appointment = $currentAppointment[0];
            $this->opalDB->deleteAppointment($appointment["AppointmentSerNum"]);
        } else if(count($pendingAppointment) == 1) {
            $toInsert = $pendingAppointment[0];
            $toInsert["DateModified"] = date("Y-m-d H:i:s");
            $toInsert["Status"] = "Deleted";
            $toInsert["State"] = "Deleted";
            var_dump($toInsert);
            $this->opalDB->insertPendingAppointment($toInsert);
            $this->_insertAppointmentPendingMH($toInsert, $source);
        } else if (count($currentAppointment) < 1 && count($pendingAppointment) < 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, "Appointment not found.");
        }      
    }

    /**
     * Validate basic information of a specific database source.
     * @params  $post : array - Contains the following information
     *                          sourceSystem : Source database of appointment (i.e. Aria, Medivisit, Mosaic, etc.)
     *                          sourceId : Source system unique appointment ID (i.e. YYYYA9999999, 9999999)
     *                          source : Source database of the diagnosis (mandatory)
     * Validation code :
     *                      1: source invalid or missing
     */
    protected function _validateDeleteAppointment(&$post, &$source)
    {

        $errCode = "";
        if (is_array($post)) {
            // 1th bit - source
            if (!array_key_exists("sourceSystem", $post) || $post["sourceSystem"] == "") {
                $errCode = "1" . $errCode;
            } else {
                $source = $this->opalDB->getSourceDatabaseDetails($post["sourceSystem"]);
                // 2sd bit - source exists
                if (count($source) != 1) {
                    $source = array();
                    $errCode = "1" . $errCode;
                } else {
                    $source = $source[0];
                    $errCode = "0" . $errCode;
                }
            }
            // 3th bit - sourceId
            if (!array_key_exists("sourceId", $post) || $post["sourceId"] == "") {
                $errCode = "1" . $errCode;
            }
        } else {
            $errCode = "111";
        }

        return bindec($errCode);
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
            "PatientSerNum" => $patientSite["PatientSerNum"],
            "sourceName" => $source["SourceDatabaseName"],
            "AppointmentAriaSer" => $post["sourceId"],
            "PrioritySerNum" => 0,
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
            "updatedBy"=>$this->opalDB->getUsername(),
            "SessionId" => $this->opalDB->getSessionId(),
        );

        $appointment = $this->opalDB->findAppointment($source["SourceDatabaseSerNum"],$post["sourceId"]);
        
        if(count($appointment) > 0 ) {
            $appointment = $appointment[0];
            unset($toInsert["sourceName"]);
            $toInsert["SourceDatabaseSerNum"] = $source["SourceDatabaseSerNum"];
            $toInsert["AppointmentSerNum"] = $appointment["AppointmentSerNum"];
        }
        
        $aliasInfos = $this->opalDB->getAlias('Appointment',$post['appointmentTypeCode'], $post['appointmentTypeDescription']);
        if(count($aliasInfos) == 1) {
            $this->_updateAppointmentPending($toInsert);
            unset($toInsert["sourceName"]);
            unset($toInsert["updatedBy"]);
            $toInsert["SourceDatabaseSerNum"] = $source["SourceDatabaseSerNum"];
            $toInsert["AliasExpressionSerNum"] = $aliasInfos[0]['AliasExpressionSerNum'];
            return $this->opalDB->insertAppointment($toInsert);
        } else {
            $toInsert["Level"]  = 1;
            $toInsert["appointmentTypeCode"] = $post['appointmentTypeCode'];
            $toInsert["appointmentTypeDescription"] = $post['appointmentTypeDescription'];
            $toInsert["AppointmentSerNum"] = $this->_insertAppointmentPending($toInsert, $source);
            $this->_insertAppointmentPendingMH($toInsert, $source);
        }
        return false;
    }

    protected function _updateAppointmentPending($toInsert) {
        $pendingAppointment = $this->opalDB->findPendingAppointment($toInsert["SourceDatabaseSerNum"],$toInsert["AppointmentAriaSer"]);
        $this->opalDB->deleteAppointmentPending($pendingAppointment["AppointmentSerNum"]);
    }

    protected function _insertAppointmentPending($toInsert, &$source) {        
        $pendingAppointment = $this->opalDB->findPendingAppointment($source["SourceDatabaseName"],$toInsert["AppointmentAriaSer"]);
        $toInsert["DateModified"] = date("Y-m-d H:i:s");

        if(count($pendingAppointment) > 0) {
            $pendingAppointment = $pendingAppointment[0];
            $toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];            
        }
        unset($toInsert["SourceDatabaseSerNum"]);
        return $this->opalDB->insertPendingAppointment($toInsert);
    }

    protected function _insertAppointmentPendingMH($toInsert, &$source) {
        $pendingAppointment = $this->opalDB->findPendingMHAppointment($source["SourceDatabaseName"],$toInsert["AppointmentAriaSer"]);        
        unset($toInsert["DateAdded"]);        

        if(count($pendingAppointment) > 0) {
            $pendingAppointment = $pendingAppointment[0];
            $toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];
            $toInsert["PendingDate"] = $pendingAppointment["PendingDate"];
            $toInsert["action"] = "UPDATE";
        } else {
            $toInsert["action"] = "INSERT";
            $toInsert["PendingDate"] = date("Y-m-d H:i:s");
        }

        unset($toInsert["SourceDatabaseSerNum"]);
        return $this->opalDB->insertPendingMHAppointment($toInsert);
    }
}
