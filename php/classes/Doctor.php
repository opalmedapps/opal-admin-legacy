<?php

/**
 * Doctor class
 */
class Doctor extends Module
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
        if (!array_key_exists("sourceSystem", $post) || $post["sourceSystem"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $source = $this->opalDB->getSourceDatabaseDetails($post["sourceSystem"]);            
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
     * Validate the input parameters for individual doctor
     * Validation code :     
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> $post - doctor parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateDoctor(&$post,  &$source)
    {
        $errCode = $this->_validateSourceExternalId($post, $source);
        
        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        //bit 5
        if (!array_key_exists("resourceId", $post) || $post["resourceId"] == "") {
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
     * Validate the input parameters for individual doctor
     * Validation code :     
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> $post - doctor parameters
     * @param array<mixed> &$patientSite (Reference) - patient parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validatePatientDoctor(&$post, &$patientSite, &$source, &$doctor)
    {
        $patientSite = array();
        $doctor = array();
        $errCode = $this->_validateBasicPatientInfo($post, $patientSite);        
        $errCode = $errCode . $this->_validateSourceExternalId($post, $source);

        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

                      

        if (!array_key_exists("resourceId", $post) || $post["resourceId"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
            $resource = $this->opalDB->getDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);
            
            if ($resource === false) {
                $errCode = $errCode . '1';
            } else {
                $errCode = $errCode . '0';
                $doctor = $this->opalDB->getDoctor($resource["ResourceSerNum"]);
            
                if ($doctor === false) {
                    $errCode = $errCode . '1';
                } else {
                    $errCode = $errCode . '0';
                }
            }
        }        

        if (!array_key_exists("oncologistFlag", $post) || $post["oncologistFlag"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        if (!array_key_exists("primaryOncologistFlag", $post) || $post["primaryOncologistFlag"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }
        

        return $errCode;
    }

    /** 
     * Insert or update doctor resource after validation.
     * @param  $post - array - contains document details
     * @return void
     */
    public function updateDoctor($post)
    {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updateDoctor($post);
    }

    /** 
     * Insert or update patient doctor after validation.
     * @param  $post - array - contains document details
     * @return void
     */
    public function updatePatientDoctor($post)
    {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updatePatientDoctor($post);
    }


    protected function _updateResource(&$post, &$source, &$resourceData)
    {
        $resource = $this->opalDB->getDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);

        if ($resource === false) {
            $resourceData = array(
                "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
                "ResourceName" => $post["alias"] ,
                "ResourceType" => "Doctor",
            );

            if ($post["sourceSystem"] == 'Aria') {
                $resourceData["ResourceAriaSer"] = $post["resourceId"];
                $resourceData["ResourceCode"] = "";
            } else {
                $resourceData["ResourceAriaSer"] = "";
                $resourceData["ResourceCode"] = $post["resourceId"];
            }
            $this->opalDB->insertResource($resourceData);
        } else {
            $resourceData = $resource;
            $resourceData["ResourceName"] = $post["alias"];

            $this->opalDB->updateDoctorResource($resourceData);
        }        
    }

    protected function _updateDoctor(&$post)
    {

        $source = null;
        $resourceData = null;

        $errCode = $this->_validateDoctor($post, $source);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $this->_updateResource($post, $source, $resourceData);

        $doctor = $this->opalDB->getDoctor($resourceData["ResourceSerNum"]);

        $doctorData = array(
            "ResourceSerNum" => $resourceData["ResourceSerNum"],
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "LastName" => $post["lastName"],
            "FirstName" => $post["firstName"],
            "Role" => "",
            "Workplace" => "",
            "Email" => "",
            "Phone" => "",
            "Address" => "",
            "ProfileImage" => "",
            "BIO_EN" => "",
            "BIO_FR" => "",
        );

        if ($post["sourceSystem"] == 'Aria') {
            $doctorData["DoctorAriaSer"] = $post["resourceId"];
        } else {
            $doctorData["DoctorAriaSer"] = "";
        }

        if ($doctor !== false) {
            $doctorData = $doctor;
        }

        $doctorData["LastName"] = $post["lastName"];
        $doctorData["FirstName"] = $post["firstName"];

        if ($doctor === false) {
            $this->opalDB->insertDoctor($doctorData);            
        } else {            
            $this->opalDB->updateDoctor($doctorData);
        }
    }


    protected function _updatePatientDoctor(&$post)
    {
        $source = null;
        $resourceData = null;
        $patientSite = null;
        $doctor = null;

        $errCode = $this->_validatePatientDoctor($post, $patientSite, $source, $doctor);                
        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        
        $patientDoctor = $this->opalDB->getPatientDoctor($doctor["DoctorSerNum"],$patientSite["PatientSerNum"]);

        if ($patientDoctor === false){
            $doctorData = array(
                "PatientSerNum" => $patientSite["PatientSerNum"],
                "DoctorSerNum" => $doctor["DoctorSerNum"],
                "OncologistFlag" => $post["oncologistFlag"],
                "PrimaryFlag" => $post["primaryOncologistFlag"],
            );
            
            $this->opalDB->insertPatientDoctor($doctorData);
        } else {
            $patientDoctor["OncologistFlag"] = $post["oncologistFlag"];
            $patientDoctor["PrimaryFlag"] = $post["primaryOncologistFlag"];
            $this->opalDB->updatePatientDoctor($patientDoctor);
        }
    }
}
