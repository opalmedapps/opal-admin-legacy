<?php

/*
 * Sms class objects and method
 * */

class Sms extends Module {

    protected $ormsDB;

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
     * Sanitize, validate and get a list of sms appointments base on input.
     * @return  List of sms appointments
     * */
    public function getAppointments() {
        $this->checkReadAccess();
        return $this->ormsDB->getAppointmentForSms();
    }

    /*
     * Sanitize, validate and get a list of events base on input.
     * Validation code :    Error validation code is coded as an int of 2 bits. Bit informations
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
     * Sanitize, validate and get a list of events for the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 4 bits. Bit informations
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
            if (!array_key_exists("language", $post) || $post["language"] != 1 || $post["language"] != 2)
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
     * Sanitize, validate and update a list of events for the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 3 bits. Bit informations
     *                      are coded from right to left:
     *                      1: appointment activation state missing
     *                      2: appointment code id missing
     *                      3: resource series number missing
     * @params  $post (array) data received from the front end.
     * @return  Number records updated in database
     * */
    public function updateActivationState($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        $response = 0;
        if (is_array($post)) {
            if(array_key_exists("updateList", $post) || is_array($post["updateList"])) {
                foreach ($post["updateList"] as $information) {
                    $errCode = "";
                    if (is_array($information)){
                        if (!array_key_exists("state", $information) || $information["state"] == "")
                            $errCode = "1" . $errCode;
                        else
                            $errCode = "0" . $errCode;
                        if (!array_key_exists("appcode", $information) || $information["appcode"] == "")
                            $errCode = "1" . $errCode;
                        else
                            $errCode = "0" . $errCode;
                        if (!array_key_exists("ressernum", $information) || $information["ressernum"] == "")
                            $errCode = "1" . $errCode;
                        else
                            $errCode = "0" . $errCode;
                    }
                    else
                        $errCode = "111";
                    $errCode = bindec($errCode);
                    if ($errCode != 0)
                        HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));
                    $response += $this->ormsDB->updateActivationState($information['state'], $information['appcode'], $information['ressernum']);
                }
            } else
                $errCode = bindec("111");
        }else
            $errCode .= bindec("111");

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $response;
    }

    /*
     * Sanitize, validate and get a list of events for the given type, speciality, event and language
     * Validation code :    Error validation code is coded as an int of 2 bits. Bit informations
     *                      are coded from right to left:
     *                      1: appointment activation state missing
     *                      2: appointment code id missing
     *                      3: resource series number missing
     * @params  $post (array) data received from the front end.
     * @return  Number records updated in database
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
            if (!array_key_exists("appcode", $post) || $post["appcode"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if (!array_key_exists("ressernum", $post) || $post["ressernum"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        }else
            $errCode .= "111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        if($post["information"]['type'] == 0)
            return $this->ormsDB->setAppointmentTypeNull($post['appcode'],$post['ressernum']);
        else
            return $this->ormsDB->updateAppointmentType($post['type'],$post['appcode'],$post['ressernum']);
    }

    public function updateSmsMessage($post){
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if(array_key_exists("UpdateInformation", $post) || is_array($post["UpdateInformation"])) {
                if (!array_key_exists("type", $post["UpdateInformation"]) || $post["UpdateInformation"]["type"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if (!array_key_exists("speciality", $post["UpdateInformation"]) || $post["UpdateInformation"]["speciality"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if (!array_key_exists("event", $post["UpdateInformation"]) || $post["UpdateInformation"]["event"] == "")
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;
                if(array_key_exists("message", $post["UpdateInformation"]) || is_array($post["UpdateInformation"]["message"])) {
                    if (!array_key_exists("English", $post["UpdateInformation"]["message"]) || $post["UpdateInformation"]["message"]["English"] == "")
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                    if (!array_key_exists("French", $post["UpdateInformation"]["message"]) || $post["UpdateInformation"]["message"]["French"] == "")
                        $errCode = "1" . $errCode;
                    else
                        $errCode = "0" . $errCode;
                }
            } else
                $errCode = "11111". $errCode;
        }else
            $errCode .= "11111";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $response =  $this->ormsDB->updateSmsMessage($post["UpdateInformation"]['message']['English'],$post["UpdateInformation"]['speciality'],
            $post["UpdateInformation"]['type'],$post["UpdateInformation"]['event'],'English');
        $response += $this->ormsDB->updateSmsMessage($post["UpdateInformation"]['message']['French'],$post["UpdateInformation"]['speciality'],
            $post["UpdateInformation"]['type'],$post["UpdateInformation"]['event'],'French');
        return $response;
    }

    public function getSpecialityMessage(){
        $this->checkReadAccess();

        return $this->ormsDB->getSpecialityForMessage();
    }

    public function getTypeMessage($post){
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";
        if (is_array($post)) {
            if (!array_key_exists("speciality", $post) || $post["speciality"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        } else
            $errCode .= "1";

        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $this->ormsDB->getTypeForMessage($post["speciality"]);
    }

    public function getAllTypeMessage(){
        $this->checkReadAccess();

        return $this->ormsDB->getAllTypeForMessage();
    }

}