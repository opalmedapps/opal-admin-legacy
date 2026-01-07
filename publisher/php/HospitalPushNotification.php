<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once "database.inc";
    require_once('PushNotification.php');
    require_once('PublisherPatient.php');


   class HospitalPushNotification{

       /**
       * ==============================================================================
       *            API CALL FUNCTIONS
       * ==============================================================================
       **/

        /**
        *    (sendSingleNotification($deviceType, $registrationId, $title, $description)
        *    Consumes a $deviceType, a $registrationId, a  $title and a $message
        *    Description: Sends notification with $title and $message to that device
        *    Requires: $deviceType = 0 or 1, 0 for iOS, 1 for Android.
	    *    Returns: Array containing key of success, failure, error if any.
        **/
       public static function sendNotification($deviceType, $registrationId, $title, $description)
       {
            // Cron refactor 2021-02 check if we are within accepted timeframe for push notifications
            $now = date('Y-m-d H:i:s');
            $eight_AM = date('Y-m-d 08:00:00');
            $eight_PM = date('Y-m-d 20:00:00');
            if($now > $eight_AM && $now < $eight_PM){ // Within acceptable time window, send notification
                $message = array(
                    "mtitle"=>$title,
                    "mdesc"=>$description
                );

                if ($deviceType == 0)
                {
                    $response = PushNotification::iOS($message, $registrationId);
                } else if ($deviceType == 1)
                {
                    $response = PushNotification::android($message, $registrationId);
                }

                return $response;
            } else { // Not within window, return empty response
				return array("success"=>0,"failure"=>1,"error"=>"Unable to send PushNotification: Quiet hours.");
            }
       }

      /**
        *    sendRoomNotification($patientId, $room, $appointmentSerNum, $mrn, $site):
        *    Consumes a PatientId or (MRN and Site), a room location, and an SourceSystemID, it
        *    stores notification in database, updates appointment with room location, sends
        *    the notification to the pertinent devices that map to that particular patientId,
        *    and finally records the send status for the push notification.
        *    (sendRoomNotification String, String, String) -> Array
        *    Requires: - PatientId or (MRN and Site) and AppointmentSerNum are real values in the Database.
        *              - NotificationControlSerNum = 10, Corresponds to the AssignedRoom
        *                notification.
        *    Returns:  Object containing keys of success, failure,
        *             responseDevices, which is an array containing, (success, failure,
        *             registrationId, deviceId) for each device, and Message array containing
        *             (title,description),  NotificationSerNum, and error if any.
        **/
        public static function sendCallPatientNotification($patientId, $room, $SourceSystemID, $mrn = null, $site = null)
        {
            global $pdo;

            // determine patientId or MRN
            $patientId = self::getPatientIDorMRN($patientId, $mrn);

            // $wsSite is the site of the hospital code (should be three digit)
            // If $wsSite is empty, then default it to RVH because it could be from a legacy call
            $wsSite = empty($site) ? "RVH" : $site;

            //Obtain Patient and appointment information from Database i.e. PatientSerNum, AppointmentSerNum and Language
            $sql = "SELECT P.PatientSerNum, A.AppointmentSerNum
                    FROM Appointment A, Patient P, Patient_Hospital_Identifier PHI
                    WHERE P.PatientSerNum = PHI.PatientSerNum
                        AND PHI.MRN = :patientId
                        and PHI.Hospital_Identifier_Type_Code = :sitecode
                        AND P.PatientSerNum = A.PatientSerNum
                        AND A.SourceSystemID = :sourceSer
                    ";
            try{
                $s = $pdo->prepare($sql);
                $s->bindValue(':patientId', $patientId);
                $s->bindValue(':sitecode', $wsSite);
                $s->bindValue(':sourceSer', $SourceSystemID);
                $s->execute();
                $result = $s->fetchAll();
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }
            if(count($result)==0)
            {
                return array("success"=>0,"failure"=>1,"error"=>"No matching PatientSerNum or AppointmentSerNum in Database");
                exit();
            }

            //Sets parameters for later usage
            $patientSerNum = $result[0]["PatientSerNum"];
            $appointmentSerNum = $result[0]["AppointmentSerNum"];


            //Update appointment room location in database
            try{
                $sql = "UPDATE Appointment SET RoomLocation_EN = '".$room['room_EN']."', RoomLocation_FR = '".$room['room_FR']."' WHERE Appointment.SourceSystemID = ".$SourceSystemID." AND Appointment.PatientSerNum = ".$patientSerNum;
                $pdo->query($sql);
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }

          //Insert into notifications table
            try{
                $sql = 'INSERT INTO `Notification` (`PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, `DateAdded`, `ReadStatus`, `RefTableRowTitle_EN`, `RefTableRowTitle_FR`)
                    SELECT '.$result[0]["PatientSerNum"].',ntc.NotificationControlSerNum,'.$result[0]["AppointmentSerNum"].', NOW(), 0,
                      getRefTableRowTitle('. $result[0]["AppointmentSerNum"] . ', "APPOINTMENT", "EN") EN, getRefTableRowTitle('. $result[0]["AppointmentSerNum"] . ', "APPOINTMENT", "FR") FR
                    FROM NotificationControl ntc WHERE ntc.NotificationType = "RoomAssignment"';
                $pdo->query($sql);
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }


            //Obtain NotificationSerNum for the last inserted Id.
            //$notificationSerNum = $pdo->lastInsertId();

            //Obtain message for room assignment
            try{
                $sql = 'SELECT Name_EN, Description_EN, Name_FR, Description_FR
                        FROM NotificationControl WHERE NotificationType = "RoomAssignment"';
                $result = $pdo->query($sql);
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }

            //Build message, replace the $roomLocation with the actual room location argument $room
            $messageLabels = $result->fetch();

            // Obtain patient device identifiers (patient's caregivers including self-caregiver)
            $caregiverDevices = PublisherPatient::getCaregiverDeviceIdentifiers($patientSerNum);

            //If no identifiers return there are no identifiers
            if(count($caregiverDevices) == 0)
            {
                return array("success"=>1, "failure"=>0,"responseDevices"=>"No patient devices available for that patient");
                exit();
            }

            //Send message to patient devices and record in database
            $resultsArray = array();
            foreach($caregiverDevices as $device => $detail)
            {
                $language = strtoupper($detail['language']);
                $message = self::buildMessageForRoomNotification($room["room_".$language], $messageLabels["Name_".$language ], $messageLabels["Description_".$language]);
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
                //Determine device type
                if($detail["device_type"]==0)
                {
                    $response = PushNotification::iOS($message, $detail["registration_id"]);
                }else if($detail["device_type"]==1)
                {
                    $response = PushNotification::android($message, $detail["registration_id"]);
                }

                //Log result of push notification on database.
                self::pushNotificationDatabaseUpdate($device, $patientSerNum, $appointmentSerNum, $response);
                //Build response
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
           $sendLog     = json_encode($response['error']);
           if ($sendStatus == 0) {$sendStatus = 'F';}
           else {
               $sendStatus = 'T';
               $sendLog = "Successfully sent push notification!";
           }
           $sql = " INSERT INTO `PushNotification`(
                    `PatientDeviceIdentifierSerNum`, `PatientSerNum`, `NotificationControlSerNum`,
                    `RefTableRowSerNum`, `DateAdded`, `SendStatus`, `SendLog`)
                    SELECT ".$deviceSerNum.", $patientSerNum, ntc.NotificationControlSerNum, $appointmentSerNum,
                    NOW(),'".$sendStatus."','".$sendLog."' FROM NotificationControl ntc WHERE ntc.NotificationType = 'RoomAssignment'";
           $result = $pdo->query($sql);
           return $sendStatus;
       }

       /**
        *    (buildMessageForRoomNotification($room, $title, $description)
        *    Consumes a room, a title and a description of message
        *    Description: Builds push notification message for Room Notification
        *    Encode: is set to NO because we do not want to re-encode again
        *    Returns: Returns array with the push notification message to be sent
        **/
        private static function buildMessageForRoomNotification($room, $title, $description)
        {
             $message = array(
                "mtitle"=>$title,
                "mdesc"=>str_replace('$roomNumber',$room, $description),
                "encode"=>'No'
             );
             return $message;
        }

        /**
         * getPatientIDorMRN($patientId, $mrn)
         * Description: This function is to determine if it is to use PatientId or MRN and return
         * the value.
         *
         * Returns: returns patientId
         **/
        public static function getPatientIDorMRN($patientId, $mrn)
        {
            // $patientId is for legacy systems/calls
            $patientId = empty($patientId) ? "---NA---" : $patientId;
            // $wsMRN is the hospital medical ID
            $wsMRN = empty($mrn) ? "---NA---" : $mrn;

            // Only one MRN is accepted if somehow both $patientId and $wsMRN is provided then we want to replace
            // the $patientId with the $wsMRN. If no $patientId provided, but $wsMRN is then we copy the $wsMRN to $patientId.
            // The $patientId is the original parameter in this entire code, so it is easier to just re-use it.
            if ( (($patientId <> "---NA---") && ($wsMRN <> "---NA---")) ||
                (($patientId == "---NA---") && ($wsMRN <> "---NA---")) )
            {
                $patientId = $wsMRN;
            };

            return $patientId;
        }

        /**
         * sanitizeInput($inString)
         * Description: This function is a basic string input sanitizer
         *
         * Returns: returns outString
         **/
        public static function sanitizeInput($inString)
        {
            // sanitize the input string
            if ($inString != '') {
                $outString = filter_var($inString, FILTER_SANITIZE_ADD_SLASHES);
             } else {
                $outString = "";
             }

            return $outString;
        }

   }

?>
