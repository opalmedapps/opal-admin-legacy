<?php

/**
 * Appointment class
 *
 */

class Appointment extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }

    /**
     * Validate the input parameters for individual patient appointment
     *  1st bit site
     *  2nd bit mrn
     *
     * @param $post array - mrn & featureList
     * @return $errCode
     */
    protected function _validateAppointment(&$post){
        $errCode = "";

        if(is_array($post)){
            //bit 1
            if(!array_key_exists("site", $post) || $post["site"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 2
            if(!array_key_exists("mrn", $post) || $post["mrn"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }

        } else {
            $errCode = "11";
        }
        return $errCode;
    }
    /**
     *  Return an appointment for a patient with or without date range
     *  @param $post: array contains parameter site/mrn
     *  @return array - appointment JSON object
     */
    public function getAppointment($post) {

        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $errCode = $this->_validateAppointment($post);
        $errCode = bindec($errCode);
        if($errCode != 0){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation"=>$errCode));
        }

        if(!array_key_exists("startDate", $post) || $post["startDate"] == "") {

        }

        if(!array_key_exists("endDate", $post) || $post["endDate"] == "") {

        }

        return $this->opalDB->getAppointment($post["site"],$post["mrn"]);
    }
}