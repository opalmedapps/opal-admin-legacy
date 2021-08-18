<?php

/*
 * Sms class objects and method
 * */

class Sms extends Module {
    public function __construct($guestStatus = false) {
        if(!WRM_DB_ENABLED)
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_FOUND);
        parent::__construct(MODULE_SMS, $guestStatus);
    }

    /*
     * Get a list of sms appointments
     * @return  List of sms appointments
     * */
    public function getAppointments() {
        $this->checkReadAccess();
        return $this->_postRequest(WRM_API_URL."/sms/smsAppointment/getSmsAppointments");
    }

    /*
     * Sanitize, validate and get a list of events for the given type and speciality.
     * Validation code :    Error validation code is coded as an int of 2 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment type missing
     *                      2: speciality Code missing
     * @params  $post (array) data received from the front end.
     * @return  List of sms messages for the given type and speciality
     * */
    public function getMessages($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("type", $post) || $post["type"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("specialityCode", $post) || $post["specialityCode"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode .= "11";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getMessages"], $post);
    }

    protected function _validateActivationState(&$post, &$dataReady) {
        $validType = $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getTypes"]);
        $dataReady = array();
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("updateList", $post) || !is_array($post["updateList"]))
                $errCode = "1" . $errCode;
            else {
                $errorFound = false;
                foreach ($post["updateList"] as $item) {
                    if (!array_key_exists("id", $item) || !array_key_exists("active", $item) || !array_key_exists("type", $item) || $item["id"] == "" || $item["active"] == "" ||  !in_array($item["type"], $validType)) {
                        $errorFound = true;
                        break;
                    }
                    else
                        array_push($dataReady, array("id"=>$item["id"], "active"=>$item["active"], "type"=>$item["type"]));
                }
                if($errorFound) {
                    $errCode = "1" . $errCode;
                    $dataReady = array();
                }
                else
                    $errCode = "0" . $errCode;
            }
        }  else
            $errCode = "1";
        return $errCode;
    }

    public function updateActivationState($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateActivationState($post, $dataReady);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($dataReady as $item)
            $this->_postRequest(WRM_API_URL."/sms/smsAppointment/updateSmsAppointment", $item);
    }

    public function updateAppointmentCode($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateActivationState($post, $dataReady);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($dataReady as $item)
            $this->_postRequest(WRM_API_URL."/sms/smsAppointment/updateSmsAppointment", $item);
    }

    /**
     * Validate a SMS message.
     * @param $post array - data to validate
     * Validation code :    Error validation code is coded as an int of 1 bit (value from 0 to 1). Bit information
     *                      are coded from right to left:
     *                      1: Update list missing or invalid
     * @return string - error code
     */
    protected function _validateSmsMessage(&$post, &$dataReady) {
        $dataReady = array();
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("updateList", $post) || !is_array($post["updateList"]))
                $errCode = "1" . $errCode;
            else {
                $errorFound = false;
                foreach ($post["updateList"] as $item) {
                    if (!array_key_exists("messageId", $item) || !array_key_exists("smsMessage", $item) || $item["messageId"] == "" || $item["smsMessage"] == "") {
                        $errorFound = true;
                        break;
                    }
                    else
                        array_push($dataReady, array("messageId" => $item["messageId"], "smsMessage" => $item["smsMessage"]));
                }
                if($errorFound) {
                    $errCode = "1" . $errCode;
                    $dataReady = array();
                }
                else
                    $errCode = "0" . $errCode;
            }
        }  else
            $errCode = "1";
        return $errCode;
    }

    /*
     * Sanitize, validate and update the sms message the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 4 bits. Bit information
     *                      are coded from right to left:
     *                      1: message id missing
     *                      2: message missing
     * @params  $post (array) data received from the front end.
     * */
    public function updateSmsMessage($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = bindec($this->_validateSmsMessage($post, $dataReady));
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($dataReady as $item)
            $this->_postRequest(WRM_API_URL.WRM_API_METHOD["updateMessage"], $item);
    }

    /**
     * Get a list of speciality for appointment message
     * @return mixed - list of speciality group information in database
     */
    public function getSpecialityMessage(){
        $this->checkReadAccess();
        return $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getSpecialityGroups"]);
    }

    /**
     * Validate and sanitize appointment SMS type info.
     * @param $post - data to validate
     * @param array $dataReady - data ready to be sent to the ORMS API
     * Validation code :    Error validation code is coded as an int of 1 bit (value from 0 to 1). Bit information
     *                      are coded from right to left:
     *                      1: post is not an array
     * @return string - error code
     */
    protected function _validateSmsType(&$post, &$dataReady) {
        $errCode = "";
        $dataReady = array();

        if (is_array($post)) {
            if (array_key_exists("specialtyCode", $post) && $post["specialtyCode"] != "")
                $dataReady = array("specialtyCode"=>$post["specialtyCode"]);
        }
        else
            $errCode = "1";

        return $errCode;
    }

    /**
     * get a list of appointment type for a given speciality code.
     * @param $post - contains the specialty code.
     * @return mixed - answer from the ORMS API
     */
    public function getSmsType($post){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateSmsType($post, $dataReady);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getTypes"], $dataReady);
    }

    /**
     * Make a post request to a specified url with a list of post parameters if available. If an error occurs, returns
     * a 502 error.
     * @param $url string - contains the url
     * @param $postParameters array - Contains the parameters for the post. Default empty array
     * @return mixed - data received from OPMS
     */
    protected function _postRequest($url, array $postParameters = array()) {
        $api = new ApiCall(WRM_API_CONFIG);
        $api->setUrl($url);
        $api->setPostFields( json_encode($postParameters,JSON_NUMERIC_CHECK));
        $api->execute();
        $requestResult = json_decode($api->getAnswer(),true);

        if($api->getError())
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to ORMS: " . $api->getError());
        else if($api->getHttpCode() != HTTP_STATUS_SUCCESS)
            HelpSetup::returnErrorMessage($api->getHttpCode(),"Error from ORMS: " . $requestResult["error"]);

        return $requestResult["data"];
    }
}