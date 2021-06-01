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
     * This function returns the list of available sms Appointment for ORMS.
     * TODO add lazy loading with pagination
     * @params  void
     * @return  array of studies
     * */
    public function getAppointments() {
        $this->checkReadAccess();
        return $this->ormsDB->getAppointmentForSms();
    }

    public function getEvents($type,$speciality) {
        $this->checkReadAccess($type);

        return $this->ormsDB->getEventsForAppointment($type,$speciality);
    }

    public function getMessage($speciality,$type,$event,$language) {
        $this->checkReadAccess($speciality);

        $result = $this->ormsDB->getMessageForAppointment($speciality,$type,$event,$language);
        $messages = array();
        foreach ($result as $item) {
            $messages = array(
                "message" => $item["smsmessage"],
            );
        }
        return $messages;
    }

    public function updateActivationState($information){
        $this->checkWriteAccess($information);

        return $this->ormsDB->updateActivationState($information['state'],$information['appcode'],$information['ressernum']);
    }

    public function updateAppointmentType($information){
        $this->checkWriteAccess($information);
        $information = HelpSetup::arraySanitization($information);
        $errCode = $this->_validateStudy($information);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        if($information['type'] == 'UNDEFINED') return $this->ormsDB->setAppointmentTypeNull($information['appcode'],$information['ressernum']);
        else return $this->ormsDB->updateAppointmentType($information['type'],$information['appcode'],$information['ressernum']);
    }

    public function updateSmsMessage($information,$language){
        $this->checkWriteAccess($information);
        $information = HelpSetup::arraySanitization($information);
        $errCode = $this->_validateStudy($information);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $response =  $this->ormsDB->updateSmsMessage($information['message'][$language],$information['speciality'],
            $information['type'],$information['event'],"English");
        $response += $this->ormsDB->updateSmsMessage($information['message'][$language],$information['speciality'],
            $information['type'],$information['event'],'French');
        return $response;
    }

    public function getSpecialityMessage(){
        $this->checkReadAccess();

        return $this->ormsDB->getSpecialityForMessage();
    }

    public function getTypeMessage($speciality){
        $this->checkReadAccess($speciality);

        return $this->ormsDB->getTypeForMessage($speciality);
    }

}