<?php

/*
 * Sms class objects and method
 * */

class Sms extends Module {

    protected $baseUrl = ORMS_API_BASE_URL;

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_SMS, $guestStatus);
    }

    /*
     * Get a list of sms appointments
     * @return  List of sms appointments
     * */
    public function getAppointments() {
        $this->checkReadAccess();
        return $this->postRequest($this->baseUrl."/sms/smsAppointment/getSmsAppointments");
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

        return $this->postRequest($this->baseUrl."/sms/smsMessage/getMessages", $post);
    }

    /*
     * Sanitize, validate and update the activation status for a list of appointments
     * Validation code :    Error validation code is coded as an int of 3 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment id missing
     *                      2: appointment activation state missing
     *                      3: appointment type missing
     * @params  $post (array) data received from the front end.
     * */
    public function updateActivationState($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        $idCount = 0;
        $activeCount = 0;
        $typeCount = 0;
        if (is_array($post)) {
            if(array_key_exists("updateList", $post) || is_array($post["updateList"])) {
                foreach ($post["updateList"] as $information) {
                    if (is_array($information)){
                        if (array_key_exists("id", $information) && $information["id"] != "")
                            $idCount++;
                        if (array_key_exists("active", $information) && $information["active"] != "")
                            $activeCount++;
                        if (array_key_exists("type", $information) && $information["type"] != "")
                            $typeCount++;
                    }
                }
                if ($idCount != count($post["updateList"]))
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if ($activeCount != count($post["updateList"]))
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if ($typeCount != count($post["updateList"]))
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            } else
                $errCode = "111";
        }else
            $errCode = "111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($post["updateList"] as $information) {
            if($information["type"] == 0) $information["type"] = NULL;
            $this->postRequest($this->baseUrl."/sms/smsAppointment/updateSmsAppointment", $information);
        }
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
        $errCode = "";
        $idCount = 0;
        $messageCount = 0;
        if (is_array($post)) {
            if (array_key_exists("updateList", $post) || is_array($post["updateList"])) {
                foreach ($post["updateList"] as $information) {
                    if (is_array($information)) {
                        if (array_key_exists("messageId", $information) && $information["messageId"] != "")
                            $idCount++;
                        if (array_key_exists("smsMessage", $information) && $information["smsMessage"] != "")
                            $messageCount++;
                    }
                }

                if ($idCount != count($post["updateList"]))
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if ($messageCount != count($post["updateList"]))
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            }
        }

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        foreach ($post["updateList"] as $information) {
            $this->postRequest($this->baseUrl."/sms/smsMessage/updateMessage", $information);
        }
    }

    /*
     * Get a list of speciality for appointment message
     * @return  list of speciality group information in database
     * */
    public function getSpecialityMessage(){
        $this->checkReadAccess();

        return $this->postRequest($this->baseUrl."/hospital/getSpecialityGroups");
    }

    /*
     * Sanitize, validate and get a list of appointment type for the given speciality code
     * If speciality code is not provided, return all existing sms appointment type.
     * @return  list of type in database
     * */
    public function getTypeMessage($post){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);

        if (is_array($post)) {
            if (array_key_exists("specialityCode", $post) && is_string($post["specialityCode"]) && $post["specialityCode"] != "")
                return $this->postRequest($this->baseUrl."/sms/smsMessage/getTypes.php",$post);
            else
                return $this->postRequest($this->baseUrl."/sms/smsMessage/getTypes");
        }
    }

    /*
     * Send the post request to ORMS and get response data. Throw error 400 when get invalid inputs
     * @params  $url            (string) the url link for post request.
     *          $postParameters (array) post parameters received from the front end.
     * @return The response data, null if there's none
     */
    private function postRequest($url, $postParameters = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
        ));
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($postParameters,JSON_NUMERIC_CHECK));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $requestResult = json_decode(curl_exec($ch),TRUE);
        curl_close($ch);
        if($requestResult["status"] != "Success"){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR,$requestResult["error"]);
        }
        return $requestResult["data"];
    }
}