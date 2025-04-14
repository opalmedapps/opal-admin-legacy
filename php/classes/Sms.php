<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * SMS class object and methods. WARNING! Because of the nature of the ORMS API with its limited calls, some of them are
 * used multiple times, and they do not allow a full validation of the data to be sent back to the ORMS API. For more
 * info, please see your administrator.
 */

class Sms extends Module {

    /**
     * Constructor. If ORMS is not available, returns an error the mode cannot be found (404)
     * @param false $guestStatus
     */
    public function __construct($guestStatus = false) {
        if(!ORMS_ENABLED)
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_FOUND);
        parent::__construct(MODULE_SMS, $guestStatus);
    }

    /**
     * Get a list of SMS appointments from ORMS
     * @return mixed ORMS API call result
     */
    public function getAppointments() {
        $this->checkReadAccess();
        return $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getSmsAppointments"]);
    }

    /**
     * Validate and sanitize fields used to get messages. WARNING! Because of the nature of the ORMS API, it is
     * impossible to determine if the speciality code is valid or not. For more info, please see your administrator.
     * @param $post array - data received from the front end
     * @param $dataReady array - data ready to be sent to the ORMS API
     * Validation code :    Error validation code is coded as an int of 2 bits (value from 0 to 3). Bits information
     *                      are coded from right to left:
     *                      1: Type is invalid or missing
     *                      2: Speciality code is missing.
     * @return string - error code detected
     */
    protected function _validateGetMessages(&$post, &$dataReady) {
        $validType = $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getTypes"]);
        $errCode = "";
        $dataReady = array();

        if (is_array($post)) {

            // 1st bit
            if (!array_key_exists("type", $post) || (!in_array($post["type"], $validType) && $post["type"] != UNDEFINED_SMS_APPOINTMENT_CODE))
                $errCode = "1" . $errCode;
            else {
                $dataReady["type"] = $post["type"];
                $errCode = "0" . $errCode;
            }

            // 2nd bit
            if (!array_key_exists("specialityCode", $post) || $post["specialityCode"] == "")
                $errCode = "1" . $errCode;
            else {
                $errCode = "0" . $errCode;
                $dataReady["specialityCode"] = $post["specialityCode"];
            }
        } else
            $errCode .= "11";

        return $errCode;
    }

    /**
     * Get a list of events for the given type and speciality. WARNING! Because of the nature of the ORMS API, it is
     * impossible to determine if the speciality code is valid or not. For more info, please see your administrator.
     * @param $post array - data received from the end user
     * @return mixed - results of the ORMS API call
     */
    public function getMessages($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateGetMessages($post, $dataReady);

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $result = $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getMessages"], $post);
        return HelpSetup::arraySanitization($result);
    }

    /**
     * Validate and prepare the change of state of a list of SMS appointment codes received from the user. WARNING!
     * Because of the nature of the ORMS API, it is impossible to determine if the IDs of the SMS appointment codes are
     * valid or not. Plus, since there is only one API method to update the information, it is possible to modify the
     * state and type in batch without any warning. For more info, please see your administrator.
     * @param $post array - data received from the front end
     * @param $dataReady array - data ready to be sent to the ORMS API
     * Validation code :    Error validation code is coded as an int of 1 bits (value from 0 to 1). Bits information
     *                      are coded from right to left:
     *                      1: One of the SMS appointment code is invalid or missing data
     * @return string - error code detected
     */
    protected function _validateActivationState(&$post, &$dataReady) {
        $validType = $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getTypes"]);
        $dataReady = array();
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("data", $post) || !is_array($post["data"]))
                $errCode = "1" . $errCode;
            else {
                $errorFound = false;
                foreach ($post["data"] as $item) {
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

    /**
     * Validate and prepare appointment code status received from the user. WARNING! Because of the nature of the ORMS
     * API, it is impossible to determine if the ID of the SMS appointment code is a valid one or not. For more info,
     * please see your administrator.
     * @param $post array - data received from the front end
     * @param $dataReady array - data ready to be sent to the ORMS API
     * Validation code :    Error validation code is coded as an int of 4 bits (value from 0 to 15). Bit information
     *                      are coded from right to left:
     *                      1: SMS ID is missing
     *                      2: activation state is missing or invalid
     *                      3: type is missing or invalid
     *                      4: appointment code cannot be set to UNDEFINED and active
     * @return string - error code detected
     */
    protected function _validateAppointmentCode(&$post, &$dataReady) {
        $validType = $this->_postRequest(WRM_API_URL.WRM_API_METHOD["getTypes"]);
        $dataReady = array();
        $errCode = "";
        if (is_array($post)) {

            // 1st bit
            if (!array_key_exists("id", $post) || $post["id"] == "")
                $errCode = "1" . $errCode;
            else {
                $dataReady["id"] = $post["id"];
                $errCode = "0" . $errCode;
            }

            // 2nd bit
            if (!array_key_exists("active", $post) || (intval($post["active"]) != 0 && intval($post["active"]) != 1))
                $errCode = "1" . $errCode;
            else {
                $dataReady["active"] = $post["active"];
                $errCode = "0" . $errCode;
            }

            // 3rd bit
            if (!array_key_exists("type", $post) || (!in_array($post["type"], $validType) && $post["type"] != UNDEFINED_SMS_APPOINTMENT_CODE))
                $errCode = "1" . $errCode;
            else {
                $dataReady["type"] = $post["type"];
                $errCode = "0" . $errCode;
            }

            // 4th bit
            if(bindec($errCode) != 0)
                $errCode = "0" . $errCode;
            else {
                if ($post["type"] == UNDEFINED_SMS_APPOINTMENT_CODE && $post["active"] == 1)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }
        }  else
            $errCode = "1111";
        return $errCode;
    }

    /**
     * Update the activation state of a list of SMS appointment codes. WARNING! Because of the nature of
     * the ORMS API, it is impossible to determine if the IDs of the SMS appointment codes are valid or not. Plus, since
     * there is only one API method to update the information, it is possible to modify the state and type in batch
     * without any warning. For more info, please see your administrator.
     * @param $post array - contains the list of SMS appointment code received from the end user.
     */
    public function updateActivationState($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateActivationState($post, $dataReady);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($dataReady as $item)
            $this->_postRequest(WRM_API_URL.WRM_API_METHOD["updateSmsAppointment"], $item);
    }

    /**
     * Validate and update a SMS appointment codes. WARNING! Because of the nature of the ORMS API, it is impossible to
     * determine if the ID of the SMS appointment code is valid or not. Plus, since there is only one API method to
     * update the information. For more info, please see your administrator.
     * @param $post array - contains the list of SMS appointment code received from the end user.
     */
    public function updateAppointmentCode($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateAppointmentCode($post, $dataReady);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $this->_postRequest(WRM_API_URL.WRM_API_METHOD["updateSmsAppointment"], $dataReady);
    }

    /**
     * Validate a SMS message. WARNING! Because of the nature of the ORMS API, it is impossible to determine if the IDs
     * of the SMS appointment codes are valid or not. Plus, since there is only one API method to update the
     * information, it is possible to modify the state and type in batch without any warning. For more info, please see
     * your administrator.
     * @param $post array - data to validate
     * Validation code :    Error validation code is coded as an int of 2 bits (value from 0 to 3). Bits information
     *                      are coded from right to left:
     *                      1: English SMS or ID are missing or invalid
     *                      2: French SMS or ID are missing or invalid
     * @return string - error code
     */
    protected function _validateSmsMessage(&$post, &$dataReady) {
        $dataReady = array();
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("en", $post) || !is_array($post["en"]) || !array_key_exists("id", $post["en"]) || !array_key_exists("sms", $post["en"]) || $post["en"]["id"] == "" || $post["en"]["sms"] == "")
                $errCode = "1" . $errCode;
            else {
                array_push($dataReady, array("messageId" => $post["en"]["id"], "smsMessage" => $post["en"]["sms"]));
                $errCode = "0" . $errCode;
            }

            if (!array_key_exists("fr", $post) || !is_array($post["fr"]) || !array_key_exists("id", $post["fr"]) || !array_key_exists("sms", $post["fr"]) || $post["fr"]["id"] == "" || $post["fr"]["sms"] == "")
                $errCode = "1" . $errCode;
            else {
                array_push($dataReady, array("messageId" => $post["fr"]["id"], "smsMessage" => $post["fr"]["sms"]));
                $errCode = "0" . $errCode;
            }
        }  else
            $errCode = "11";
        return $errCode;
    }

    /*
     * Update the french and english messages of a specific SMS.
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));

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
     *                      1: specialityCode is missing
     * @return string - error code
     */
    protected function _validateSmsType(&$post, &$dataReady) {
        $errCode = "";
        $dataReady = array();

        if (is_array($post)) {
            if (array_key_exists("specialityCode", $post) && $post["specialityCode"] != "")
                $dataReady = array("specialityCode"=>$post["specialityCode"]);
        }
        else
            $errCode = "1";

        return $errCode;
    }

    /**
     * get a list of appointment type for a given speciality code.
     * @param $post - contains the speciality code.
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
        else if($api->getHttpCode() != HTTP_STATUS_SUCCESS) {
            echo $api->getHttpCode();
            print_r($api->getAnswer());
            HelpSetup::returnErrorMessage($api->getHttpCode(), "Error from ORMS: " . $requestResult["error"]);
        }

        return $requestResult["data"];
    }
}
