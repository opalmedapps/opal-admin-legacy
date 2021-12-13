<?php

/**
 * Document class
 *
 */

class Document extends Module
{
    public function __construct($guestStatus = false)
    {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }

    protected function _validateDocumentSourceExternalId(&$post, &$patientSite, &$source)  {
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

    protected function _validateInsertDocument(&$post, &$patientSite, &$source) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateDocumentSourceExternalId($post, $patientSite, $source);
        
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation"=>$errCode)));

        //bit 5
        if(!array_key_exists("documentId", $post) || $post["documentId"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 6
        if(!array_key_exists("appovalUserId", $post) || $post["appovalUserId"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 7
        if(array_key_exists("approvalDatetime", $post) && $post["approvalDatetime"] != "") {
            if(!HelpSetup::verifyDate($post["approvalDatetime"], false, 'Y-m-d H:i:s'))
                $errCode = "1" . $errCode;
            else {                
                $errCode = "0" . $errCode;
            }
        }

        //bit 8
        if(!array_key_exists("authorUserId", $post) || $post["authorUserId"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 9
        if(!array_key_exists("noteDescription", $post) || $post["noteDescription"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 10
        if(array_key_exists("noteDatetime", $post) && $post["noteDatetime"] != "") {
            if(!HelpSetup::verifyDate($post["noteDatetime"], false, 'Y-m-d H:i:s'))
                $errCode = "1" . $errCode;
            else {                
                $errCode = "0" . $errCode;
            }
        }

        //bit 11
        if(!array_key_exists("revised", $post) || $post["revised"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 12
        if(!array_key_exists("validEntry", $post) || $post["validEntry"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }
       
        //bit 13
        if(!array_key_exists("fileName", $post) || $post["fileName"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 14
        if(!array_key_exists("creationUserId", $post) || $post["creationUserId"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
        }

        //bit 15
        if(array_key_exists("creationDatetime", $post) && $post["creationDatetime"] != "") {
            if(!HelpSetup::verifyDate($post["creationDatetime"], false, 'Y-m-d H:i:s'))
                $errCode = "1" . $errCode;
            else {                
                $errCode = "0" . $errCode;
            }
        }
        
        return $errCode;
    }

    protected function _insertDocument($post){
        $patientSite = null;
        $source = null;        
        $errCode = $this->_validateInsertDocument($post, $patientSite, $source);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
           
        $doc = $this->opalDB->getDocument($source["SourceDatabaseSerNum"], $post["documentId"]);        
        $toInsert = array(
            "PatientSerNum" => $patientSite["PatientSerNum"],
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "DocumentId" => $post["documentId"],
            "ApprovedBySerNum" => $post["appovalUserId"],
            "ApprovedTimeStamp" => $post["approvalDatetime"],
            "AuthoredBySerNum" => $post["authorUserId"],
            "DateOfService" => $post["noteDatetime"],
            "Revised" => $post["revised"],
            "ValidEntry" => $post["validEntry"],
            "ErrorReasonText" => $post["errorMessage"],
            "OriginalFileName" => $post["fileName"],
            "FinalFileName" => $post["fileName"],
            "CreatedBySerNum" => $post["creationUserId"],
            "CreatedTimeStamp" => $post["creationDatetime"],
            "TransferStatus" => "T",
            "TransferLog" => "Transfert Api",
            "ReadStatus" => 0,
            "LastUpdated" => $post["modifiedDatetime"],
            "SessionId" => $this->opalDB->getSessionId()
        );

        $aliasInfos = $this->opalDB->getAlias('Document',$post['noteDescription'], $post['noteDescription']);        
        if(count($aliasInfos) == 1) {            
            $toInsert["AliasExpressionSerNum"] = $aliasInfos[0]["AliasExpressionSerNum"];
        }

        if (count($doc) == 0) {
            $toInsert["DateAdded"] = date("Y-m-d H:i:s");
            $action = "Document";
            $id = $this->opalDB->insertDocument($toInsert);
            $toInsert["DocumentSerNum"] = $id;
        } else {
            $action = "UpdDocument";
            $toInsert["DocumentSerNum"] = $doc[0]["DocumentSerNum"];
            $toInsert["DateAdded"]      = $doc[0]["DateAdded"];
            $this->opalDB->updateDocument($toInsert);
        }
        
        $patientAccessLevel = $this->opalDB->getPatientAccessLevel($patientSite["PatientSerNum"]);
        
        if(array_key_exists("Accesslevel", $patientAccessLevel) && $patientAccessLevel["Accesslevel"] == 3){            
            $this->_notifyDocumentChange($toInsert,$action);
        }
        
        if ($post["validEntry"] == "Y"  && array_key_exists("documentString", $post) && $post["documentString"] != "")  {
            $filename = basename($post["fileName"]);
            $output_file = CLINICAL_DOC_PATH . $filename;
            $ifp = fopen ( $output_file, "w+") or die("Unable to open file!");
            $data = explode( ',', $post["documentString"]);
            fwrite ( $ifp, base64_decode( $data[0] ) );
            chmod($output_file, 0755);
            fclose( $ifp );
        }        
    }
    /*
     * Insert a new document after validation.
     * @params  $post - array - contains document details
     * @return  200 or error 400 with validation error
     * */
    public function insertDocument($post) {
        $this->checkWriteAccess($post);        
        $post = HelpSetup::arraySanitization($post);
        return $this->_insertDocument($post);
    }

    protected function _notifyDocumentChange($data, $action){
        $notificationControl = $this->opalDB->getNotificationControlDetails($data["PatientSerNum"],$action);
        $controlser         = $notificationControl[0]["NotificationControlSerNum"];
        $title              = $notificationControl[0]["Name"];
        $message            = $notificationControl[0]["Message"];

        $ptdIds = $this->opalDB->getPatientDeviceIdentifiers($data["PatientSerNum"]);       

        if (count($ptdIds) == 0){
            $toInsert = array( 
                "SendStatus" => "W",
                "SendLog" => "Patient has no device identifier! No push notification sent.",
                "DateAdded" => date("Y-m-d H:i:s"),
                "RefTableRowSerNum" => $data["DocumentSerNum"],
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
                    "RefTableRowSerNum" => $data["DocumentSerNum"],
                    "NotificationControlSerNum" => $controlser,
                    "PatientSerNum"=>$data["PatientSerNum"],
                    "PatientDeviceIdentifierSerNum" => $ptdidser
                );                
                $this->opalDB->insertPushNotification($toInsert);
            }
        }
    }
}