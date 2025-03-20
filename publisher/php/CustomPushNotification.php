<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once "database.inc";
require_once('PushNotification.php');
require_once('PublisherPatient.php');


class CustomPushNotification{

    /**
     * ==============================================================================
     *            API CALL FUNCTIONS
     * ==============================================================================
     **/

    /**
     *    sendNotificationByPatientSerNum($patientSerNum, $language, $message):
     *    Requires: - PatientSerNum, language, and message. The message is in an array.
     *    Optional: - ignoredUsernames - list of usernames to whom the push notifications should not be sent.
     *    Returns:  Object containing keys of success, failure,
     *             responseDevices, which is an array containing, (success, failure,
     *             registrationId, deviceId) for each device, and Message array containing
     *             (title, description),  NotificationSerNum, and error if any.
     **/
    public static function sendNotificationByPatientSerNum(
        $patientSerNum,
        $message,
        $ignoredUsernames = [],
    ) {
        // Obtain patient device identifiers (patient's caregivers including self-caregiver)
        $patientDevices = PublisherPatient::getCaregiverDeviceIdentifiers(
            $patientSerNum,
            $ignoredUsernames,
        );

        // If no identifiers return there are no identifiers
        if (count($patientDevices) == 0) {
            return array(
                "success" => 0,
                "failure" => 1,
                "responseDevices" => "No patient devices available for that patient",
            );
            exit();
        }

        

        //Send message to patient devices and record in database
        $resultsArray = array();
        foreach ($patientDevices as $device => $detail) {
            $wsmtitle = $message['title_'.$device['language']];
            $wsmdesc = $message['message_text_'.$device['language']];
            
            // Need this format for PushNotification functions
            $messageBody = array(
                "mtitle" => $wsmtitle,
                "mdesc" => $wsmdesc,
                "encode" => "No",  // Set the encoding to NO because the French characters works
            );

            //Determine device type
            if ($device["type"] == 0) {
                $response = PushNotification::iOS($messageBody, $device);
            } else if ($device["type"] == 1) {
                $response = PushNotification::android($messageBody, $device);
            }

            //Log result of push notification on database.
            self::logCustomPushNotification(
                $detail["legacy_id"],
                $patientSerNum,
                $wsmtitle,
                $wsmdesc,
                $response,
            );

            //Build response
            $response["DeviceType"] = $detail["device_type"];
            $response["RegistrationId"] = $device;
            $resultsArray[] = $response;
        }

        // TODO: Log message to logAppointmentReminder(mrn, phoneNumber, messageSent, creationDate, creationTime)

        return array("success" => 1, "failure" => 0, "responseDevices" => $resultsArray, "message" => $message);
    }

    /**
     * ==============================================================================
     *                    HELPER FUNCTIONS
     * ==============================================================================
     **/

    /**
    *    (logCustomPushNotification($deviceSerNum, $patientSerNum, $title, $msg, $response)
    *    Consumes a PatientDeviceIdentifierSerNum, $deviceSerNum, $title, $msg,
    *    and response, $response, where send status is a 1 or 0 for whether is was successfully sent or not.
    *    Inserts a into the PushNotification table or updates SendLog flag.
    *    RegistrationId.
    *    Returns: Returns the send status
    **/
    private static function logCustomPushNotification($deviceSerNum, $patientSerNum, $title, $msg, $response)
    {
        global $pdo;
        $sendStatus  = $response['success'];
        $sendLog     = $response['error'];
        if ($sendStatus == 0) {$sendStatus = 'F';}
        else {
            $sendStatus = 'T';
            $sendLog = "Successfully sent push notification!";
        }
        $sql = " INSERT INTO `customPushNotificationLog`(
                PatientSerNum, PatientDeviceIdentifierSerNum, SendStatus, NotificationTitle, NotificationMSG, DateAdded)
                SELECT :PatientSerNum, :PatientDeviceIdentifierSerNum, :SendStatus, :NotificationTitle, :NotificationMSG, NOW()";

        $s = $pdo->prepare($sql);
        $s->bindValue(':PatientSerNum', $patientSerNum);
        $s->bindValue(':PatientDeviceIdentifierSerNum', $deviceSerNum);
        $s->bindValue(':SendStatus', $sendStatus);
        $s->bindValue(':NotificationTitle', $title);
        $s->bindValue(':NotificationMSG', $msg);
        $s->execute();

        return $sendStatus;
    }
}
