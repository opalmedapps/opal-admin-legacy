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

        //bit 
        if(!array_key_exists("patientVisitId", $post) || $post["patientVisitId"] == ""){
            $errCode = "1" . $errCode;
        } else{
            $errCode = "0" . $errCode;
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
                
        $toInsert = array(
            "PatientSerNum" => $patientSite["PatientSerNum"],
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "DocumentId" => $post["uuid"],
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
            "TransferLog" => "",
            "ReadStatus" => 0,
            "SessionId" => $this->opalDB->getSessionId(),
            "DateAdded" => date("Y-m-d H:i:s")            
        );

        $aliasInfos = $this->opalDB->getAlias('Document',$post['noteDescription'], $post['noteDescription']);
        if(count($aliasInfos) == 1) {
            $toInsert["AliasExpressionSerNum"] = $aliasInfos["AliasExpressionSerNum"];
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

}