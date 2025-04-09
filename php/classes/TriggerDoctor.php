<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * TriggerDoctor class
 */
class TriggerDoctor extends Trigger
{
    /**
     * Validate input parameters
     * Validation code :
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> &$post (Reference) - doctor parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code
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
     * Validate input parameters for individual doctor
     * Validation code :
     *                      1st bit invalid or missing source system
     *                      2nd bit invalid or missing resource ID
     *                      3rd bit invalid or missing first name
     *                      4th bit invalid or missing last name
     *
     * @param array<mixed> &$post (Reference) - doctor parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateDoctor(&$post,  &$source)
    {
        $errCode = $this->_validateSourceExternalId($post, $source);

        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        //bit 2
        if (!array_key_exists("resourceId", $post) || $post["resourceId"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 3
        if (!array_key_exists("firstName", $post) || $post["firstName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 4
        if (!array_key_exists("lastName", $post) || $post["lastName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        return $errCode;
    }

    /**
     * Validate the input parameters of patient doctor
     * Validation code :
     *                      1st bit invalid or missing MRN
     *                      2nd bit invalid or missing Site
     *                      3rd bit Identifier MRN-site-patient does not exists
     *                      4th bit invalid or missing source system
     *                      5th bit invalid or missing resource ID
     *                      6th bit invalid or missing doctor resource info
     *                      7th bit invalid or missing Is an Oncologist Flag
     *                      8th bit invalid or missing Is an Primary Oncologist Flag
     *
     * @param array<mixed> $post (Reference) - doctor parameters
     * @param array<mixed> &$patientSite (Reference) - patient parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @param array<mixed> &$doctor (Reference) - source parameters
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
            if ($post["sourceSystem"] == 'Aria') {
                $resource = $this->opalDB->getAriaDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);
            } else {
                $resource = $this->opalDB->getNonAriaDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);
            }

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

    /**
     * This function insert or update a doctor resource informations after its validation.
     * @param  $post : array - details of resource information to insert/update.
     * @param array<mixed> $post (Reference) - resource parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @param array<mixed> &$resourceData (Reference) - resource infos
     */
    protected function _updateResource(&$post, &$source, &$resourceData)
    {
        if ($post["sourceSystem"] == 'Aria') {
            $resource = $this->opalDB->getAriaDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);
        } else {
            $resource = $this->opalDB->getNonAriaDoctorResource($source["SourceDatabaseSerNum"], $post["resourceId"]);
        }

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
                $resourceData["ResourceAriaSer"] = 0;
                $resourceData["ResourceCode"] = $post["resourceId"];
            }
            $resourceData['ResourceSerNum'] = $this->opalDB->insertResource($resourceData);
        } else {
            $resourceData = $resource;
            $resourceData["ResourceName"] = $post["alias"];

            $this->opalDB->updateDoctorResource($resourceData);
        }
    }

    /**
     * This function insert or update a doctor informations after its validation.
     * @param array<mixed> $post - details of doctor information to insert/update.
     * @return void
     */
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
            $doctorData["DoctorAriaSer"] = 0;
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

    /**
     * This function insert or update a patient doctor informations after its validation.
     * @param array<mixed> $post - details of patient doctor information to insert/update.
     * @return void
     */
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
