<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

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
        $this->checkDeleteAccess($alertId);

        $result = $this->opalDB->getAlertDetails($alertId);
        if(count($result) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid Alert ID.");
        $currentAlert = $result[0];

        if(!$currentAlert["ID"] || $currentAlert["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Alert not found.");

        return $this->opalDB->markAlertAsDeleted($alertId);
    }

    /*
     * Return the details of a specific alert.
     * @params  $alertId - int - ID of the alert to retrieve the info
     * @return  array - contains the details of the alert
     * */
    public function getAlertDetails($alertId) {
        $this->checkReadAccess($alertId);
        $result = $this->opalDB->getAlertDetails($alertId);
        if(count($result) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid Alert ID.");
        return $result[0];
    }

    /*
     * Insert a new alert to the table after sanitization and validation check.
     * @parems  $post - array - details of the alert to sanitize and validate before inserting it
     * @return  array - ID of the record inserted
     * */
    public function insertAlert($post) {
        $this->checkWriteAccess($post);
        $newAlert = $this->_validateAlert($post);
        return $this->opalDB->insertAlert($newAlert);
    }

    /*
     * Validate an alert. All fields are mandatory. It checks if the subject, body and trigger fields exists and are
     * not empty. It strips any HTML tags present. Then it checks the contact list, for phone and email address. For
     * phone, only then digits are accepted.
     * @params  $post - details of the alert to validate
     * @return  $validatedAlert - cleaned alert ready to be inserted
     * */
    protected function _validateAlert($post) {
        $validatedAlert = array();
        $post = HelpSetup::arraySanitization($post);

        // Check subject and body
        if(is_array($post["message"])) {
            if($post["message"]["subject"] != "")
                $validatedAlert["subject"] = trim(strip_tags($post["message"]["subject"]));
            else
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing subject.");
            if($post["message"]["body"] != "")
                $validatedAlert["body"] = trim(strip_tags($post["message"]["body"]));
            else
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing body.");
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing message.");

        // Check trigger
        if($post["trigger"] != "")
            $validatedAlert["trigger"] = trim(strip_tags($post["trigger"]));
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing trigger.");

        // Check contact
        if(is_array($post["contact"]) && (is_array($post["contact"]["phone"]) || is_array($post["contact"]["email"]))) {
            $contactArr = array();
            if (is_array($post["contact"]["phone"])) {
                $phoneArr = array();
                $cpt = 0;
                foreach($post["contact"]["phone"] as $phone) {
                    $phone["num"] = trim(strip_tags($phone["num"]));
                    $temp = preg_replace('/[^0-9.]+/', '', $phone["num"]);
                    if(strlen($temp) != 10)
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid phone number.");
                    array_push($phoneArr, $temp);
                    $cpt++;
                }
                $contactArr["phone"] = $phoneArr;
            }
            if (is_array($post["contact"]["email"])) {
                $emailArr = array();
                $cpt = 0;
                foreach($post["contact"]["email"] as $email) {
                    $email["adr"] = trim(strip_tags($email["adr"]));

                    if (!filter_var($email["adr"], FILTER_VALIDATE_EMAIL))
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid email address.");
                    array_push($emailArr, $email["adr"]);
                    $cpt++;
                }
                $contactArr["email"] = $emailArr;
            }
            $validatedAlert["contact"] = json_encode($contactArr);
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing contact info.");
        return $validatedAlert;
    }

    /*
     * Update an alert to the table after sanitization and validation check.
     * @parems  $post - array - details of the alert to sanitize and validate before inserting it
     * @return  array - number of record updated
     * */
    public function updateAlert($post) {
        $this->checkWriteAccess($post);
        $updatedAlert = $this->_validateAlert($post);
        $post["ID"] = trim(strip_tags($post["ID"]));
        if($post["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing alert ID.");
        $updatedAlert["ID"] = $post["ID"];
        return $this->opalDB->updateAlert($updatedAlert);
    }

    /*
     * Update the list of activation flag to the alerts.
     * @parems  $post - array - list of activation flags to validate.
     * @return  array - number of record updated
     * */
    public function updateActivateFlag($post) {
        $this->checkWriteAccess($post);
        $validAlert = $this->_validateAndSanitizeAlertList($post);

        foreach ($validAlert as $item)
            $this->opalDB->updateAlertActivationFlag($item["ID"], $item["active"]);
        return false;
    }

    /*
     * validate and sanitze the alert list before getting updated. If there is a problem return an error 500.
     * @params  $toValidate - array - contains ID and active state
     * @return  $validatedList - array - sanitized array
     * */
    protected function _validateAndSanitizeAlertList($toValidate) {
        $validatedList = array();
        $toValidate = HelpSetup::arraySanitization($toValidate);
        foreach($toValidate["flagList"] as $item) {
            $id = intval(trim(strip_tags($item["ID"])));
            $active = intval(trim(strip_tags($item["active"])));
            if ($active != 0 && $active != 1)
                $active = 0;
            if($id == "")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid alert activation flag.");
            array_push($validatedList, array("ID"=>$id, "active"=>$active));
        }
        return $validatedList;
    }
}
