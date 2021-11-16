<?php

/**
 * Appointment class
 *
 */
require_once('/var/www/html/opalAdmin/publisher/php/HospitalPushNotification.php');

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
            $toUpdate = $currentAppointment[0];
            $toUpdate["Status"] = APPOINTMENT_STATUS_CODE_DELETED;
            $toUpdate["State"] = APPOINTMENT_STATE_CODE_DELETED;
            $toUpdate["SourceDatabaseSerNum"] = $source["SourceDatabaseSerNum"];
            $OStartDateTime = strtotime($toUpdate["ScheduledStartTime"]); 

            if ($post["status"] == "Cancelled"){
                $action = "AppointmentCancelled";
                $replacementMap = array();
                setlocale(LC_TIME, 'fr_CA');                                        
                $replacementMap["\$oldAppointmentDateFR"] =  strftime('%A %d %B %Y', $OStartDateTime);                
                $replacementMap["\$oldAppointmentTimeFR"] =  strftime('%R', $OStartDateTime);

                setlocale(LC_TIME, 'en_CA');
                $replacementMap["\$oldAppointmentDateEN"] =  strftime('%A, %B %e, %Y', $OStartDateTime);
                $replacementMap["\$oldAppointmentTimeEN"] =  strftime('%l:%M %p', $OStartDateTime);
                
                $this->_notifyAppointmentChange($toUpdate,  $action, $replacementMap);
            }

            $this->opalDB->deleteAppointment($toUpdate);

        } else if(count($pendingAppointment) == 1) {
            $toInsert = $pendingAppointment[0];
            $toInsert["Status"] = APPOINTMENT_STATUS_CODE_DELETED;
            $toInsert["State"] = APPOINTMENT_STATUS_CODE_DELETED;
            $toInsert["DateModified"] = date("Y-m-d H:i:s");            
            $toInsert["sourceName"] = $source["SourceDatabaseName"];
            $this->opalDB->insertPendingAppointment($toInsert);

            unset($toInsert["DateModified"]);
            $this->_insertAppointmentPendingMH($toInsert, $source);            
            
        } else if (count($currentAppointment) < 1 && count($pendingAppointment) < 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, "Appointment not found.");
        }      
    }

    /**
     * Validate basic information of a specific database source.
     * @param  $post : array - Contains the following information
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
        $post["scheduledTimestamp"] = "2021-11-15 23:30:00";
        $aliasInfos = $this->opalDB->getAlias('Appointment',$post['appointmentTypeCode'], $post['appointmentTypeDescription']);
        if(count($aliasInfos) == 1) {
            $this->_updateAppointmentPending($toInsert);
            unset($toInsert["sourceName"]);
            unset($toInsert["updatedBy"]);
            $toInsert["SourceDatabaseSerNum"] = $source["SourceDatabaseSerNum"];
            $toInsert["AliasExpressionSerNum"] = $aliasInfos[0]['AliasExpressionSerNum'];
            $SStartDateTime = strtotime($post["scheduledTimestamp"]);
            $OStartDateTime = strtotime($appointment["ScheduledStartTime"]);            

            if ($SStartDateTime <> $OStartDateTime) {
                //if difference is greater than an hour
		        // 2019-06-12 : Change from 1 hour to 2 hours by John's request
                $hourdiff = abs(round(($SStartDateTime - $OStartDateTime)/3600, 1));
                print_r("Difference entre " . $appointment["ScheduledStartTime"] . " et " . $post["scheduledTimestamp"] . " est de " . $hourdiff ."\n\n");
                                
                if ($hourdiff >= 2) {
                    $action = 'AppointmentTimeChange';                                  
                    $replacementMap = array();                
                    setlocale(LC_TIME, 'fr_CA');                                        
                    $replacementMap["\$oldAppointmentDateFR"] =  strftime('%A %d %B %Y', $OStartDateTime);
                    $replacementMap["\$newAppointmentDateFR"] =  strftime('%A %d %B %Y', $SStartDateTime);
                    $replacementMap["\$oldAppointmentTimeFR"] =  strftime('%R', $OStartDateTime);
                    $replacementMap["\$newAppointmentTimeFR"] =  strftime('%R', $SStartDateTime);

                    setlocale(LC_TIME, 'en_CA');
                    $replacementMap["\$oldAppointmentDateEN"] =  strftime('%A, %B %e, %Y', $OStartDateTime);
                    $replacementMap["\$newAppointmentDateEN"] =  strftime('%A, %B %e, %Y', $SStartDateTime);
                    $replacementMap["\$oldAppointmentTimeEN"] =  strftime('%l:%M %p', $OStartDateTime);
                    $replacementMap["\$newAppointmentTimeEN"] =  strftime('%l:%M %p', $SStartDateTime);

                    $this->_notifyAppointmentChange($toInsert, $action, $replacementMap);
                }
            }
            
            return $this->opalDB->insertAppointment($toInsert);
        } else {
            $toInsert["Level"]  = 1;
            $toInsert["appointmentTypeCode"] = $post['appointmentTypeCode'];
            $toInsert["appointmentTypeDescription"] = $post['appointmentTypeDescription'];            
            $toInsert["ID"] = $this->_insertAppointmentPending($toInsert, $source);
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
            $toInsert["ID"] = $pendingAppointment["ID"];            
            //$toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];            
        }
        unset($toInsert["SourceDatabaseSerNum"]);
        unset($toInsert["AppointmentSerNum"]);
        return $this->opalDB->insertPendingAppointment($toInsert);
    }

    protected function _insertAppointmentPendingMH($toInsert, &$source) {
        $pendingAppointment = $this->opalDB->findPendingMHAppointment($source["SourceDatabaseName"],$toInsert["AppointmentAriaSer"]);        
        unset($toInsert["DateAdded"]);        

        if(count($pendingAppointment) > 0) {
            $pendingAppointment = $pendingAppointment[0];
            //$toInsert["AppointmentSerNum"] = $pendingAppointment["AppointmentSerNum"];
            $toInsert["AppointmentPendingId"] = $pendingAppointment["AppointmentPendingId"];
            
            $toInsert["PendingDate"] = $pendingAppointment["PendingDate"];
            $toInsert["action"] = "UPDATE";
        } else {
            $toInsert["action"] = "INSERT";
            $toInsert["PendingDate"] = date("Y-m-d H:i:s");
        }

        unset($toInsert["SourceDatabaseSerNum"]);
        unset($toInsert["AppointmentSerNum"]);
        unset($toInsert["ID"]);
        return $this->opalDB->insertPendingMHAppointment($toInsert);
    }

    /**
     * Validate basic information of a specific database source.
     * @param  $post : array - Contains the following information
     *                          sourceSystem : Source database of appointment (i.e. Aria, Medivisit, Mosaic, etc.)
     *                          sourceId : Source system unique appointment ID (i.e. YYYYA9999999, 9999999)
     *                          source : Source database of the diagnosis (mandatory)
     * Validation code :
     *                      1: source invalid or missing
     */
    protected function _validateUpdateAppointmentStatus(&$post, &$source)
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
     * Update a specific appointment status.
     * @params  $post : array - contains the following info:
     *                          sourceSystem : Source database of appointment (i.e. Aria, Medivisit, Mosaic, etc.)
     *                          sourceId : Source system unique appointment ID (i.e. YYYYA9999999, 9999999)
     * @return  int - number of records update
     * */
    public function updateAppointmentStatus($post)
    {
        $source = null;
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateUpdateAppointmentStatus($post, $source);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        $currentAppointment = $this->opalDB->findAppointment($source["SourceDatabaseSerNum"],$post["sourceId"]);
        $pendingAppointment = $this->opalDB->findPendingAppointment($source["SourceDatabaseName"],$post["sourceId"]);
        
        if (count($currentAppointment) > 1){
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates appointment found.");                    
        } else if (count($currentAppointment) == 1){
            $toUpdate = $currentAppointment[0];
            $toUpdate["Status"] = $post["status"];
            $toUpdate["State"] = APPOINTMENT_STATE_CODE_ACTIVE;
            $toUpdate["SourceDatabaseSerNum"] = $source["SourceDatabaseSerNum"];            
            $OStartDateTime = strtotime($toUpdate["ScheduledStartTime"]); 
            if ($post["status"] == "Cancelled"){
                $action = "AppointmentCancelled";
                $replacementMap = array();
                setlocale(LC_TIME, 'fr_CA');                                        
                $replacementMap["\$oldAppointmentDateFR"] =  strftime('%A %d %B %Y', $OStartDateTime);                
                $replacementMap["\$oldAppointmentTimeFR"] =  strftime('%R', $OStartDateTime);

                setlocale(LC_TIME, 'en_CA');
                $replacementMap["\$oldAppointmentDateEN"] =  strftime('%A, %B %e, %Y', $OStartDateTime);
                $replacementMap["\$oldAppointmentTimeEN"] =  strftime('%l:%M %p', $OStartDateTime);
                
                $this->_notifyAppointmentChange($toUpdate, $action, $replacementMap);
            }

            return $this->opalDB->updateAppointment($toUpdate);

        } else if(count($pendingAppointment) == 1) {
            $toInsert = $pendingAppointment[0];
            $toInsert["Status"] = $post["status"];
            $toUpdate["State"] = APPOINTMENT_STATE_CODE_ACTIVE;
            $toInsert["DateModified"] = date("Y-m-d H:i:s");
            
            $this->opalDB->insertPendingAppointment($toInsert);

            unset($toInsert["DateModified"]);
            $this->_insertAppointmentPendingMH($toInsert, $source);            
            
        } else if (count($currentAppointment) < 1 && count($pendingAppointment) < 1) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, "Appointment not found.");
        }      
    }

    protected function _notifyAppointmentChange($data, $action, $dynamicKeys){
        
        $notificationControl = $this->opalDB->getNotificationControlDetails($data["PatientSerNum"],$action);        
        $controlser         = $notificationControl[0]["NotificationControlSerNum"];
        $title              = $notificationControl[0]["Name"];
        $messageTemplate    = $notificationControl[0]["Message"];
        
        $patterns           = array();
        $replacements       = array();
        $indice             = 0;   
        foreach($dynamicKeys as $key=>$val) {
            $patterns[$indice] = $key;
            $replacements[$indice] = $val;
            $indice +=1;
        }

        ksort($patterns);
        ksort($replacements);
        $message =  str_replace($patterns, $replacements, $messageTemplate);
        
        $ptdIds = $this->opalDB->getPatientDeviceIdentifiers($data["PatientSerNum"]);
        if (count($ptdIds) == 0){
            $toInsert = array( 
                "SendStatus" => "W",
                "SendLog" => "Patient has no device identifier! No push notification sent.",
                "DateAdded" => date("Y-m-d H:i:s"),
                "RefTableRowSerNum" => $data["AppointmentAriaSer"],
                "NotificationControlSerNum" => $controlser,
                "PatientSerNum"=>$data["PatientSerNum"],
                "PatientDeviceIdentifierSerNum" => null
            );    
            $this->opalDB->insertPushNotification($toInsert);        
        } else {
            
            foreach($ptdIds as $ptdId) {                
                $ptdidser       = $ptdId["PatientDeviceIdentifierSerNum"];
                $registrationId = $ptdId["RegistrationId"];
                $deviceType     = $ptdId["DeviceType"];

                $response = array("success" => 1);
                $response = HospitalPushNotification::sendNotification($deviceType, $registrationId, $title, $message);                               
                
                if ($response["success"] == 1){
                    $sendstatus = "T"; // successful
                    $sendlog    = "Push notification successfully sent! Message: $message";
                } else {
                    $sendstatus = "F"; // failed
                    $sendlog    = "Failed to send push notification! Message: " . $response['error'];
                }

                $toInsert = array( 
                    "SendStatus" => $sendstatus,
                    "SendLog" => $sendlog,
                    "DateAdded" => date("Y-m-d H:i:s"),
                    "RefTableRowSerNum" => $data["AppointmentAriaSer"],
                    "NotificationControlSerNum" => $controlser,
                    "PatientSerNum"=>$data["PatientSerNum"],
                    "PatientDeviceIdentifierSerNum" => $ptdidser
                );
                
                $this->opalDB->insertPushNotification($toInsert);
            }
        }
    }
}
