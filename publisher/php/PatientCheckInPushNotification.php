<?php
    include_once "database.inc";
    require_once('PushNotifications.php');

   class PatientCheckInPushNotification{

       /**
       * ==============================================================================
       *            API CALL FUNCTIONS
       * ==============================================================================
       **/

	   
      /**
        *    sendPatientCheckInNotification($patientId):
		*    Using the PatientID, it will send a notification to the patient cell phone (if avaialble)
		*    a confirmation notification that they are now checked in.
		*    
        *    (sendPatientCheckInNotification String) -> Array
        *    Requires: Database
		* 				- Patient table and the field PatientID.
        *              - NotificationControl table and field NotificationType with the value of "CheckInNotification"

        *    Returns:  Object containing keys of success, failure,    
        *             responseDevices, which is an array containing, (success, failure, 
        *             registrationId, deviceId) for each device, and Message array containing 
        *             (title,description),  NotificationSerNum, and error if any. 
        **/
       public function sendPatientCheckInNotification($patientId)
       {
           global $pdo;

			// Step 1 - Get the patient selected Language
           $sql = "SELECT Patient.Language, Patient.PatientSerNum
							FROM Patient 
							WHERE Patient.PatientId = :patientId;";

		   //  Repalce the parameter :patientId with a value
           try{
                $s = $pdo->prepare($sql);
                $s->bindValue(':patientId', $patientId);
                $s->execute();
                $result = $s->fetchAll();
           }catch(PDOException $e)
           {
               return array("success"=>0,"failure"=>1,"error"=>$e);
               exit();
           }
		   
		   // Exit function if no patient serial number exist in database
           if(count($result)==0)
           {
               return array("success"=>0,"failure"=>1,"error"=>"No matching PatientSerNum in Database");
               exit();
           }
           
           // Sets parameters for later usage
            $language = $result[0]["Language"]; // Used to control what default language the patient have in their profile
            $patientSerNum = $result[0]["PatientSerNum"];
           
		   // if no language is available, then default to English
		   if (strlen(trim($language)) == 0)
			{
			$language = 'EN';
			}
		   

		   // Step 2 - Get the Title and the Body of the notification
            try{
                $sql = 'SELECT Name_'.$language.', Description_'.$language.' FROM NotificationControl WHERE NotificationType = "CheckInNotification"';
                $result = $pdo->query($sql);
            }catch(PDOException $e)
            {
                return array("success"=>0,"failure"=>1,"error"=>$e);
                exit();
            }

            // Set message notificaiton
            $messageLabels = $result->fetch();
           
		   // Prepare the message title and body
		   $message = self::buildMessageForCheckInNotification($messageLabels["Name_".$language ], $messageLabels["Description_".$language] );
		   
           // Obtain patient device identifiers
            $patientDevices = self::getDevicesForPatient($patientId);

            // If no device identifiers return there are no device identifiers
            if(count($patientDevices)==0)
            {
                return array("success"=>1, "failure"=>0,"responseDevices"=>"No patient devices available for that patient");
                exit();
            }
            
            // Step 3 - Send message to patient devices and record in database
            $resultsArray = array();
            foreach($patientDevices as $device)
            {   
                // Determine device type (0 = iOS & 1 = Android)
                if($device["DeviceType"]==0)
                {
                    $response = PushNotifications::iOS($message, $device["RegistrationId"]);
                }else if($device["DeviceType"]==1)
                {
                    $response = PushNotifications::android($message, $device["RegistrationId"]);
                }

                // Log result of push notification on database.
				// NOTE: Inserting -1 for appointmentSerNum
                self::pushNotificationDatabaseUpdate($device["PatientDeviceIdentifierSerNum"], $patientSerNum, -1, $response);
                // Build response
                $response["DeviceType"] = $device["DeviceType"];
                $response["RegistrationId"] = $device["RegistrationId"];
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
       private function pushNotificationDatabaseUpdate($deviceSerNum, $patientSerNum, $appointmentSerNum, $response)
       {
           global $pdo;
           $sendStatus  = $response['success'];
           $sendLog     = $response['error'];
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

       /**
        *    (getDevicesForPatient($patientId)
        *    Consumes a PatientId, $patientId
        *    Returns: Returns array with devices that match that particular PatiendId. 
        **/
       private function getDevicesForPatient($patientId)
       {
           global $pdo;
           //Retrieving device registration id for notification and device
           try{
			   // ********** TESTING SELECT STATEMENT **********
               $sql = "SELECT PD.PatientDeviceIdentifierSerNum, PD.RegistrationId, PD.DeviceType 
							FROM PatientDeviceIdentifier as PD, Patient as P
							WHERE P.PatientId = '".$patientId."' 
								AND P.PatientSerNum = PD.PatientSerNum
								AND length(trim(PD.RegistrationId)) > 0
								ORDER BY PD.PatientDeviceIdentifierSerNum;";

		   // $sql = "SELECT PD.PatientDeviceIdentifierSerNum, PD.RegistrationId, PD.DeviceType 
							// FROM PatientDeviceIdentifier as PD, Patient as P
							// WHERE P.PatientId = '".$patientId."' 
								// AND P.PatientSerNum = PD.PatientSerNum
								// AND length(trim(PD.RegistrationId)) > 0
								// ORDER BY PD.PatientDeviceIdentifierSerNum;";
               $result = $pdo->query($sql);
           }catch(PDOException $e)
           {
               echo $e;
               exit();
           }
          return $result ->fetchAll();
       }
       
	   
       /**
        *    (buildMessageForCheckInNotification($title, $description)
        *    Build the messages with title and a description
        *    Description: Builds push notification message for checking in
        *    Returns: Returns array with the push notification message to be sent
        **/
       private function buildMessageForCheckInNotification($title, $description)
       {
            $message = array(
               "mtitle"=>$title,
               "mdesc"=>$description
            );
            return $message;
       }


   }
   
?>
