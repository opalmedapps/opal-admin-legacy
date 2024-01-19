<?php

/**
 * TriggerDocument class
 */

class TriggerDocument extends Trigger
{

    /**
     * Validate the input parameters for patient document
     * Validation code :     
     *                      1st bit invalid or missing MRN
     *                      2nd bit invalid or missing Site
     *                      3rd bit Identifier MRN-site-patient does not exists
     *                      4th bit invalid or missing source system
     *
     * @param array<mixed> $post (Reference) - document parameters
     * @param array<mixed> &$patientSite (Reference) - patient parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateDocumentSourceExternalId(&$post, &$patientSite, &$source)  {
        $patientSite = array();
        $errCode = $this->_validateBasicPatientInfo($post, $patientSite);
                
        // 1st bit - source system
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
     * Validate the input parameters for individual patient document
     * Validation code :     
     *                       1st bit invalid or missing MRN
     *                       2nd bit invalid or missing Site
     *                       3rd bit Identifier MRN-site-patient does not exists
     *                       4th bit invalid or missing source system
     *                       5th bit invalid or missing document ID
     *                       6th bit invalid or missing approval user (staff) ID
     *                       7th bit invalid or missing approval date time
     *                       8th bit invalid or missing author user (staff) ID
     *                       9th bit invalid or missing note description
     *                      10th bit invalid or missing note date time
     *                      11th bit invalid or missing revised flag
     *                      12th bit invalid or missing valid entry flag
     *                      13th bit invalid or missing file name
     *                      14th bit invalid or missing creator user (staff) ID
     *                      15th bit invalid or missing create date time
     *
     * @param array<mixed> $post (Reference) - docuement parameters
     * @param array<mixed> &$patientSite (Reference) - patient parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */    
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

    /**
     * This function insert or update a document informations after its validation.
     * @param  $post : array - details of document information to insert/update.
     * @return  void
     */
    protected function _insertDocument($post){
        $yesterday = strtotime(date("Y-m-d H:i:s",strtotime("-1 hours")));
        $patientSite = null;
        $source = null;        
        $errCode = $this->_validateInsertDocument($post, $patientSite, $source);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
           
        $doc = $this->opalDB->getDocument($source["SourceDatabaseSerNum"], $post["documentId"]);
        $countDoc = count($doc);
        $toInsert = array(
            "PatientSerNum" => $patientSite["PatientSerNum"],
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "DocumentId" => $post["documentId"],
            "ApprovedBySerNum" => $this->opalDB->getStaffDetail($source["SourceDatabaseSerNum"],$post["appovalUserId"])["StaffSerNum"],
            "ApprovedTimeStamp" => $post["approvalDatetime"],
            "AuthoredBySerNum" => $this->opalDB->getStaffDetail($source["SourceDatabaseSerNum"],$post["authorUserId"])["StaffSerNum"],
            "DateOfService" => $post["noteDatetime"],
            "Revised" => $post["revised"],
            "ValidEntry" => $post["validEntry"],
            "ErrorReasonText" => $post["errorMessage"],
            "OriginalFileName" => $post["fileName"],
            "FinalFileName" => $post["fileName"],
            "CreatedBySerNum" => $this->opalDB->getStaffDetail($source["SourceDatabaseSerNum"],$post["creationUserId"])["StaffSerNum"],
            "CreatedTimeStamp" => $post["creationDatetime"],
            "TransferStatus" => "T",
            "TransferLog" => "Transfert Api",
            "ReadStatus" => 0,
            "LastUpdated" => $post["modifiedDatetime"],
            "SessionId" => $this->opalDB->getSessionId()
        );

        $aliasInfos = $this->opalDB->getAlias('Document',$post['noteDescription'], $post['noteDescription']);
        $countAlias = count($aliasInfos);
        if($countAlias == 1) {
            $toInsert["AliasExpressionSerNum"] = $aliasInfos[0]["AliasExpressionSerNum"];
        }

        if ($countDoc == 0) {
            $toInsert["DateAdded"] = date("Y-m-d H:i:s");
            $action = "Document";
            $id = $this->opalDB->insertDocument($toInsert);
            $toInsert["DocumentSerNum"] = $id;
        } else {
            $action = "UpdDocument";
            $doc["ApprovedBySerNum"] = $this->opalDB->getStaffDetail($source["SourceDatabaseSerNum"],$post["appovalUserId"])["StaffSerNum"];
            $doc["ApprovedTimeStamp"] = $post["approvalDatetime"];
            $doc["AuthoredBySerNum"] = $this->opalDB->getStaffDetail($source["SourceDatabaseSerNum"],$post["authorUserId"])["StaffSerNum"];
            $doc["Revised"] = $post["revised"];
            $doc["ValidEntry"] = $post["validEntry"];
            $doc["AliasExpressionSerNum"] = $aliasInfos[0]["AliasExpressionSerNum"];
            $doc["ErrorReasonText"] = $post["errorMessage"];
            $doc["LastUpdated"] = $post["modifiedDatetime"];
            $doc["SessionId"] = $this->opalDB->getSessionId();
            $this->opalDB->updateDocument($toInsert);
        }
        
        $patientAccessLevel = $this->opalDB->getPatientAccessLevel($patientSite["PatientSerNum"]);
        $modifyDatetime = strtotime($post["modifiedDatetime"]);
        if(array_key_exists("Accesslevel", $patientAccessLevel) && $patientAccessLevel["Accesslevel"] == 3 && $modifyDatetime >= $yesterday){
            $this->_notifyChange($toInsert, $action, array(), $toInsert["DocumentSerNum"]);
        }
        
        if (array_key_exists("documentString", $post) && $post["documentString"] != "")  {
            $filename = basename($post["fileName"]);
            $output_file = CLINICAL_DOC_PATH . $filename;
            $ifp = fopen ( $output_file, "w+") or die("Unable to open file!");
            $data = explode( ',', $post["documentString"]);
            fwrite ( $ifp, base64_decode( $data[0] ) );
            chmod($output_file, 0755);
            fclose( $ifp );
        }
    }

    /**
     * Insert a new document after validation.
     * @param  $post - array - contains document details
     * @return void
     */
    public function insertDocument($post) {
        $this->checkWriteAccess($post);        
        $post = HelpSetup::arraySanitization($post);
        $this->_insertDocument($post);
    }    
}