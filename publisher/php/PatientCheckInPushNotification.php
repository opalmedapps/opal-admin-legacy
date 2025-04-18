<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

//
// INCLUDES
//===================================
include_once "database.inc";
require_once('PushNotification.php');
require_once('PublisherPatient.php');


class PatientCheckInPushNotification{


    /**
     * API METHODS
     * ===============================================
     * @name sendPatientCheckInNotification
     * @desc Using the PatientSerNum, it will send a notification to the patient cell phone stating that they are now checked in.
     * @param $patientSerNum
     * @param $success
     * @return  array containing keys of success, failure,
     *          responseDevices, which is an array containing, (success, failure,
     *          registrationId, deviceId) for each device, and Message array containing
     *          (title,description),  NotificationSerNum, and error if any.
     */

    public static function sendPatientCheckInNotification($patientSerNum, $success){
        // Determines whether or not all appointments were checked in successfully
        $allSuccessful = true;

        //
        // POPULATE NOTIFICATIONS TABLE
        //=========================================================
        // Insert checkin notifications into opaldb
        foreach($success as $apt) {
            self::insertCheckInNotification(array($apt), $patientSerNum);
        }

        // If there are any appointments that were not successfully checked into... insert error notification
        $failedCheckins = self::getFailedCheckins($patientSerNum);

        if(count($failedCheckins) > 0) {
            $allSuccessful = false;
            self::insertCheckInNotification($failedCheckins, $patientSerNum, 'CheckInError');
        }

        //
        // SEND MESSAGE TO PATIENT DEVICES AND RECORD IN DATABASE
        //================================================================

        // Obtain patient device identifiers (patient's caregivers including self-caregiver)
        $caregiverDevices = PublisherPatient::getCaregiverDeviceIdentifiers($patientSerNum);

        // If no device identifiers return there are no device identifiers
        if(count($caregiverDevices) == 0) {
            return array("success"=>1, "failure"=>0,"responseDevices"=>"No patient devices available for that patient");
        }

        $resultsArray = array();

        foreach($caregiverDevices as $device => $detail) {

            $response = null;
            $language = strtoupper($detail['language']);

            // Prepare the success message title and body
            $message = (!$allSuccessful)? self::buildMessageForPushNotification('CheckInError', $language) : self::buildMessageForPushNotification('CheckInNotification', $language);
            $dynamicKeys = [];

            // Special case for replacing the $institution wildcard
            if (str_contains($message["mdesc"], '$institution')) {
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
            $message["mdesc"] =  str_replace($patterns, $replacements, $message["mdesc"]);

            // Determine device type (0 = iOS & 1 = Android)
            if($detail["device_type"] == 0) {
                $response = PushNotification::iOS($message, $detail["registration_id"]);
            } else if($detail["device_type"] == 1) {
                $response = PushNotification::android($message, $detail["registration_id"]);
            }
            // Log result of push notification on database.
            // NOTE: Inserting -1 for appointmentSerNum
            self::pushNotificationDatabaseUpdate($device, $patientSerNum, -1, $response);

            // Build response
            $response["DeviceType"] = $detail["device_type"];
            $response["RegistrationId"] = $detail["registration_id"];
            $resultsArray[] = $response;
        }
        return array("success"=>1,"failure"=>0,"responseDevices"=>$resultsArray,"message"=>$message);
    }


    /**
     * ==============================================================================
     *                    HELPER FUNCTIONS
     * ==============================================================================
     **/

    /**
     * @name insertCheckInNotification
     * @desc Inserts CheckIn notification into notifications table in OpalDB, also responsible for inserting checkin error notification
     * @param $apts
     * @param $patientSerNum
     * @param $type
     * @return array|bool
     */
    private static function insertCheckInNotification($apts, $patientSerNum, $type = 'CheckInNotification'){
        global $pdo;

        //Insert checkin notification into notifications table
        foreach ($apts as $apt){
            try{
                $sql = 'INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
                        SELECT  ' . $patientSerNum . ',ntc.NotificationControlSerNum, '. $apt . ', NOW(), 0,
                          getRefTableRowTitle('. $apt . ', "APPOINTMENT", "EN") EN, getRefTableRowTitle('. $apt . ', "APPOINTMENT", "FR") FR
                        FROM NotificationControl ntc
                        WHERE ntc.NotificationType = \''. $type . '\'';

                $s = $pdo->prepare($sql);
                $s->execute();

            }catch(PDOException $e) {
                return array("success"=>0,"failure"=>1,"error"=>$e);
            }
        }

        return true;
    }

    /**
     * @name getFailedCheckins
     * @desc gets list of appointments that were not checked into successfully. This is determined by an appointment existing in our DB and not in the list of appointments in $success
     * @param $patientSerNum
     * @return array|bool
     */
    private static function getFailedCheckins($patientSerNum){
        global $pdo;

        // Retrieve the number of success and/or failed check in of the appointments
        $sql = "Select Appointment.AppointmentSerNum
                From Patient, Appointment
                Where Patient.PatientSerNum = :patientSerNum
                and Patient.PatientSerNum = Appointment.PatientSerNum
                and Appointment.Checkin = 0
                and DATE_FORMAT(Appointment.ScheduledStartTime, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d');";

        // Fetch the results
        try{
            $r = $pdo->prepare($sql);
            $r->bindValue(':patientSerNum', $patientSerNum);
            $r->execute();
            $results = $r->fetchAll();

            $appts = array();
            foreach ($results as $apt) {
                $appts[] = $apt['AppointmentSerNum'];
            }

            return $appts;

        }catch(PDOException $e) {
            return array("success"=>0,"failure"=>1,"error"=>$e);
        }
    }

    /**
     *    (buildMessageForCheckInNotification($datetimestamp, $title, $description)
     *    Build the messages with title and a description
     *    Description: Builds push notification message for checking in and replace the string
     *      $getDateTime with the time stamp
     *    Returns: Returns array with the push notification message to be sent
     **/
    private static function buildMessageForPushNotification($type, $language){
        //
        // GET THE TITLE AND THE BODY OF THE  PUSH NOTIFICATION
        //======================================================
        $messageLabels = self::getNotificationMessage($type, $language);

        $datetimestamp = "";
        // Get the date and time stamp of when the person checked in
        if ($language == "FR") {
            $datetimestamp = date("H:i"); // "14:20"
        }
        else if ($language == "EN") {
            $datetimestamp = date("g:i A"); // "3:14 AM"
        }

        return array(
            "mtitle"=> $messageLabels["Name_".$language ],
            "mdesc"=>str_replace('$getDateTime', $datetimestamp,  $messageLabels["Description_".$language]),
            "encode"=> "No" // Set the encoding to NO because the French characters works
        );
    }

    /**
     * @name getCheckinNotificationMeta
     * @desc gets the name and description of the checkin notification based on the user language
     * @param $language
     * @return array
     */
    private static function getNotificationMessage($type, $language){
        global $pdo;

        try{
            $sql = 'SELECT Name_'.$language.', Description_'.$language.' FROM NotificationControl WHERE NotificationType = \'' .$type . '\'';
            $result = $pdo->query($sql);
            return $result->fetch();
        }catch(PDOException $e) {
            return array("success"=>0,"failure"=>1,"error"=>$e);
        }
    }

    /**
     *    (pushNotificationDatabaseUpdate($deviceSerNum, $patientSerNum, $appointmentSerNum, $sendStatus)
     *    Consumes a PatientDeviceIdentifierSerNum, $deviceSerNum,
     *    and response, $response, where send status is a 1 or 0 for whether is was successfully sent or not.
     *    Inserts a into the PushNotification table or updates SendLog flag.
     *    RegistrationId.
     *    Returns: Returns the send status
     **/
    private static function pushNotificationDatabaseUpdate($deviceSerNum, $patientSerNum, $appointmentSerNum, $response)
    {
        global $pdo;
        $sendStatus  = $response['success'];
        $sendLog     = $response['failure'];
        if ($sendStatus == 0) $sendStatus = 'F';
        else {
            $sendStatus = 'T';
            $sendLog = "Successfully sent push notification!";
        }
        $sql = " INSERT INTO `PushNotification`(`PatientDeviceIdentifierSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `SendStatus`, `SendLog`)
                        SELECT ".$deviceSerNum.", $patientSerNum, ntc.NotificationControlSerNum, $appointmentSerNum, NOW(),'".$sendStatus."','".$sendLog."'
                        FROM NotificationControl ntc
                        WHERE ntc.NotificationType in ('CheckInNotification', 'CheckInError')";
        $pdo->query($sql);
        return $sendStatus;
    }
}

?>
