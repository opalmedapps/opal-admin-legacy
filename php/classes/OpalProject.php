<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../../publisher/php/PublisherPatient.php");
include_once("NewOpalApiCall.php");


/**
 * OpalProject class
 *
 */

abstract class OpalProject
{
    protected $opalDB;

    // Notification statuses
    protected string $statusSuccess = 'T';
    protected string $statusWarning = 'W';
    protected string $statusFailure = 'F';

    public function __construct($sessionInfo, $guestStatus) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $sessionInfo,
            $guestStatus
        );
    }

    protected function _insertAudit($module, $method, $arguments, $access, $username = false) {
        $toInsert = array(
            "module"=>$module,
            "method"=>$method,
            "argument"=>json_encode($arguments),
            "access"=>$access,
            "ipAddress"=>HelpSetup::getUserIP(),
        );
        if($username) {
            $toInsert["createdBy"] = $username;
            $this->opalDB->insertAuditForceUser($toInsert);
        }
        else
            $this->opalDB->insertAudit($toInsert);
    }

    /*
    * Get the list of educational materials. Protected function so any module can call it the same way when needed
    * without having to call the module educational materials itself, but cannot be called from outside.
    * @params  void
    * @return  $result - array - list of educational materials
    * */
    protected function _getListEduMaterial() {
        $results = $this->opalDB->getPublishedEducationalMaterial();
        foreach($results as &$row){
            $row["tocs"] = $this->opalDB->getTocsContent($row["serial"]);
            // Create purpose/category mapping
            $purposeId = $row["purpose_ID"];
            $purposeMapping = PURPOSE_CATEGORY_MAPPING[$purposeId];
            $row["purpose"] = $purposeMapping;
        }
        return $results;
    }

    /*
     * Get the details of an educational material. Protected function so any module can call it the same way when needed
     * without having to call the module educational materials itself, but cannot be called from outside.
     * @params  void
     * @return  $result - array - list of educational materials
     * */
    protected function _getEducationalMaterialDetails($eduId) {
        $results = $this->opalDB->getEduMaterialDetails($eduId);
        $results["tocs"] = $this->opalDB->getTocsContent($results["serial"]);
        // Create purpose/category mapping
        $purposeId = $results["purpose_ID"];
        $purposeMapping = PURPOSE_CATEGORY_MAPPING[$purposeId];
        $results["purpose"] = $purposeMapping;
        return $results;
    }

    /*
     * Get the activate source database (Aria, ORMS, local, etc...)
     * @params  void
     * @return  $assignedDB : array - source database ID
     * */
    protected function _getActiveSourceDatabase(){
        $assigned = $this->opalDB->getActiveSourceDatabase();
        $assigned = HelpSetup::arraySanitization($assigned);
        $assignedDB = array();
        foreach($assigned as $item) {
            array_push($assignedDB, $item["SourceDatabaseSerNum"]);
        }
        return $assignedDB;
    }

    /**
     * Validate basic information info of patient and site and make sure they exist
     * @param $post - contain MRN and site to validate
     * @param $patientSite - hospital site info
     * Validation code :    in case of error returns code 422 with array of invalid entries and validation code.
     *                      Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit informations
     *                      are coded from right to left:
     *                      1: MRN invalid or missing
     *                      2: site invalid or missing
     *                      3: combo of MRN-site-patient does not exists
     * @return string - validation code in binary
     */
    protected function _validateBasicPatientInfo(&$post, &$patientSite) {
        $errCode = "";

        // 1st bit - MRN
        if(!array_key_exists("mrn", $post) || $post["mrn"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 2nd bit - Site
        if(!array_key_exists("site", $post) || $post["site"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        // 3rd bit - MRN and site combo must exists
        if(bindec($errCode) != 0) {
            $patientSite = array();
            $errCode = "1" . $errCode;
        } else {
            $patientSite = $this->opalDB->getPatientSite($post["mrn"], $post["site"]);

            if(count($patientSite) != 1) {
                $patientSite = array();
                $errCode = "1" . $errCode;
            }
            else {
                $patientSite = $patientSite[0];
                $errCode = "0" . $errCode;
            }
        }
        return $errCode;
    }


    /**
     * Insert and update resources before updating the pivot table for the current resources needed.
     * @param $appointmentId int - ID of the appointment
     * @param $resources array - list of resources to insert and associate with the appointment
     * @param $sourceDatabaseId - ID of the requested source database
     */
    protected function _insertResources($appointmentId, $resources, $sourceDatabaseId) {

        foreach ($resources as $resource) {
            $data = array(
                "SourceDatabaseSerNum"=>$sourceDatabaseId,
                "ResourceCode"=>$resource["code"],
                "ResourceName"=>$resource["name"],
                "ResourceType"=>$resource["type"],
            );

            $result = $this->opalDB->countResource($data);
            if (intval($result["total"]) <= 0)
                $this->opalDB->insertResource($data);
            else
                $this->opalDB->updateResource($data);
        }

        $resourceAppointmentList = $this->opalDB->getResourceIds($resources, $sourceDatabaseId, $appointmentId);

        $resourceIdList = array();
        foreach ($resourceAppointmentList as $id)
            array_push($resourceIdList, intval($id["ResourceSerNum"]));

        $this->opalDB->deleteResourcesForAppointment($appointmentId, $resourceIdList);
        $this->opalDB->insertResourcesForAppointment($resourceAppointmentList);
    }

    protected function _notifyChange($data, $action, $dynamicKeys, $refTableId){
        // NOTE: The same functionality already exists in Perl (PushNotification.pm). Any change to the logic here needs to be applied there as well.
        $notificationControl = $this->opalDB->getNotificationControlDetails($data["PatientSerNum"], $action);
        $controlser          = $notificationControl[0]["NotificationControlSerNum"];
        $messageTitles        = array(
            "en"=>$notificationControl[0]["Name_EN"],
            "fr"=>$notificationControl[0]["Name_FR"],
        );
        $messageTemplates     = array(
            "en"=>$notificationControl[0]["Message_EN"],
            "fr"=>$notificationControl[0]["Message_FR"],
        );

        $this->_insertNotification($data, $controlser, $refTableId);

        try {
            $patient = $this->opalDB->getPatientSerNum($data['PatientSerNum'])[0];
        } catch (Exception $e) {
            $sendlog = "An error occurred while querying the patient's information: $e";
            $pushNotificationDetail = $this->_buildNotification($this->statusFailure, $sendlog, $refTableId, $controlser, $data['PatientSerNum'], null);
            $this->opalDB->insertPushNotification($pushNotificationDetail);
            return;
        }

        $caregiverDevices = PublisherPatient::getCaregiverDeviceIdentifiers($data["PatientSerNum"]);

        if (count($caregiverDevices) == 0){
            $sendlog = "Patient has no device identifier! No push notification sent.";
            $pushNotificationDetail = $this->_buildNotification($this->statusWarning, $sendlog, $refTableId, $controlser, $data["PatientSerNum"], null);
            $this->opalDB->insertPushNotification($pushNotificationDetail);
        } else {

            // NOTE! Push notifications are sent based on the target caregivers's language.
            foreach($caregiverDevices as $ptdId => $detail) {
                $ptdidser        = $ptdId;
                $registrationId  = $detail['registration_id'];
                $deviceType      = $detail["device_type"];
                $language        = $detail['language'];
                $messageTemplate = $messageTemplates[$language];
                $messageTitle    = $messageTitles[$language];

                // Special case for replacing the $patientName wildcard
                if (str_contains($messageTemplate, '$patientName')) {
                    $firstName = $patient['FirstName'];
                    // Add $patientName as a wildcard for replacement
                    $dynamicKeys['$patientName'] = $firstName;
                }

                // Special case for replacing the $institution wildcard
                if (str_contains($messageTemplate, '$institution')) {
                    if (!$detail['institution_acronym']){
                        $sendlog = "An error occurred while getting the patient's institution";
                        $pushNotificationDetail = $this->_buildNotification($this->statusFailure, $sendlog, $refTableId, $controlser, $data['PatientSerNum'], null);
                        return;
                    }
                    $dynamicKeys['$institution'] = $detail['institution_acronym'];
                }
                // prepare array for replacements
                $patterns           = array();
                $replacements       = array();
                $indice             = 0;
                foreach($dynamicKeys as $key=>$val) {
                    $patterns[$indice] = $key;
                    $replacements[$indice] = $val;
                    $indice +=1;
                }

                ksort($patterns);
                ksort($replacements);
                $message =  str_replace($patterns, $replacements, $messageTemplate);

                if (!in_array($deviceType, SUPPORTED_PHONE_DEVICES)) {
                    $sendstatus = $this->statusFailure;
                    $sendlog    = "Failed to send push notification! Message: Unsupported device type";
                } else {
                    if ($deviceType == APPLE_PHONE_DEVICE)
                        $api = new AppleApiCall($registrationId, $messageTitle, $message);
                    else
                        $api = new AndroidApiCall($registrationId, $messageTitle, $message);

                    $api->execute();
                    if ($api->getError()) {
                        $sendstatus = $this->statusFailure;
                        $sendlog    = "Failed to send push notification! Message: " . $api->getError();
                    } else {
                        $sendstatus = $this->statusSuccess;
                        $sendlog = "Push notification successfully sent! Message: $message";
                    }
                }

                $pushNotificationDetail = $this->_buildNotification($sendstatus, $sendlog, $refTableId, $controlser, $data["PatientSerNum"], $ptdidser);
                $this->opalDB->insertPushNotification($pushNotificationDetail);
            }
        }
    }

    protected function _buildNotification($sendstatus, $sendlog, $refTableId, $controlser, $patientSerNum, $ptdidser) {
        return array(
            "SendStatus" => $sendstatus,
            "SendLog" => $sendlog,
            "DateAdded" => date("Y-m-d H:i:s"),
            "RefTableRowSerNum" => $refTableId,
            "NotificationControlSerNum" => $controlser,
            "PatientSerNum" => $patientSerNum,
            "PatientDeviceIdentifierSerNum" => $ptdidser
        );
    }

    protected function _insertNotification($data, $controlser, $refTableId){
        $aliasExpressionDetail = $this->opalDB->getAliasExpressionDetail($data["AliasExpressionSerNum"]);
        $newNotification = array (
            "PatientSerNum"=>$data["PatientSerNum"],
            "NotificationControlSerNum" => $controlser,
            "RefTableRowSerNum" => $refTableId,
            "DateAdded" => date("Y-m-d H:i:s"),
            "ReadStatus" => 0
        );

        if (is_array($aliasExpressionDetail) && array_key_exists("AliasName_FR", $aliasExpressionDetail)) {
            $newNotification["RefTableRowTitle_FR"]  = $aliasExpressionDetail["AliasName_FR"];
        }

        if (is_array($aliasExpressionDetail) && array_key_exists("AliasName_EN", $aliasExpressionDetail)) {
            $newNotification["RefTableRowTitle_EN"]  = $aliasExpressionDetail["AliasName_EN"];
        }

        $this->opalDB->insertNotification($newNotification);
    }
}
