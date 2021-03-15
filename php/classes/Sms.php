<?php

/*
 * Sms class objects and method
 * */

class Sms extends Module {

    protected $ormsDB;

    public function __construct($moduleId, $guestStatus = false) {
        parent::__construct(MODULE_SMS, $guestStatus);
        if($_SESSION["userAccess"][$moduleId]) {
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
        $this->checkReadAccess();

        return $this->ormsDB->getEventsForAppointment($type,$speciality);
    }

    public function getMessage($type,$event,$language) {
        $this->checkReadAccess();

        return $this->ormsDB->getMessageForAppointment($type,$event,$language);
    }
}