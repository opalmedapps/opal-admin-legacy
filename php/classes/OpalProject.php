<?php

/**
 * OpalProject class
 *
 */
require_once FRONTEND_ABS_PATH . 'publisher'. DIRECTORY_SEPARATOR .  'php'. DIRECTORY_SEPARATOR . 'HospitalPushNotification.php';

abstract class OpalProject
{
    protected $opalDB;

    public function __construct($sessionInfo, $guestStatus) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $sessionInfo,
            $guestStatus
        );
    }

    protected function _insertAudit($module, $method, $arguments, $access, $username = false) {
        $toInsert = array(
            "module"=>$module,
            "method"=>$method,
            "argument"=>json_encode($arguments),
            "access"=>$access,
            "ipAddress"=>HelpSetup::getUserIP(),
        );
        if($username) {
            $toInsert["createdBy"] = $username;
            $this->opalDB->insertAuditForceUser($toInsert);
        }
        else
            $this->opalDB->insertAudit($toInsert);
    }

    /*
    * Get the list of educational materials. Protected function so any module can call it the same way when needed
    * without having to call the module educational materials itself, but cannot be called from outside.
    * @params  void
    * @return  $result - array - list of educational materials
    * */
    protected function _getListEduMaterial() {
        $results = $this->opalDB->getPublishedEducationalMaterial();
        foreach($results as &$row)
            $row["tocs"] = $this->opalDB->getTocsContent($row["serial"]);
        return $results;
    }

    /*
     * Get the details of aneducational material. Protected function so any module can call it the same way when needed
     * without having to call the module educational materials itself, but cannot be called from outside.
     * @params  void
     * @return  $result - array - list of educational materials
     * */
    protected function _getEducationalMaterialDetails($eduId) {
        $results = $this->opalDB->getEduMaterialDetails($eduId);
        $results["tocs"] = $this->opalDB->getTocsContent($results["serial"]);
        return $results;
    }

    /*
     * Get the activate source database (Aria, ORMS, local, etc...)
     * @params  void
     * @return  $assignedDB : array - source database ID
     * */
    protected function _getActiveSourceDatabase(){
        $assigned = $this->opalDB->getActiveSourceDatabase();
        $assigned = HelpSetup::arraySanitization($assigned);
        $assignedDB = array();
        foreach($assigned as $item) {
            array_push($assignedDB, $item["SourceDatabaseSerNum"]);
        }
        return $assignedDB;
    }

    /**
     * Validate basic information info of patient and site and make sure they exist
     * @param $post - contain MRN and site to validate
     * @param $patientSite - hospital site info
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit informations
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     * @return string - validation code in binary
     */
    protected function _validateBasicPatientInfo(&$post, &$patientSite) {
        $errCode = "";

        // 1st bit - MRN
        if(!array_key_exists("mrn", $post) || $post["mrn"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 2nd bit - Site
        if(!array_key_exists("site", $post) || $post["site"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }
        
        // 3rd bit - MRN and site combo must exists
        if(bindec($errCode) != 0) {
            $patientSite = array();
            $errCode = "1" . $errCode;
        } else {            
            $patientSite = $this->opalDB->getPatientSite($post["mrn"], $post["site"]);            

            if(count($patientSite) != 1) {
                $patientSite = array();
                $errCode = "1" . $errCode;
            }
            else {
                $patientSite = $patientSite[0];
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }


    /**
     * Insert and update resources before updating the pivot table for the current resources needed.
     * @param $appointmentId int - ID of the appointment
     * @param $resources array - list of resources to insert and associate with the appointment
     * @param $sourceDatabaseId - ID of the requested source database
     */
    protected function _insertResources($appointmentId, $resources, $sourceDatabaseId) {

        foreach ($resources as $resource) {
            $data = array(
                "SourceDatabaseSerNum"=>$sourceDatabaseId,
                "ResourceCode"=>$resource["code"],
                "ResourceName"=>$resource["name"],
                "ResourceType"=>$resource["type"],
            );
            $rowCount = $this->opalDB->updateResource($data);
            if (intval($rowCount) <= 0)
                $this->opalDB->insertResource($data);
        }

        $resourceAppointmentList = $this->opalDB->getResourceIds($resources, $sourceDatabaseId, $appointmentId);
        $resourceIdList = array();
        foreach ($resourceAppointmentList as $id)
            array_push($resourceIdList, intval($id["ResourceSerNum"]));
        
        $this->opalDB->deleteResourcesForAppointment($appointmentId, $resourceIdList);
        $this->opalDB->insertResourcesForAppointment($resourceAppointmentList);
    }

    /**
     * Validate and sanitize appointment check-in info.
     * @param $post - data for the resource to validate
     * @param $source - contains source details
     * @param $appointment - contains appointment details (if exists)
     * @param $patientInfo - contains patient info (if exists)
     * Validation code :    Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit information
     *                      are coded from right to left:
     *                      1: source name missing or invalid
     *                      2: appointment missing
     *                      3: Duplicate appointments have being found. Contact the administrator ASAP.
     *                      4: MRN and site not found.
     * @return string - error code
     */
    protected function _validateAppointmentCheckIn(&$post, &$source, &$appointment, &$patientInfo) {
        $errCode = "";

        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("source", $post) || $post["source"] == "") {
                if(!array_key_exists("source", $post)) $post["source"] = "";
                $errCode = "1" . $errCode;
            }
            else {
                $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);
                if(count($source) < 1) {
                    $errCode = "1" . $errCode;
                    $source = array();
                }
                else if(count($source) == 1) {
                    $source = $source[0];
                    $errCode = "0" . $errCode;
                }
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates sources found. Contact your administrator.");
            }

            // 2nd bit
            if (!array_key_exists("appointment", $post) || $post["appointment"] == "") {
                if(!array_key_exists("appointment", $post)) $post["appointment"] = "";
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if(bindec($errCode) == 0) {
                $appointment = $this->opalDB->getAppointmentForResource($post["appointment"], $source["SourceDatabaseSerNum"]);
                if(count($appointment) > 1)
                    $errCode = "1" . $errCode;
                else {
                    if(count($appointment) == 1)
                        $appointment = $appointment[0];
                    $errCode = "0" . $errCode;
                }

                // 4th bit
                $patientInfo = $this->opalDB->getFirstMrnSiteBySourceAppointment($post["source"], $post["appointment"]);
                if(count($patientInfo) < 1)
                    $errCode = "1" . $errCode;
                else {
                    $patientInfo = $patientInfo[0];
                    $errCode = "0" . $errCode;
                }

            } else
                $errCode = "1" . $errCode;

        } else
            $errCode .= "1111";

        return $errCode;
    }

    protected function _notifyChange($data, $action, $dynamicKeys, $refTableId){
        
        $notificationControl = $this->opalDB->getNotificationControlDetails($data["PatientSerNum"],$action);        
        $controlser         = $notificationControl[0]["NotificationControlSerNum"];
        $messageTitle       = $notificationControl[0]["Name"];
        $messageTemplate    = $notificationControl[0]["Message"];
        
        $this->_insertNotification($data,$controlser,$refTableId);

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
            $pushNotificationDetail = array( 
                "SendStatus" => "W",
                "SendLog" => "Patient has no device identifier! No push notification sent.",
                "DateAdded" => date("Y-m-d H:i:s"),
                "RefTableRowSerNum" => $refTableId,
                "NotificationControlSerNum" => $controlser,
                "PatientSerNum"=>$data["PatientSerNum"],
                "PatientDeviceIdentifierSerNum" => null
            );    
            $this->opalDB->insertPushNotification($pushNotificationDetail);        
        } else {
            
            foreach($ptdIds as $ptdId) {                
                $ptdidser       = $ptdId["PatientDeviceIdentifierSerNum"];
                $registrationId = $ptdId["RegistrationId"];
                $deviceType     = $ptdId["DeviceType"];
                
                $response = HospitalPushNotification::sendNotification($deviceType, $registrationId, $messageTitle, $message);                               
                
                if ($response["success"] == 1){
                    $sendstatus = "T"; // successful
                    $sendlog    = "Push notification successfully sent! Message: $message";
                } else {
                    $sendstatus = "F"; // failed
                    $sendlog    = "Failed to send push notification! Message: " . $response['error'];
                }

                $pushNotificationDetail = array( 
                    "SendStatus" => $sendstatus,
                    "SendLog" => $sendlog,
                    "DateAdded" => date("Y-m-d H:i:s"),
                    "RefTableRowSerNum" => $refTableId,
                    "NotificationControlSerNum" => $controlser,
                    "PatientSerNum"=>$data["PatientSerNum"],
                    "PatientDeviceIdentifierSerNum" => $ptdidser
                );
                
                $this->opalDB->insertPushNotification($pushNotificationDetail);
            }
        }
    }

    protected function _insertNotification($data, $controlser,$refTableId){
        $aliasExpressionDetail = $this->opalDB->getAliasExpressionDetail($data["AliasExpressionSerNum"]);
        $newNotification = array (
            "PatientSerNum"=>$data["PatientSerNum"],
            "NotificationControlSerNum" => $controlser,
            "RefTableRowSerNum" => $refTableId,
            "DateAdded" => date("Y-m-d H:i:s"),
            "ReadStatus" => 0
        );
        
        if (is_array($aliasExpressionDetail) && array_key_exists("AliasName_FR", $aliasExpressionDetail)) {
            $newNotification["RefTableRowTitle_FR"]  = $aliasExpressionDetail["AliasName_FR"];
        }

        if (is_array($aliasExpressionDetail) && array_key_exists("AliasName_EN", $aliasExpressionDetail)) {
            $newNotification["RefTableRowTitle_EN"]  = $aliasExpressionDetail["AliasName_EN"];
        }
        
        $this->opalDB->insertNotification($newNotification);
    }

    /**
     * Updates the check-in for a particular appointment to checked and send the info to the push notification API. If
     * the call returns an error, a code 502 (bad gateway) is returned to the caller to inform there's a problem with
     * the push notification. Otherwise, a code 200 (all clear) is returned.
     * @param $post array - contains the source name and the external appointment ID
     */
    protected function _updateAppointmentCheckIn(&$post) {
        $today = strtotime(date("Y-m-d H:i:s"));
        $errCode = $this->_validateAppointmentCheckIn($post, $source, $appointment, $patientInfo);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        
        $rowCount = $this->opalDB->updateCheckInForAppointment($source["SourceDatabaseSerNum"], $post["appointment"]);
        
        if($rowCount >= 1) {
            $currentAppointment = $this->opalDB->findAppointment($source["SourceDatabaseSerNum"],$post["appointment"]);
            $currentAppointment = $currentAppointment[0];
            $StartDateTime = strtotime(date("Y-m-d H:i:s"));
            $action = "CheckInNotification";
            $replacementMap = array();
            setlocale(LC_TIME, 'fr_CA');        
            $replacementMap["\$getDateTime"] =  strftime('%R', $StartDateTime);
            setlocale(LC_TIME, 'en_CA');        
            $replacementMap["\$getDateTime"] =  strftime('%l:%M %p', $StartDateTime);
                    
            $scheduledStartTime = strtotime($currentAppointment["ScheduledStartTime"]);
            if ($scheduledStartTime >= $today){
                $this->_notifyChange($currentAppointment, $action, $replacementMap,$post["appointment"]);        
            }            
        }        
    }
}