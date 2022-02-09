<?php

/**
 * Staff class
 */
class Staff extends Module
{

    public function __construct($guestStatus = false)
    {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }


    /**
     * Validate the input parameters 
     * Validation code :     
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> $post - document parameters
     * @param array<mixed> &$patientSite (Reference) - patient parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateSourceExternalId(&$post,  &$source)
    {

        $errCode = "";

        // 1st bit - source system
        if (!array_key_exists("source", $post) || $post["source"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);            
            if (count($source) != 1) {
                $source = array();
                $errCode = "1" . $errCode;
            } else {
                $source = $source[0];
                $errCode = "0" . $errCode;
            }
        }
        
        return $errCode;
    }

        /**
     * Validate the input parameters for individual staff
     * Validation code :     
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> $post - staff parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateStaff(&$post,  &$source)
    {
        $errCode = $this->_validateSourceExternalId($post, $source);
        
        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        //bit 5
        if (!array_key_exists("userId", $post) || $post["userId"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 5
        if (!array_key_exists("firstName", $post) || $post["firstName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 5
        if (!array_key_exists("lastName", $post) || $post["lastName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        return $errCode;
    }

    /** 
     * Insert or update staff resource after validation.
     * @param  $post - array - contains document details
     * @return void
     */
    public function updateStaff($post)
    {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updateStaff($post);
    }

    protected function _updateStaff(&$post)
    {

        $source = null;       

        $errCode = $this->_validateStaff($post, $source);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $staff = $this->opalDB->getStaff($post["userId"],$source["SourceDatabaseSerNum"]);

        $staffData = array(            
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "StaffId" => $post["userId"],
            "LastName" => $post["lastName"],
            "FirstName" => $post["firstName"],
            "LastUpdated" => $post["lastUpdated"],
        );

        if ($staff !== false) {
            $staffData = $staff;
        }

        $staffData["LastName"] = $post["lastName"];
        $staffData["FirstName"] = $post["firstName"];
        $staffData["LastUpdated"] = $post["lastUpdated"];

        if ($staff === false) {
            $this->opalDB->insertStaff($staffData);            
        } else {            
            $this->opalDB->updateStaff($staffData);
        }
    }
}
