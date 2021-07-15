<?php


class Resource extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_RESOURCE, $guestStatus);
    }

    protected function _validateResource(&$post, &$source) {
        $errCode = "";

        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("sourceName", $post) || $post["sourceName"] == "") {
                if(!array_key_exists("sourceName", $post)) $post["sourceName"] = "";
                $errCode = "1" . $errCode;
            }
            else {
                $errCode = "0" . $errCode;
                $source = $this->opalDB->getSourceDatabaseDetails($post["sourceName"]);
                if(count($source) < 1)
                    $source = array();
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
            if (!array_key_exists("resources", $post) || !is_array($post["resources"]) || !HelpSetup::hasStringKeys($post["resources"])) {
                if (!array_key_exists("resources", $post)) $post["resources"] = array();
                if(!is_array($post["resources"])) $post["resources"] = array($post["resources"]);
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;
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
    }
}