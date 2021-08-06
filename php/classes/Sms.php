<?php

/*
 * Sms class objects and method
 * */

class Sms extends Module {

    protected $ormsDB;
    protected $baseUrl = "http://192.168.146.3//php/api/public/v1";

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_SMS, $guestStatus);
        if($_SESSION["userAccess"][MODULE_SMS]) {
            $this->ormsDB = new DatabaseOrms(
                WRM_DB_HOST,
                WRM_DB_NAME,
                WRM_DB_PORT,
                WRM_DB_USERNAME,
                WRM_DB_PASSWORD,
                false,
                $_SESSION["ID"]
            );
        }
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
     *                      2: speciality missing
     * @params  $post (array) data received from the front end.
     * @return  List of events for the given type and speciality
     * */
    public function getEvents($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("type", $post) || $post["type"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("speciality", $post) || $post["speciality"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode .= "11";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->ormsDB->getEventsForAppointment($post["type"],$post["speciality"]);
    }

    /*
     * Sanitize, validate and get the message for the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 4 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment type missing
     *                      2: speciality missing
     *                      3: event missing
     *                      4: language missing
     * @params  $post (array) data received from the front end.
     * @return  List of events base on input.
     * */
    public function getMessage($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("type", $post) || $post["type"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("speciality", $post) || $post["speciality"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("event", $post) || $post["event"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("language", $post) || ($post["language"] != 1 && $post["language"] != 2))
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode .= "1111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        if ($post["language"] == 1)
            return $this->ormsDB->getMessageForAppointment($post["speciality"],$post["type"],$post["event"],"French");
        else if($post["language"] == 2)
            return $this->ormsDB->getMessageForAppointment($post["speciality"],$post["type"],$post["event"],"English");
    }

    /*
     * Sanitize, validate and update the activation status for a list of appointments
     * Validation code :    Error validation code is coded as an int of 3 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment id missing
     *                      2: appointment activation state missing
     *                      3: appointment type missing
     * @params  $post (array) data received from the front end.
     * @return  Number records updated in database
     * */
    public function updateActivationState($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        $response = 0;
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
        return $response;
    }

    /*
     * Sanitize, validate and update the type for an appointments
     * Validation code :    Error validation code is coded as an int of 3 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment type state missing
     *                      2: appointment code id missing
     *                      3: resource series number missing
     * @params  $post (array) data received from the front end.
     * @return  1 for success 0 for fail
     * */
    public function updateAppointmentType($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("type", $post) || $post["type"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("id", $post) || $post["id"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        }else
            $errCode .= "11";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        if($post['type'] == 0)
            return $this->ormsDB->updateAppointmentType(NULL,$post['id']);
        else
            return $this->ormsDB->updateAppointmentType($post['type'],$post['id']);
    }

    /*
     * Sanitize, validate and update the sms message the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 4 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment type missing
     *                      2: speciality missing
     *                      3: event missing
     *                      4: language missing
     * @params  $post (array) data received from the front end.
     * @return  0 for fail, success otherwise
     * */
    public function updateSmsMessage($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("type", $post) || $post["type"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("specialityId", $post) || $post["specialityId"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("event", $post) || $post["event"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if(array_key_exists("message", $post) && is_array($post["message"])) {
                if (!array_key_exists("English", $post["message"]) || $post["message"]["English"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if (!array_key_exists("French", $post["message"]) || $post["message"]["French"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
            } else
                $errCode = "11" . $errCode;
        }else
            $errCode .= "11111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $response =  $this->ormsDB->updateSmsMessage($post['message']['English'],$post['specialityId'],
            $post['type'],$post['event'],'English');
        $response += $this->ormsDB->updateSmsMessage($post['message']['French'],$post['specialityId'],
            $post['type'],$post['event'],'French');
        return $response;
    }

    /*
     * Get a list of speciality for appointment message
     * @return  list of speciality updated in database
     * */
    public function getSpecialityMessage(){
        $this->checkReadAccess();

        return $this->postRequest($this->baseUrl."/hospital/getSpecialityGroups");
    }

    /*
     * Sanitize, validate and get a list of type for the given speciality
     * * Validation code :    Error validation code is coded as an int of 1 bits. Bit information
     *                      are coded from right to left:
     *                      1: appointment type missing
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