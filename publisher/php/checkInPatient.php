<?php
    include_once "database.inc";

	public function updateAppointmentCheckIn($patientSerNum, $appointmentSerNum, $appointmentCheckIn, $response)
       {
           global $pdo;
		   
           $sendStatus  = $response['success'];
           $sendLog     = $response['error'];

           $sql = "
                    SELECT ".$deviceSerNum.", $patientSerNum, ntc.NotificationControlSerNum, $appointmentSerNum,
                    NOW(),'".$sendStatus."','".$sendLog."' FROM NotificationControl ntc WHERE ntc.NotificationType = 'CheckInNotification'";


		   
           if ($sendStatus == 0) {$sendStatus = 'F';}
           else {
               $sendStatus = 'T';
               $sendLog = "Successfully sent push notification!";
           } 
           $sql = " INSERT INTO `PushNotification`(
                    `PatientDeviceIdentifierSerNum`, `PatientSerNum`, `NotificationControlSerNum`, `RefTableRowSerNum`, 
					`DateAdded`, `SendStatus`, `SendLog`) 
                    SELECT ".$deviceSerNum.", $patientSerNum, ntc.NotificationControlSerNum, $appointmentSerNum,
                    NOW(),'".$sendStatus."','".$sendLog."' FROM NotificationControl ntc WHERE ntc.NotificationType = 'CheckInNotification'";
           $result = $pdo->query($sql);

           return $sendStatus;
       }











?>