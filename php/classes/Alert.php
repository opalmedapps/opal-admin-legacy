<?php

/*
 * Study class objects and method
 * */

class Alert extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_ALERT, $guestStatus);
    }

    /*
     * This function returns the list of available alerts for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of studies
     * */
    public function getAlerts() {
        $this->checkReadAccess();
        return $this->opalDB->getAlertsList();
    }

    /**
     * Mark an alert as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the alert table! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked and the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its
     * records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params  $alertId (ID of the alert)
     * @return  (int) number of record marked or error 500 if an error occurred.
     */
    public function deleteAlert($alertId) {
        $this->checkDeleteAccess();
        return 0;
    }

    /*
     * Return the details of a specific alert.
     * @params  $alertId - int - ID of the alert to retrieve the info
     * @return  array - contains the details of the alert
     * */
    public function getAlertDetails($alertId) {
        $this->checkReadAccess();
        return array();
    }

    /*
     * Insert a new alert to the table after sanitization and validation check.
     * @parems  $post - array - details of the alert to sanitize and validate before inserting it<
     * @return  array - number of record inserted
     * */
    public function insertAlert($post) {
        $this->checkWriteAccess();
        return array();
    }

    /*
     * Update an alert to the table after sanitization and validation check.
     * @parems  $post - array - details of the alert to sanitize and validate before inserting it<
     * @return  array - number of record updated
     * */
    public function updateAlert($post) {
        $this->checkWriteAccess();
        return array();
    }
}