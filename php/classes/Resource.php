<?php


class Resource extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_RESOURCE, $guestStatus);
    }

    protected function _validateResource(&$post, &$source) {
        $errCode = "";

        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("source", $post) || $post["source"] == "") {
                if(!array_key_exists("source", $post)) $post["source"] = "";
                $errCode = "1" . $errCode;
            }
            else {
                $errCode = "0" . $errCode;
                $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);
                if(count($source) < 1) {
                    $errCode = "1" . $errCode;
                    $source = array();
                }
                else if(count($source) == 1)
                    $source = $source[0];
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates sources found.");
            }

            // 2nd bit
            if (!array_key_exists("appointmentId", $post) || $post["appointmentId"] == "") {
                if(!array_key_exists("appointmentId", $post)) $post["appointmentId"] = "";
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if (!array_key_exists("resources", $post) || !is_array($post["resources"])) {
                if (!array_key_exists("resources", $post)) $post["resources"] = array();
                if(!is_array($post["resources"])) $post["resources"] = array($post["resources"]);
                $errCode = "1" . $errCode;
            }
            else {
                $errorFound = false;
                foreach ($post["resources"] as $resource) {
                    if (!array_key_exists("code", $resource) || !array_key_exists("name", $resource) || !array_key_exists("type", $resource)
                        || $resource["code"] = "" || $resource["name"] = "" || $resource["type"] = "") {
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
                "sourceName"=>"",
                "appointmentId"=>"",
                "resources"=>array(),
            );
            $errCode .= "111";
        }

        return $errCode;
    }

    /**
     * Insert, update and delete resources for specific appointment. Depending if the appointment exists or not, insert
     * into resource or resourcePending table.
     * @param $post - contains the resource info and appointment info for identification
     */
    public function insertResource($post) {
        $this->checkWriteAccess();
        $post = HelpSetup::arraySanitization($post);
        $source = array();
        $errCode = $this->_validateResource($post, $source);
        $errCode = bindec($errCode);

        if ($errCode != 0) {
            $this->opalDB->insertResourcePendingError($post["sourceName"], $post["appointmentId"], json_encode($post["resources"]), json_encode(array("validation" => $errCode)));
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }

        $appointment = $this->opalDB->getAppointment($post["appointmentId"], $source["SourceDatabaseSerNum"]);
        if (count($appointment) < 1)
            echo "Pending process goes here\n\r";
        else if (count($appointment) == 1) {
            $appointment = $appointment[0];
            echo "normal process goes here\n\r";
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates appointments found.");
    }
}