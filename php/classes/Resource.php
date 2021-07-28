<?php


class Resource extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_RESOURCE, $guestStatus);
    }

    /**
     * Validate and sanitize resource info.
     * @param $post - data for the resource to validate
     * @param $source - contains source details
     * @param $appointment - contains appointment details (if exists)
     * Validation code :    Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit informations
     *                      are coded from right to left:
     *                      1: source name missing or invalid
     *                      2: appointment missing
     *                      3: Duplicate appointments have being found. Contact the administrator ASAP.
     *                      4: resources missing or invalid
     * @return string - error code
     */
    protected function _validateResources(&$post, &$source, &$appointment) {
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
            }

            // 4rd bit
            if (!array_key_exists("resources", $post) || !is_array($post["resources"])) {
                if (!array_key_exists("resources", $post)) $post["resources"] = array();
                if(!is_array($post["resources"])) $post["resources"] = array($post["resources"]);
                $errCode = "1" . $errCode;
            }
            else {
                $errorFound = false;
                foreach ($post["resources"] as $resource) {
                    if (!array_key_exists("code", $resource) || !array_key_exists("name", $resource) || !array_key_exists("type", $resource)
                        || $resource["code"] == "" || $resource["name"] == "" || $resource["type"] == "") {
                        $errorFound = true;
                        break;
                    }
                }
                if($errorFound)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }
        } else {
            $post = array(
                "source"=>"",
                "appointment"=>"",
                "resources"=>array(),
            );
            $errCode .= "1111";
        }

        return $errCode;
    }

    /**
     * Insert, update and delete resources for specific appointment. Depending if the appointment exists or not, insert
     * into resource or resourcePending table.
     * @param $post - contains the resource info and appointment info for identification
     */
    public function insertResource($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $source = array();
        $appointment = array();
        $errCode = $this->_validateResources($post, $source, $appointment);
        $errCode = bindec($errCode);

        if ($errCode != 0) {
            $this->opalDB->insertResourcePendingError($post["source"], $post["appointment"], json_encode($post["resources"]), json_encode(array("validation" => $errCode)));
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }
        if (empty($appointment))
            $this->_insertResourcePending($post);
        else
            $this->_insertResources($appointment["AppointmentSerNum"], $post["resources"], $source["SourceDatabaseSerNum"]);
    }

    /**
     * Insert a resource in the resource pending table. The logic is the following: the function will try 6 times every
     * 5 seconds to insert/update a resource in resource pending. If the resource does not exists, it will insert right
     * away (but if in the meantime, an entry was created in the resource pending table with same value, it will be
     * rejected). If the resource exists already, it will update it only if the level is set to 1. If it is not, it will
     * wait 5 seconds and try again. After 6 tries, an error is returned.
     * @param $post array - contains the source name, external appointment ID and a string of json of resources
     */
    protected function _insertResourcePending(&$post) {
        $data = array(
            "sourceName"=>$post["source"],
            "appointmentId"=>$post["appointment"],
            "resources"=>json_encode($post["resources"])
        );

        for($cpt = 0; $cpt < 6; $cpt++) {
            $resourcePending = $this->opalDB->getResourcePending($post["source"], $post["appointment"]);
            if(count($resourcePending) < 1) {
                $rowCount = $this->opalDB->insertPendingResource($data);
                if($rowCount <= 0) {
                    $this->opalDB->insertResourcePendingError($post["source"], $post["appointment"], json_encode($post["resources"]), "Resource pending already exists.");
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Resource pending already exists.");
                }
                break;
            }
            else if(count($resourcePending) == 1) {
                if($resourcePending[0]["level"] == RESOURCE_LEVEL_READY) {
                    $this->opalDB->updatePendingResource($data);
                    break;
                }
                else
                    sleep(5);
            }
            else {
                $this->opalDB->insertResourcePendingError($post["source"], $post["appointment"], json_encode($post["resources"]), "Duplicates resource pending found.");
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates resource pending found.");
                break;
            }
        }

        if($cpt >= 6) {
            $this->opalDB->insertResourcePendingError($post["source"], $post["appointment"], json_encode($post["resources"]), "Error resource pending failed.");
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Error resource pending failed.");
        }
    }
}