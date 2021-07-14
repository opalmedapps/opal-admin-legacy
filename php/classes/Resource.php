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
            if (!array_key_exists("sourceId", $post) || $post["sourceId"] == "") {
                if(!array_key_exists("sourceId", $post)) $post["sourceId"] = "";
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if (!array_key_exists("description", $post) || $post["description"] == "") {
                if(!array_key_exists("description", $post)) $post["description"] = "";
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;
        } else {
            $post = array(
                "sourceName"=>"",
                "sourceId"=>"",
                "description"=>"",
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
            $this->opalDB->insertResourcePendingError($post["sourceName"], $post["sourceId"], $post["description"], json_encode(array("validation" => $errCode)));
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
        }
    }
}